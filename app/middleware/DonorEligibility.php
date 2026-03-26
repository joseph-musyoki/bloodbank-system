<?php
/**
 * DonorEligibility — Complex conditional validation
 *
 * Rules (Kenya National Blood Transfusion Service guidelines):
 *  - Age: 16–65 years
 *  - Weight: ≥ 50 kg
 *  - Hemoglobin: ≥ 12.5 g/dL (female), ≥ 13.0 g/dL (male)
 *  - Deferral period: 56 days (8 weeks) between whole-blood donations
 *  - Platelets: 14 days between donations
 *  - Plasma: 28 days between donations
 *  - Post-illness deferral: varies by condition
 */
class DonorEligibility
{
    // Deferral days by donation type
    public const DEFERRAL_DAYS = [
        'whole_blood' => 56,
        'packed_cells'=> 56,
        'platelets'   => 14,
        'plasma'      => 28,
    ];

    public const MIN_HB_MALE   = 13.0;
    public const MIN_HB_FEMALE = 12.5;
    public const MIN_WEIGHT    = 50.0;
    public const MIN_AGE       = 16;
    public const MAX_AGE       = 65;
    public const MAX_DONATIONS_PER_YEAR = 4;

    /**
     * Full eligibility check — returns ['eligible' => bool, 'reasons' => [], 'deferred_until' => ?Date]
     */
    public static function check(array $donor, ?array $lastDonation = null, string $component = 'whole_blood'): array
    {
        $reasons = [];
        $deferredUntil = null;

        // ── Age ──────────────────────────────────────────────
        $dob = new DateTime($donor['date_of_birth']);
        $age = (int)(new DateTime())->diff($dob)->y;

        if ($age < self::MIN_AGE) {
            $reasons[] = "Donor is too young ({$age} yrs). Minimum age is " . self::MIN_AGE . " years.";
        } elseif ($age > self::MAX_AGE) {
            $reasons[] = "Donor exceeds maximum age ({$age} yrs). Maximum is " . self::MAX_AGE . " years.";
        }

        // ── Weight ───────────────────────────────────────────
        if ((float)$donor['weight_kg'] < self::MIN_WEIGHT) {
            $reasons[] = "Weight ({$donor['weight_kg']} kg) is below minimum of " . self::MIN_WEIGHT . " kg.";
        }

        // ── Hemoglobin (if provided) ─────────────────────────
        if (!empty($donor['hemoglobin'])) {
            $minHb = ($donor['gender'] === 'female') ? self::MIN_HB_FEMALE : self::MIN_HB_MALE;
            if ((float)$donor['hemoglobin'] < $minHb) {
                $reasons[] = "Hemoglobin ({$donor['hemoglobin']} g/dL) below minimum ({$minHb} g/dL) for {$donor['gender']} donor.";
            }
        }

        // ── Deferral period ──────────────────────────────────
        if ($lastDonation) {
            $deferralDays = self::DEFERRAL_DAYS[$component] ?? 56;
            $lastDate     = new DateTime($lastDonation['donation_date']);
            $nextEligible = (clone $lastDate)->modify("+{$deferralDays} days");
            $today        = new DateTime();

            if ($today < $nextEligible) {
                $deferredUntil = $nextEligible->format('Y-m-d');
                $daysLeft      = (int)$today->diff($nextEligible)->days;
                $reasons[]     = "Deferral period active. Eligible from {$nextEligible->format('d M Y')} ({$daysLeft} days remaining).";
            }
        }

        // ── Manual deferral (admin-set) ──────────────────────
        if (!empty($donor['deferral_until'])) {
            $manualDefer = new DateTime($donor['deferral_until']);
            if (new DateTime() < $manualDefer) {
                $reasons[] = "Manually deferred until {$manualDefer->format('d M Y')}: " . ($donor['deferral_reason'] ?? 'No reason given.');
                $deferredUntil = $donor['deferral_until'];
            }
        }

        return [
            'eligible'      => empty($reasons),
            'reasons'       => $reasons,
            'deferred_until'=> $deferredUntil,
            'age'           => $age,
        ];
    }

    /**
     * Calculate next eligible donation date
     */
    public static function nextEligibleDate(string $lastDonationDate, string $component = 'whole_blood'): string
    {
        $days = self::DEFERRAL_DAYS[$component] ?? 56;
        return (new DateTime($lastDonationDate))->modify("+{$days} days")->format('d M Y');
    }

    /**
     * Annual donation count check
     */
    public static function annualLimitReached(int $donationsThisYear): bool
    {
        return $donationsThisYear >= self::MAX_DONATIONS_PER_YEAR;
    }
}
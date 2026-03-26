<?php
/**
 * BloodCompatibility — Blood type matching logic
 *
 * Whole blood / packed cells compatibility chart (receive FROM):
 *   O-  can give to: everyone  (universal donor)
 *   O+  can give to: O+, A+, B+, AB+
 *   A-  can give to: A-, A+, AB-, AB+
 *   A+  can give to: A+, AB+
 *   B-  can give to: B-, B+, AB-, AB+
 *   B+  can give to: B+, AB+
 *   AB- can give to: AB-, AB+
 *   AB+ can give to: AB+ only
 *
 * Plasma (reverse — AB plasma is universal):
 *   AB can donate plasma to all
 */
class BloodCompatibility
{
    // Compatible donors for a RECIPIENT blood type (whole blood / RBCs)
    private const COMPATIBLE_DONORS = [
        'A+'  => ['A+', 'A-', 'O+', 'O-'],
        'A-'  => ['A-', 'O-'],
        'B+'  => ['B+', 'B-', 'O+', 'O-'],
        'B-'  => ['B-', 'O-'],
        'AB+' => ['AB+', 'AB-', 'A+', 'A-', 'B+', 'B-', 'O+', 'O-'],
        'AB-' => ['AB-', 'A-', 'B-', 'O-'],
        'O+'  => ['O+', 'O-'],
        'O-'  => ['O-'],
    ];

    // Compatible donors for PLASMA transfusion
    private const PLASMA_COMPATIBLE = [
        'A+'  => ['A+', 'A-', 'AB+', 'AB-'],
        'A-'  => ['A+', 'A-', 'AB+', 'AB-'],
        'B+'  => ['B+', 'B-', 'AB+', 'AB-'],
        'B-'  => ['B+', 'B-', 'AB+', 'AB-'],
        'AB+' => ['AB+', 'AB-'],
        'AB-' => ['AB+', 'AB-'],
        'O+'  => ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'],
        'O-'  => ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'],
    ];

    public static function getCompatibleDonors(string $recipientType, string $component = 'whole_blood'): array
    {
        if ($component === 'plasma') {
            return self::PLASMA_COMPATIBLE[$recipientType] ?? [];
        }
        return self::COMPATIBLE_DONORS[$recipientType] ?? [];
    }

    public static function isCompatible(string $donorType, string $recipientType, string $component = 'whole_blood'): bool
    {
        return in_array($donorType, self::getCompatibleDonors($recipientType, $component), true);
    }

    public static function isUniversalDonor(string $bloodType, string $component = 'whole_blood'): bool
    {
        return $component === 'plasma' ? $bloodType === 'AB+' : $bloodType === 'O-';
    }

    public static function allTypes(): array
    {
        return ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
    }

    public static function components(): array
    {
        return ['whole_blood' => 'Whole Blood', 'packed_cells' => 'Packed Cells',
                'plasma' => 'Plasma', 'platelets' => 'Platelets', 'cryoprecipitate' => 'Cryoprecipitate'];
    }
}
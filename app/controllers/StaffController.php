<?php
class StaffController
{
    private InventoryModel $inventory;
    private DonorModel     $donors;
    private DonationModel  $donations;
    private RequestModel   $requests;

    public function __construct()
    {
        $this->inventory = new InventoryModel();
        $this->donors    = new DonorModel();
        $this->donations = new DonationModel();
        $this->requests  = new RequestModel();
    }

    public function dashboard(array $p = []): void
    {
        Auth::requireRole('staff');
        $stats       = $this->inventory->summaryStats();
        $alerts      = $this->inventory->generateAlerts();
        $stock       = $this->inventory->getLiveStock();
        $nearExpiry  = $this->inventory->getNearExpiry(7);
        $recentDon   = $this->donations->recent(10);
        $pendingReq  = $this->requests->all(5);
        $pageTitle   = 'Staff Dashboard';
        require BASE_PATH . '/app/views/staff/dashboard.php';
    }

    public function inventory(array $p = []): void
    {
        Auth::requireRole('staff');
        $stock      = $this->inventory->getLiveStock();
        $thresholds = $this->inventory->getThresholds();
        $alerts     = $this->inventory->generateAlerts();
        $nearExpiry = $this->inventory->getNearExpiry(14);
        $pageTitle  = 'Blood Inventory';
        require BASE_PATH . '/app/views/staff/inventory.php';
    }

    public function donors(array $p = []): void
    {
        Auth::requireRole('staff');
        $filters  = ['blood_type'=>$_GET['bt']??'','county'=>$_GET['county']??'','eligible'=>$_GET['eligible']??''];
        $donors   = $this->donors->search($filters);
        $pageTitle= 'Donor Management';
        require BASE_PATH . '/app/views/staff/donors.php';
    }

    public function donorDetail(array $p = []): void
    {
        Auth::requireRole('staff');
        $donor    = $this->donors->findById((int)$p['id']);
        if (!$donor) { http_response_code(404); require BASE_PATH.'/app/views/shared/404.php'; return; }
        $history  = $this->donors->getDonationHistory($donor['id']);
        $lastDon  = $this->donors->getLastDonation($donor['id']);
        $yearCount= $this->donors->countDonationsThisYear($donor['id']);
        $elig     = DonorEligibility::check($donor, $lastDon ?: null);
        $pageTitle= 'Donor: '.$donor['name'];
        require BASE_PATH . '/app/views/staff/donor_detail.php';
    }

    public function recordDonation(array $p = []): void
    {
        Auth::requireRole('staff');
        $donor     = $this->donors->findById((int)$p['id']);
        if (!$donor) { header('Location: /staff/donors'); exit; }
        $lastDon   = $this->donors->getLastDonation($donor['id']);
        $elig      = DonorEligibility::check($donor, $lastDon ?: null);
        $components= BloodCompatibility::components();
        $pageTitle = 'Record Donation';
        $errors    = [];
        require BASE_PATH . '/app/views/staff/record_donation.php';
    }

    public function storeDonation(array $p = []): void
    {
        Auth::requireRole('staff');
        Auth::verifyCsrf();
        $donorId = (int)$p['id'];
        $donor   = $this->donors->findById($donorId);
        $lastDon = $this->donors->getLastDonation($donorId);

        // Run eligibility checks with hemoglobin from form
        $donorWithHb = array_merge($donor, ['hemoglobin' => $_POST['hemoglobin'] ?? null]);
        $elig        = DonorEligibility::check($donorWithHb, $lastDon ?: null, $_POST['component']??'whole_blood');

        if (!$elig['eligible'] && ($_POST['override_eligibility']??'') !== '1') {
            $_SESSION['flash'] = ['type'=>'error','message'=>'Donor not eligible: '.implode(' ',$elig['reasons'])];
            header("Location: /staff/donors/{$donorId}/donate");
            exit;
        }

        $this->donations->record([
            'donor_id'          => $donorId,
            'donation_date'     => $_POST['donation_date'],
            'volume_ml'         => (int)($_POST['volume_ml']??450),
            'hemoglobin'        => $_POST['hemoglobin']??null,
            'blood_pressure'    => $_POST['blood_pressure']??null,
            'pulse'             => $_POST['pulse']??null,
            'donation_site'     => $_POST['donation_site'],
            'component'         => $_POST['component']??'whole_blood',
            'staff_id'          => Auth::id(),
            'status'            => $_POST['status']??'completed',
            'rejection_reason'  => $_POST['rejection_reason']??null,
        ]);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Donation recorded and blood unit added to inventory.'];
        header("Location: /staff/donors/{$donorId}");
        exit;
    }

    public function requests(array $p = []): void
    {
        Auth::requireRole('staff');
        $requests  = $this->requests->all();
        $pageTitle = 'Blood Requests';
        require BASE_PATH . '/app/views/staff/requests.php';
    }

    public function requestDetail(array $p = []): void
    {
        Auth::requireRole('staff');
        $request    = $this->requests->findById((int)$p['id']);
        if (!$request) { header('Location: /staff/requests'); exit; }
        $compatible = $this->inventory->getCompatibleUnits($request['blood_type'], $request['component'], 20);
        $issuedUnits= $this->requests->getIssuedUnits($request['id']);
        $pageTitle  = 'Request #'.$request['id'];
        require BASE_PATH . '/app/views/staff/request_detail.php';
    }

    public function fulfillRequest(array $p = []): void
    {
        Auth::requireRole('staff');
        Auth::verifyCsrf();
        $unitIds = array_map('intval', $_POST['unit_ids'] ?? []);
        if (empty($unitIds)) {
            $_SESSION['flash'] = ['type'=>'error','message'=>'Please select at least one blood unit.'];
            header('Location: /staff/requests/'.$p['id']);
            exit;
        }
        $this->requests->fulfill((int)$p['id'], $unitIds, Auth::id());
        $_SESSION['flash'] = ['type'=>'success','message'=>count($unitIds).' unit(s) issued successfully.'];
        header('Location: /staff/requests/'.$p['id']);
        exit;
    }

    public function deferDonor(array $p = []): void
    {
        Auth::requireRole('staff');
        Auth::verifyCsrf();
        $until  = !empty($_POST['deferral_until']) ? $_POST['deferral_until'] : null;
        $reason = trim($_POST['deferral_reason'] ?? '');
        $this->donors->setDeferral((int)$p['id'], $until, $reason ?: null);
        $_SESSION['flash'] = ['type'=>'success','message'=>$until ? "Donor deferred until {$until}." : 'Deferral cleared.'];
        header('Location: /staff/donors/'.$p['id']);
        exit;
    }

    public function expireUnits(array $p = []): void
    {
        Auth::requireRole('staff');
        $count = $this->inventory->markExpired();
        $_SESSION['flash'] = ['type'=>'info','message'=>"{$count} expired unit(s) marked."];
        header('Location: /staff/inventory');
        exit;
    }
}
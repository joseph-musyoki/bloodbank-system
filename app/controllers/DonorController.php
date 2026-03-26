<?php
class DonorController
{
    private DonorModel    $donors;
    private DonationModel $donations;

    public function __construct()
    {
        $this->donors    = new DonorModel();
        $this->donations = new DonationModel();
    }

    public function dashboard(array $p = []): void
    {
        Auth::requireRole('donor');
        $donor       = $this->donors->findByUserId(Auth::id());
        $history     = $this->donors->getDonationHistory($donor['id']);
        $lastDonation= $this->donors->getLastDonation($donor['id']);
        $yearCount   = $this->donors->countDonationsThisYear($donor['id']);
        $eligibility = DonorEligibility::check($donor, $lastDonation ?: null);
        $pageTitle   = 'My Dashboard';
        require BASE_PATH . '/app/views/donor/Dashboard.php';
    }

    public function appointments(array $p = []): void
    {
        Auth::requireRole('donor');
        $donor = $this->donors->findByUserId(Auth::id());
        $db    = Database::getInstance();
        $s     = $db->prepare('SELECT * FROM appointments WHERE donor_id=? ORDER BY scheduled_at DESC');
        $s->execute([$donor['id']]);
        $appointments = $s->fetchAll();
        $pageTitle    = 'My Appointments';
        require BASE_PATH . '/app/views/donor/appointments.php';
    }

    public function bookAppointment(array $p = []): void
    {
        Auth::requireRole('donor');
        $pageTitle = 'Book Appointment';
        $errors    = [];
        require BASE_PATH . '/app/views/donor/book_appointment.php';
    }

    public function storeAppointment(array $p = []): void
    {
        Auth::requireRole('donor');
        Auth::verifyCsrf();
        $donor = $this->donors->findByUserId(Auth::id());

        // Check eligibility first
        $lastDon = $this->donors->getLastDonation($donor['id']);
        $elig    = DonorEligibility::check($donor, $lastDon ?: null);
        if (!$elig['eligible']) {
            $_SESSION['flash'] = ['type'=>'error','message'=>'You are not currently eligible to donate: '.implode(' ',$elig['reasons'])];
            header('Location: /donor/appointments');
            exit;
        }

        $db = Database::getInstance();
        $s  = $db->prepare('INSERT INTO appointments (donor_id,scheduled_at,location,notes) VALUES (?,?,?,?)');
        $s->execute([$donor['id'], $_POST['scheduled_at'], $_POST['location'], $_POST['notes']??null]);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Appointment booked successfully!'];
        header('Location: /donor/appointments');
        exit;
    }

    public function history(array $p = []): void
    {
        Auth::requireRole('donor');
        $donor   = $this->donors->findByUserId(Auth::id());
        $history = $this->donors->getDonationHistory($donor['id']);
        $pageTitle = 'Donation History';
        require BASE_PATH . '/app/views/donor/history.php';
    }

    public function profile(array $p = []): void
    {
        Auth::requireRole('donor');
        $donor     = $this->donors->findByUserId(Auth::id());
        $lastDon   = $this->donors->getLastDonation($donor['id']);
        $elig      = DonorEligibility::check($donor, $lastDon ?: null);
        $pageTitle = 'My Profile';
        $counties  = $this->counties();
        $errors    = [];
        require BASE_PATH . '/app/views/donor/profile.php';
    }

    public function updateProfile(array $p = []): void
    {
        Auth::requireRole('donor');
        Auth::verifyCsrf();
        $donor = $this->donors->findByUserId(Auth::id());
        $errors = [];
        if ((float)($_POST['weight_kg']??0) < 50) $errors['weight_kg'] = 'Weight must be ≥ 50 kg.';
        if (!empty($errors)) {
            $pageTitle = 'My Profile';
            $counties  = $this->counties();
            require BASE_PATH . '/app/views/donor/profile.php';
            return;
        }
        $this->donors->update($donor['id'], ['phone'=>$_POST['phone'],'weight_kg'=>$_POST['weight_kg'],'county'=>$_POST['county'],'town'=>$_POST['town'],'medical_notes'=>$_POST['medical_notes']??null]);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Profile updated.'];
        header('Location: /donor/profile');
        exit;
    }

    private function counties(): array
    {
        return ['Baringo','Bomet','Bungoma','Busia','Elgeyo Marakwet','Embu','Garissa','Homa Bay','Isiolo','Kajiado','Kakamega','Kericho','Kiambu','Kilifi','Kirinyaga','Kisii','Kisumu','Kitui','Kwale','Laikipia','Lamu','Machakos','Makueni','Mandera','Marsabit','Meru','Migori','Mombasa',"Murang'a",'Nairobi','Nakuru','Nandi','Narok','Nyamira','Nyandarua','Nyeri','Samburu','Siaya','Taita Taveta','Tana River','Tharaka Nithi','Trans Nzoia','Turkana','Uasin Gishu','Vihiga','Wajir','West Pokot'];
    }
}
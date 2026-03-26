<?php
class HospitalController
{
    private RequestModel   $requests;
    private InventoryModel $inventory;

    public function __construct()
    {
        $this->requests  = new RequestModel();
        $this->inventory = new InventoryModel();
    }

    private function getHospital(): array
    {
        $db = Database::getInstance();
        $s  = $db->prepare('SELECT h.*, u.name FROM hospitals h JOIN users u ON u.id=h.user_id WHERE h.user_id=?');
        $s->execute([Auth::id()]);
        $h = $s->fetch();
        if (!$h) { $_SESSION['flash']=['type'=>'error','message'=>'Hospital profile not found.']; header('Location: /login'); exit; }
        return $h;
    }

    public function dashboard(array $p = []): void
    {
        Auth::requireRole('hospital');
        $hospital   = $this->getHospital();
        $myRequests = $this->requests->byHospital($hospital['id']);
        $stock      = $this->inventory->getLiveStock();
        $alerts     = $this->inventory->generateAlerts();
        $pageTitle  = 'Hospital Dashboard';
        require BASE_PATH . '/app/views/hospital/Dashboard.php';
    }

    public function showRequestForm(array $p = []): void
    {
        Auth::requireRole('hospital');
        $hospital   = $this->getHospital();
        $pageTitle  = 'Request Blood';
        $errors     = []; $old = [];
        $components = BloodCompatibility::components();
        $bloodTypes = BloodCompatibility::allTypes();
        $stock      = $this->inventory->getLiveStock();
        require BASE_PATH . '/app/views/hospital/request_form.php';
    }

    public function submitRequest(array $p = []): void
    {
        Auth::requireRole('hospital');
        Auth::verifyCsrf();
        $hospital = $this->getHospital();
        $errors = []; $old = $_POST;
        foreach (['patient_name','blood_type','component','units_requested','urgency'] as $f)
            if (empty(trim($_POST[$f]??''))) $errors[$f] = ucfirst(str_replace('_',' ',$f)).' is required.';
        if (empty($errors['units_requested']) && (int)($_POST['units_requested']??0)<1)
            $errors['units_requested'] = 'At least 1 unit required.';
        if (!empty($errors)) {
            $pageTitle='Request Blood'; $components=BloodCompatibility::components(); $bloodTypes=BloodCompatibility::allTypes(); $stock=$this->inventory->getLiveStock();
            require BASE_PATH.'/app/views/hospital/request_form.php'; return;
        }
        $id = $this->requests->create(['hospital_id'=>$hospital['id'],'patient_name'=>trim($_POST['patient_name']),'patient_age'=>$_POST['patient_age']??null,'blood_type'=>$_POST['blood_type'],'component'=>$_POST['component'],'units_requested'=>(int)$_POST['units_requested'],'urgency'=>$_POST['urgency'],'clinical_notes'=>$_POST['clinical_notes']??null,'required_by'=>!empty($_POST['required_by'])?$_POST['required_by']:null]);
        $_SESSION['flash']=['type'=>'success','message'=>"Request #$id submitted successfully."];
        header('Location: /hospital/requests'); exit;
    }

    public function myRequests(array $p = []): void
    {
        Auth::requireRole('hospital');
        $hospital  = $this->getHospital();
        $requests  = $this->requests->byHospital($hospital['id']);
        $pageTitle = 'My Blood Requests';
        require BASE_PATH . '/app/views/hospital/my_requests.php';
    }

    public function requestStatus(array $p = []): void
    {
        Auth::requireRole('hospital');
        $hospital = $this->getHospital();
        $request  = $this->requests->findById((int)$p['id']);
        if (!$request || $request['hospital_id'] !== $hospital['id']) { http_response_code(403); require BASE_PATH.'/app/views/shared/403.php'; return; }
        $issuedUnits = $this->requests->getIssuedUnits($request['id']);
        $pageTitle   = 'Request #'.$request['id'];
        require BASE_PATH . '/app/views/hospital/request_detail.php';
    }

    public function cancelRequest(array $p = []): void
    {
        Auth::requireRole('hospital');
        Auth::verifyCsrf();
        $hospital = $this->getHospital();
        $request  = $this->requests->findById((int)$p['id']);
        if ($request && $request['hospital_id']===$hospital['id'] && $request['status']==='pending')
            $this->requests->cancel((int)$p['id']);
        $_SESSION['flash']=['type'=>'info','message'=>'Request cancelled.'];
        header('Location: /hospital/requests'); exit;
    }
}
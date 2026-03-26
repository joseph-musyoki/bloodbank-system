<?php
class AuthController
{
    public function showLogin(array $p = []): void
    {
        if (Auth::check()) { $this->redirectByRole(); return; }
        $error = $_SESSION['auth_error'] ?? null;
        unset($_SESSION['auth_error']);
        $pageTitle = 'Sign In';
        require BASE_PATH . '/app/views/auth/login.php';
    }

    public function login(array $p = []): void
    {
        Auth::verifyCsrf();
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $db       = Database::getInstance();
        $s        = $db->prepare('SELECT * FROM users WHERE email=?');
        $s->execute([$email]);
        $user = $s->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            Auth::login($user);
            $intended = $_SESSION['intended'] ?? null;
            unset($_SESSION['intended']);
            header('Location: ' . ($intended ?? $this->dashboardFor($user['role'])));
        } else {
            $_SESSION['auth_error'] = 'Invalid email or password.';
            header('Location: /login');
        }
        exit;
    }

    public function showRegister(array $p = []): void
    {
        $pageTitle = 'Register';
        $errors    = [];
        $old       = [];
        $counties  = $this->counties();
        require BASE_PATH . '/app/views/auth/register.php';
    }

    public function register(array $p = []): void
    {
        Auth::verifyCsrf();
        $errors  = [];
        $old     = $_POST;
        $model   = new DonorModel();

        // Validate
        foreach (['name','email','password','national_id','phone','dob','gender','blood_type','weight_kg','county','town'] as $f) {
            if (empty(trim($_POST[$f]??''))) $errors[$f] = ucfirst(str_replace('_',' ',$f)).' is required.';
        }
        if (empty($errors['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
            $errors['email'] = 'Invalid email address.';
        if (empty($errors['email']) && $model->emailExists($_POST['email']))
            $errors['email'] = 'Email already registered.';
        if (empty($errors['password']) && strlen($_POST['password']) < 8)
            $errors['password'] = 'Password must be at least 8 characters.';
        if (empty($errors['weight_kg']) && (float)($_POST['weight_kg']??0) < 50)
            $errors['weight_kg'] = 'Weight must be at least 50 kg.';

        // Age check
        if (empty($errors['dob'])) {
            $dob = DateTime::createFromFormat('Y-m-d', $_POST['dob']);
            if (!$dob) { $errors['dob'] = 'Invalid date.'; }
            else {
                $age = (int)(new DateTime())->diff($dob)->y;
                if ($age < 16 || $age > 65) $errors['dob'] = "Age must be between 16–65 years (you are {$age}).";
            }
        }

        if (!empty($errors)) {
            $counties  = $this->counties();
            $pageTitle = 'Register';
            require BASE_PATH . '/app/views/auth/register.php';
            return;
        }

        $userId = $model->createUser(['name'=>trim($_POST['name']),'email'=>$_POST['email'],'password'=>$_POST['password']]);
        $model->create(['user_id'=>$userId,'national_id'=>$_POST['national_id'],'phone'=>$_POST['phone'],'dob'=>$_POST['dob'],'gender'=>$_POST['gender'],'blood_type'=>$_POST['blood_type'],'weight_kg'=>$_POST['weight_kg'],'county'=>$_POST['county'],'town'=>$_POST['town'],'medical_notes'=>$_POST['medical_notes']??null]);

        $_SESSION['flash'] = ['type'=>'success','message'=>'Registration successful! Please sign in.'];
        header('Location: /login');
        exit;
    }

    public function logout(array $p = []): void
    {
        Auth::logout();
        header('Location: /login');
        exit;
    }

    private function redirectByRole(): void
    {
        header('Location: ' . BASE_URL . $this->dashboardFor(Auth::role()));
        exit;
    }

    private function dashboardFor(string $role): string
    {
        return match($role) {
            'staff'    => '/staff/dashboard',
            'hospital' => '/hospital/dashboard',
            default    => '/donor/dashboard',
        };
    }

    private function counties(): array
    {
        return ['Baringo','Bomet','Bungoma','Busia','Elgeyo Marakwet','Embu','Garissa','Homa Bay','Isiolo','Kajiado','Kakamega','Kericho','Kiambu','Kilifi','Kirinyaga','Kisii','Kisumu','Kitui','Kwale','Laikipia','Lamu','Machakos','Makueni','Mandera','Marsabit','Meru','Migori','Mombasa',"Murang'a",'Nairobi','Nakuru','Nandi','Narok','Nyamira','Nyandarua','Nyeri','Samburu','Siaya','Taita Taveta','Tana River','Tharaka Nithi','Trans Nzoia','Turkana','Uasin Gishu','Vihiga','Wajir','West Pokot'];
    }
}
<?php
class Auth
{
    public static function check(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION['user']['role'] ?? null;
    }

    public static function id(): ?int
    {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    public static function requireLogin(string $redirectTo = null): void
    {
        if ($redirectTo === null) {
            $redirectTo = BASE_URL . '/login';
        } elseif (!str_starts_with($redirectTo, 'http')) {
            $redirectTo = BASE_URL . $redirectTo;
        }

        if (!self::check()) {
            $_SESSION['intended'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . $redirectTo);
            exit;
        }
    }

    public static function requireRole(string ...$roles): void
    {
        self::requireLogin();
        if (!in_array(self::role(), $roles, true)) {
            http_response_code(403);
            require BASE_PATH . '/app/views/shared/403.php';
            exit;
        }
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user']    = $user;
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    public static function logout(): void
    {
        session_unset();
        session_destroy();
    }

    public static function verifyCsrf(): void
    {
        $token = $_POST['_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            die('CSRF token mismatch.');
        }
    }
}
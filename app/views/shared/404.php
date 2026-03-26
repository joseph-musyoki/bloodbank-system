<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Page Not Found | BloodBank</title>
    <link rel="stylesheet" href="/BLOODBANK-SYSTEM/public/assets/css/main.css">
</head>
<body class="error-page">
    <div class="error-container">
        <div class="error-visual">
            <div class="error-code">404</div>
            <div class="error-pulse"></div>
        </div>
        <h1 class="error-title">Page Not Found</h1>
        <p class="error-message">
            The page you're looking for doesn't exist or may have been moved.
        </p>
        <div class="error-actions">
            <a href="javascript:history.back()" class="btn btn-outline">← Go Back</a>
            <?php
            $dashboards = [
                'donor'    => '/donor/dashboard',
                'staff'    => '/staff/dashboard',
                'hospital' => '/hospital/dashboard',
            ];
            $role = $_SESSION['role'] ?? null;
            if ($role && isset($dashboards[$role])): ?>
                <a href="<?= $dashboards[$role] ?>" class="btn btn-primary">My Dashboard</a>
            <?php else: ?>
                <a href="/login" class="btn btn-primary">Go to Login</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

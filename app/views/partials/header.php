<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'BloodBank') ?> — Kenya Blood Bank System</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Newsreader:ital,wght@0,400;0,500;1,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/BLOODBANK-SYSTEM/public/assets/css/main.css">
</head>
<body class="<?= $bodyClass ?? '' ?>" data-role="<?= htmlspecialchars(Auth::role() ?? 'guest') ?>">

<?php
$role      = Auth::role();
$user      = Auth::user();
$navLinks  = match($role) {
    'donor'    => ['/donor/dashboard'=>'Dashboard','/donor/history'=>'My Donations','/donor/appointments'=>'Appointments','/donor/profile'=>'Profile'],
    'staff'    => ['/staff/dashboard'=>'Dashboard','/staff/inventory'=>'Inventory','/staff/donors'=>'Donors','/staff/requests'=>'Requests'],
    'hospital' => ['/hospital/dashboard'=>'Dashboard','/hospital/request'=>'Request Blood','/hospital/requests'=>'My Requests','/hospital/stock'=>'Availability'],
    default    => [],
};
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>

<header class="topbar">
  <a href="<?= match($role){ 'staff'=>'/staff/dashboard','hospital'=>'/hospital/dashboard',default=>'/donor/dashboard' } ?>" class="topbar__brand">
    <span class="brand-drop"></span>
    <span class="brand-text">BloodBank <em>Kenya</em></span>
  </a>

  <nav class="topbar__nav">
    <?php foreach ($navLinks as $href => $label): ?>
    <a href="<?= $href ?>" class="topbar__link <?= str_starts_with($currentPath, $href) && $href !== '/donor/dashboard' || $currentPath === $href ? 'is-active' : '' ?>">
      <?= $label ?>
    </a>
    <?php endforeach; ?>
  </nav>

  <div class="topbar__right">
    <span class="role-badge role-badge--<?= $role ?>"><?= ucfirst($role ?? '') ?></span>
    <span class="topbar__user"><?= htmlspecialchars($user['name'] ?? '') ?></span>
    <a href="<?= BASE_URL ?>/logout" class="topbar__logout">Sign out</a>
  </div>

  <button class="topbar__burger" id="navToggle" aria-label="Menu">&#9776;</button>
</header>

<?php if (!empty($_SESSION['flash'])): ?>
<div class="flash flash--<?= $_SESSION['flash']['type'] ?>" id="flash">
  <span><?= htmlspecialchars($_SESSION['flash']['message']) ?></span>
  <button onclick="this.parentElement.remove()" class="flash__close">&times;</button>
</div>
<?php unset($_SESSION['flash']); endif; ?>

<main class="main-content">

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In — BloodBank System</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/BLOODBANK-SYSTEM/public/assets/css/main.css">
</head>
<body>
<?php if (!empty($_SESSION['flash'])): ?>
<div class="flash"><div class="alert alert--<?= $_SESSION['flash']['type'] ?>"><?= htmlspecialchars($_SESSION['flash']['message']) ?></div></div>
<?php unset($_SESSION['flash']); endif; ?>
<div class="auth-wrap">
  <div class="auth-box">
    <div class="auth-logo"><div class="auth-logo__dot"></div>BloodBank System</div>
    <h1 class="auth-title">Welcome back</h1>
    <p class="auth-sub">Sign in to your portal to continue.</p>
    <?php if (!empty($error)): ?><div class="alert alert--error" style="margin-bottom:16px;"><span class="alert__icon">✕</span> <?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form class="auth-form" method="POST" action="<?= BASE_URL ?>/login">
      <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?>">
      <div class="field"><label for="email">Email Address</label><input type="email" id="email" name="email" required autofocus value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="you@example.com"></div>
      <div class="field"><label for="password">Password</label><input type="password" id="password" name="password" required placeholder="••••••••"></div>
      <button type="submit" class="btn btn--primary btn--full btn--lg">Sign In</button>
    </form>
    <div class="auth-divider">— or —</div>
    <a href="<?= BASE_URL ?>/register" class="btn btn--ghost btn--full">Register as Donor</a>
  </div>
</div>
<script src="/BLOODBANK-SYSTEM/public/assets/js/main.js"></script>
</body>
</html>

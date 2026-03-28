<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In — BloodBank Kenya</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=IBM+Plex+Sans:wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/main.css?v=3">
</head>
<body class="login-bg">

<?php if (!empty($_SESSION['flash'])): ?>
<div class="flash flash--<?= $_SESSION['flash']['type'] ?>">
  <span><?= htmlspecialchars($_SESSION['flash']['message']) ?></span>
  <button onclick="this.parentElement.remove()">×</button>
</div>
<?php unset($_SESSION['flash']); endif; ?>

<!-- Ambient background blobs -->
<div class="login-blob login-blob--1"></div>
<div class="login-blob login-blob--2"></div>
<div class="login-blob login-blob--3"></div>

<div class="login-center">
  <div class="login-card">

    <!-- Logo -->
    <div class="login-logo">
      <div class="login-logo__drop"></div>
      <div class="login-logo__text">
        <span class="login-logo__name">BloodBank</span>
        <span class="login-logo__sub">Kenya National System</span>
      </div>
    </div>

    <!-- Title -->
    <h1 class="login-title">Welcome back</h1>
    <p class="login-hint">Select your role or enter credentials below</p>

    <!-- Error -->
    <?php if (!empty($error)): ?>
    <div class="login-error">
      <span class="login-error__icon">!</span>
      <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <!-- Role quick-fill cards -->
    <div class="login-roles">
      <button type="button" class="login-role" data-email="donor@test.ke" data-pw="password">
        <span class="login-role__icon">🩸</span>
        <span class="login-role__label">Donor</span>
      </button>
      <button type="button" class="login-role" data-email="staff@bloodbank.ke" data-pw="password">
        <span class="login-role__icon">🔬</span>
        <span class="login-role__label">Staff</span>
      </button>
      <button type="button" class="login-role" data-email="hospital@test.ke" data-pw="password">
        <span class="login-role__icon">🏥</span>
        <span class="login-role__label">Hospital</span>
      </button>
    </div>

    <!-- Divider -->
    <div class="login-divider">
      <span>or sign in manually</span>
    </div>

    <!-- Form -->
    <form method="POST" action="/login" novalidate>
      <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?>">

      <div class="login-field">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" autocomplete="email" required
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
               placeholder="you@example.com">
      </div>

      <div class="login-field">
        <label for="password">Password</label>
        <div class="login-field__pw">
          <input type="password" id="password" name="password" autocomplete="current-password"
                 required placeholder="••••••••">
          <button type="button" id="togglePw" class="login-field__eye" title="Show password">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
              <circle cx="12" cy="12" r="3"/>
            </svg>
          </button>
        </div>
      </div>

      <button type="submit" class="login-btn">Sign In</button>
    </form>

    <!-- Register link -->
    <p class="login-register">
      New donor? <a href="/register">Create an account →</a>
    </p>
  </div>
</div>

<script>
// Role card auto-fill
document.querySelectorAll('.login-role').forEach(card => {
  card.addEventListener('click', () => {
    document.getElementById('email').value    = card.dataset.email;
    document.getElementById('password').value = card.dataset.pw;
    document.querySelectorAll('.login-role').forEach(c => c.classList.remove('login-role--active'));
    card.classList.add('login-role--active');
    // Subtle bounce
    card.style.transform = 'scale(0.95)';
    setTimeout(() => { card.style.transform = ''; }, 120);
  });
});

// Password toggle
document.getElementById('togglePw').addEventListener('click', function() {
  const inp = document.getElementById('password');
  const svg = this.querySelector('svg');
  if (inp.type === 'password') {
    inp.type = 'text';
    svg.style.opacity = '1';
    this.style.color = 'var(--red2)';
  } else {
    inp.type = 'password';
    svg.style.opacity = '0.4';
    this.style.color = '';
  }
});
</script>
</body>
</html>

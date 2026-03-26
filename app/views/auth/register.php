<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register as Donor - BloodBankKE</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=JetBrains+Mono:wght@400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/BLOODBANK-SYSTEM/public/assets/css/main.css">
</head>
<body class="auth-page register-page">
<div class="register-wrap">
  <div class="register-header">
    <a href="<?= BASE_URL ?>/login" class="register-back">Back to sign in</a>
    <h1 class="register-title">Donor Registration</h1>
    <p class="register-sub">Join Kenya's blood donor network. Takes about 3 minutes.</p>
  </div>
  <form class="register-form" method="POST" action="<?= BASE_URL ?>/register" novalidate>
    <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?>">
    <div class="form-section">
      <h3 class="form-section__title">01 — Personal Details</h3>
      <div class="form-grid form-grid--2">
        <div class="field <?= isset($errors['name'])?'field--error':'' ?>">
          <label>Full Name *</label>
          <input type="text" name="name" value="<?= htmlspecialchars($old['name']??'') ?>" placeholder="Jane Wanjiku" required>
          <?php if(isset($errors['name'])): ?><span class="field__err"><?= $errors['name'] ?></span><?php endif; ?>
        </div>
        <div class="field <?= isset($errors['national_id'])?'field--error':'' ?>">
          <label>National ID *</label>
          <input type="text" name="national_id" value="<?= htmlspecialchars($old['national_id']??'') ?>" placeholder="12345678" required>
          <?php if(isset($errors['national_id'])): ?><span class="field__err"><?= $errors['national_id'] ?></span><?php endif; ?>
        </div>
        <div class="field <?= isset($errors['dob'])?'field--error':'' ?>">
          <label>Date of Birth * (16-65 yrs)</label>
          <input type="date" name="dob" value="<?= htmlspecialchars($old['dob']??'') ?>" required>
          <?php if(isset($errors['dob'])): ?><span class="field__err"><?= $errors['dob'] ?></span><?php endif; ?>
        </div>
        <div class="field <?= isset($errors['gender'])?'field--error':'' ?>">
          <label>Gender *</label>
          <select name="gender" required>
            <option value="">Select...</option>
            <option value="male" <?= ($old['gender']??'')=== 'male'?'selected':'' ?>>Male</option>
            <option value="female" <?= ($old['gender']??'')=== 'female'?'selected':'' ?>>Female</option>
            <option value="other" <?= ($old['gender']??'')=== 'other'?'selected':'' ?>>Other</option>
          </select>
          <?php if(isset($errors['gender'])): ?><span class="field__err"><?= $errors['gender'] ?></span><?php endif; ?>
        </div>
        <div class="field <?= isset($errors['blood_type'])?'field--error':'' ?>">
          <label>Blood Type *</label>
          <select name="blood_type" required>
            <option value="">Select...</option>
            <?php foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bt): ?>
            <option value="<?= $bt ?>" <?= ($old['blood_type']??'')===$bt?'selected':'' ?>><?= $bt ?></option>
            <?php endforeach; ?>
          </select>
          <?php if(isset($errors['blood_type'])): ?><span class="field__err"><?= $errors['blood_type'] ?></span><?php endif; ?>
        </div>
        <div class="field <?= isset($errors['weight_kg'])?'field--error':'' ?>">
          <label>Weight (kg) * (min 50 kg)</label>
          <input type="number" name="weight_kg" value="<?= htmlspecialchars($old['weight_kg']??'') ?>" min="50" max="200" step="0.1" placeholder="70" required>
          <?php if(isset($errors['weight_kg'])): ?><span class="field__err"><?= $errors['weight_kg'] ?></span><?php endif; ?>
        </div>
      </div>
    </div>
    <div class="form-section">
      <h3 class="form-section__title">02 — Contact and Location</h3>
      <div class="form-grid form-grid--2">
        <div class="field <?= isset($errors['email'])?'field--error':'' ?>">
          <label>Email Address *</label>
          <input type="email" name="email" value="<?= htmlspecialchars($old['email']??'') ?>" placeholder="you@example.com" required>
          <?php if(isset($errors['email'])): ?><span class="field__err"><?= $errors['email'] ?></span><?php endif; ?>
        </div>
        <div class="field <?= isset($errors['phone'])?'field--error':'' ?>">
          <label>Phone Number *</label>
          <input type="tel" name="phone" value="<?= htmlspecialchars($old['phone']??'') ?>" placeholder="0712345678" required>
          <?php if(isset($errors['phone'])): ?><span class="field__err"><?= $errors['phone'] ?></span><?php endif; ?>
        </div>
        <div class="field <?= isset($errors['county'])?'field--error':'' ?>">
          <label>County *</label>
          <select name="county" required>
            <option value="">Select county...</option>
            <?php foreach($counties as $c): ?>
            <option value="<?= htmlspecialchars($c) ?>" <?= ($old['county']??'')===$c?'selected':'' ?>><?= htmlspecialchars($c) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if(isset($errors['county'])): ?><span class="field__err"><?= $errors['county'] ?></span><?php endif; ?>
        </div>
        <div class="field <?= isset($errors['town'])?'field--error':'' ?>">
          <label>Town / Area *</label>
          <input type="text" name="town" value="<?= htmlspecialchars($old['town']??'') ?>" placeholder="Westlands" required>
          <?php if(isset($errors['town'])): ?><span class="field__err"><?= $errors['town'] ?></span><?php endif; ?>
        </div>
      </div>
    </div>
    <div class="form-section">
      <h3 class="form-section__title">03 — Account Password</h3>
      <div class="form-grid form-grid--2">
        <div class="field <?= isset($errors['password'])?'field--error':'' ?>">
          <label>Password * (min 8 characters)</label>
          <input type="password" name="password" required placeholder="Choose a strong password">
          <?php if(isset($errors['password'])): ?><span class="field__err"><?= $errors['password'] ?></span><?php endif; ?>
        </div>
        <div class="field">
          <label>Medical Notes (optional)</label>
          <input type="text" name="medical_notes" value="<?= htmlspecialchars($old['medical_notes']??'') ?>" placeholder="Any conditions, medications...">
        </div>
      </div>
    </div>
    <div class="form-section form-section--submit">
      <p class="form-consent">By registering you confirm you are at least 16 years old and consent to storage and use of your health information for blood donation purposes in line with Kenya Data Protection Act 2019.</p>
      <button type="submit" class="btn btn--primary btn--lg">Create Donor Account</button>
    </div>
  </form>
</div>
<script src="/BLOODBANK-SYSTEM/public/assets/js/main.js"></script>
</body>
</html>

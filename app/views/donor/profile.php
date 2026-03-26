<?php require BASE_PATH.'/app/views/partials/layout_start.php'; ?>
<div class="page-header"><h1 class="page-title">My Profile</h1></div>
<div class="grid-2">
  <div>
    <!-- Eligibility card -->
    <div class="eligibility-card eligibility-card--<?= $elig['eligible']?'ok':'defer' ?>" style="margin-bottom:20px;">
      <div class="eligibility-card__status"><?= $elig['eligible']?'✓ Eligible':'⊘ Deferred' ?></div>
      <?php if(!$elig['eligible']): ?><ul class="eligibility-card__reasons"><?php foreach($elig['reasons'] as $r): ?><li><?= htmlspecialchars($r) ?></li><?php endforeach; ?></ul><?php endif; ?>
    </div>
    <!-- Fixed info -->
    <div class="card">
      <div class="card__header"><span class="card__title">Registered Details</span></div>
      <div class="card__body" style="font-size:0.875rem;display:grid;gap:12px;">
        <div class="flex-between"><span style="color:var(--text3)">National ID</span><span><?= htmlspecialchars($donor['national_id']) ?></span></div>
        <div class="flex-between"><span style="color:var(--text3)">Blood Type</span><span class="badge badge--blood"><?= htmlspecialchars($donor['blood_type']) ?></span></div>
        <div class="flex-between"><span style="color:var(--text3)">Date of Birth</span><span><?= date('d M Y',strtotime($donor['date_of_birth'])) ?> (Age <?= $elig['age'] ?>)</span></div>
        <div class="flex-between"><span style="color:var(--text3)">Gender</span><span><?= ucfirst($donor['gender']) ?></span></div>
      </div>
    </div>
  </div>
  <div>
    <form method="POST" action="<?= BASE_URL ?>/donor/profile">
      <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?>">
      <div class="form-section">
        <div class="form-section__title">Update Profile</div>
        <div class="form-grid" style="gap:14px;">
          <div class="field"><label>Phone</label><input type="tel" name="phone" value="<?= htmlspecialchars($donor['phone']) ?>" required></div>
          <div class="field <?= !empty($errors['weight_kg'])?'field--error':'' ?>">
            <label>Weight (kg) <span class="req">*</span></label>
            <input type="number" name="weight_kg" step="0.1" min="50" value="<?= htmlspecialchars($donor['weight_kg']) ?>" required>
            <?php if(!empty($errors['weight_kg'])): ?><span class="field__error">✕ <?= $errors['weight_kg'] ?></span><?php endif; ?>
          </div>
          <div class="field"><label>County</label>
            <select name="county"><option value="">Select…</option><?php foreach($counties as $c): ?><option value="<?= htmlspecialchars($c) ?>" <?= $donor['county']===$c?'selected':'' ?>><?= htmlspecialchars($c) ?></option><?php endforeach; ?></select>
          </div>
          <div class="field"><label>Town</label><input type="text" name="town" value="<?= htmlspecialchars($donor['town']) ?>"></div>
          <div class="field"><label>Medical Notes</label><textarea name="medical_notes"><?= htmlspecialchars($donor['medical_notes']??'') ?></textarea></div>
        </div>
      </div>
      <div class="form-actions"><button type="submit" class="btn btn--primary">Save Changes</button></div>
    </form>
  </div>
</div>
<?php require BASE_PATH.'/app/views/partials/layout_end.php'; ?>

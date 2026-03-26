<?php require BASE_PATH.'/app/views/partials/layout_start.php'; ?>
<div class="page-header"><h1 class="page-title">Book Appointment</h1></div>
<div style="max-width:560px;">
<form method="POST" action="<?= BASE_URL ?>/donor/appointments/book" class="form-grid" style="gap:0;">
  <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?>">
  <div class="form-section">
    <div class="form-section__title">Appointment Details</div>
    <div class="form-grid" style="gap:16px;">
      <div class="field <?= !empty($errors['scheduled_at'])?'field--error':'' ?>">
        <label>Date & Time <span class="req">*</span></label>
        <input type="datetime-local" name="scheduled_at" required min="<?= date('Y-m-d\TH:i') ?>">
        <?php if (!empty($errors['scheduled_at'])): ?><span class="field__error">✕ <?= $errors['scheduled_at'] ?></span><?php endif; ?>
      </div>
      <div class="field <?= !empty($errors['location'])?'field--error':'' ?>">
        <label>Donation Site <span class="req">*</span></label>
        <select name="location" required>
          <option value="">Select site…</option>
          <?php foreach(['Nairobi Blood Bank – Upper Hill','KNH Donation Centre','Mombasa Regional Centre','Kisumu Blood Bank','Nakuru County Hospital'] as $s): ?>
          <option value="<?= $s ?>"><?= $s ?></option>
          <?php endforeach; ?>
        </select>
        <?php if (!empty($errors['location'])): ?><span class="field__error">✕ <?= $errors['location'] ?></span><?php endif; ?>
      </div>
      <div class="field">
        <label>Notes <small style="color:var(--text3)">(optional)</small></label>
        <textarea name="notes" placeholder="Any special requirements or notes…"></textarea>
      </div>
    </div>
  </div>
  <div class="form-actions">
    <button type="submit" class="btn btn--primary btn--lg">Confirm Appointment</button>
    <a href="<?= BASE_URL ?>/donor/appointments" class="btn btn--ghost">Cancel</a>
  </div>
</form>
</div>
<?php require BASE_PATH.'/app/views/partials/layout_end.php'; ?>

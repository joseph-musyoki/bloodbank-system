<?php require BASE_PATH.'/app/views/partials/layout_start.php'; ?>
<div class="page-header">
  <div><h1 class="page-title">Record Donation</h1><p class="page-sub">Donor: <?= htmlspecialchars($donor['name']) ?> — <?= $donor['blood_type'] ?></p></div>
  <a href="<?= BASE_URL ?>/staff/donors/<?= $donor['id'] ?>" class="btn btn--ghost">← Back</a>
</div>

<?php if (!$elig['eligible']): ?>
<div class="alert alert--error" style="margin-bottom:20px;">
  <span class="alert__icon">⚠️</span>
  <div><strong>Donor is not currently eligible.</strong><br>
    <?php foreach($elig['reasons'] as $r): ?><div style="font-size:0.82rem;margin-top:2px;"><?= htmlspecialchars($r) ?></div><?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>/staff/donors/<?= $donor['id'] ?>/donate">
  <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?>">

  <div class="form-section">
    <div class="form-section__title">Pre-Donation Vitals</div>
    <div class="form-grid form-grid--3">
      <div class="field"><label>Hemoglobin (g/dL) <span class="req">*</span><br><small style="color:var(--text3)">Min: <?= $donor['gender']==='female'?DonorEligibility::MIN_HB_FEMALE:DonorEligibility::MIN_HB_MALE ?></small></label>
        <input type="number" name="hemoglobin" step="0.1" min="5" max="25" placeholder="13.5" required></div>
      <div class="field"><label>Blood Pressure</label><input type="text" name="blood_pressure" placeholder="120/80"></div>
      <div class="field"><label>Pulse (bpm)</label><input type="number" name="pulse" min="40" max="200" placeholder="72"></div>
    </div>
  </div>

  <div class="form-section">
    <div class="form-section__title">Donation Details</div>
    <div class="form-grid form-grid--2">
      <div class="field"><label>Donation Date <span class="req">*</span></label><input type="date" name="donation_date" value="<?= date('Y-m-d') ?>" required></div>
      <div class="field"><label>Volume (ml)</label><input type="number" name="volume_ml" value="450" min="100" max="600"></div>
      <div class="field"><label>Component</label>
        <select name="component"><?php foreach($components as $v=>$l): ?><option value="<?= $v ?>"><?= $l ?></option><?php endforeach; ?></select>
      </div>
      <div class="field"><label>Donation Site <span class="req">*</span></label>
        <select name="donation_site" required>
          <option value="">Select site…</option>
          <?php foreach(['Nairobi Blood Bank – Upper Hill','KNH Donation Centre','Mombasa Regional Centre','Kisumu Blood Bank','Nakuru County Hospital'] as $s): ?><option value="<?= $s ?>"><?= $s ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="field"><label>Status</label>
        <select name="status">
          <option value="completed">Completed</option>
          <option value="rejected">Rejected / Deferred</option>
          <option value="pending">Pending (processing)</option>
        </select>
      </div>
    </div>
    <div class="field" style="margin-top:14px;"><label>Rejection Reason <small style="color:var(--text3)">(if rejected)</small></label><input type="text" name="rejection_reason" placeholder="e.g. Low haemoglobin, recent illness"></div>
  </div>

  <?php if (!$elig['eligible']): ?>
  <div class="form-section" style="border-color:var(--amber);">
    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:0.9rem;color:var(--amber);">
      <input type="checkbox" name="override_eligibility" value="1">
      <span>Override eligibility check (emergency/supervisor approval)</span>
    </label>
  </div>
  <?php endif; ?>

  <div class="form-actions">
    <button type="submit" class="btn btn--primary btn--lg">Record Donation</button>
    <a href="<?= BASE_URL ?>/staff/donors/<?= $donor['id'] ?>" class="btn btn--ghost">Cancel</a>
  </div>
</form>
<?php require BASE_PATH.'/app/views/partials/layout_end.php'; ?>

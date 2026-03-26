<?php require BASE_PATH.'/app/views/partials/layout_start.php'; ?>
<div class="page-header">
  <div><h1 class="page-title"><?= htmlspecialchars($donor['name']) ?></h1><p class="page-sub"><?= htmlspecialchars($donor['email']) ?></p></div>
  <div class="flex-gap">
    <?php if ($elig['eligible']): ?><a href="<?= BASE_URL ?>/staff/donors/<?= $donor['id'] ?>/donate" class="btn btn--primary">+ Record Donation</a><?php endif; ?>
  </div>
</div>

<div class="grid-2">
  <div style="display:flex;flex-direction:column;gap:16px;">
    <div class="eligibility-card eligibility-card--<?= $elig['eligible']?'ok':'defer' ?>">
      <div class="eligibility-card__status"><?= $elig['eligible']?'✓ Eligible to Donate':'⊘ Currently Deferred' ?></div>
      <?php if (!$elig['eligible'] && !empty($elig['reasons'])): ?>
        <ul class="eligibility-card__reasons"><?php foreach($elig['reasons'] as $r): ?><li><?= htmlspecialchars($r) ?></li><?php endforeach; ?></ul>
      <?php endif; ?>
    </div>

    <div class="card"><div class="card__header"><span class="card__title">Donor Info</span></div>
      <div class="card__body" style="font-size:0.875rem;display:grid;gap:12px;">
        <div class="flex-between"><span style="color:var(--text3)">Blood Type</span><span class="badge badge--blood"><?= $donor['blood_type'] ?></span></div>
        <div class="flex-between"><span style="color:var(--text3)">National ID</span><span><?= htmlspecialchars($donor['national_id']) ?></span></div>
        <div class="flex-between"><span style="color:var(--text3)">Date of Birth</span><span><?= date('d M Y',strtotime($donor['date_of_birth'])) ?> (<?= $elig['age'] ?> yrs)</span></div>
        <div class="flex-between"><span style="color:var(--text3)">Weight</span><span><?= $donor['weight_kg'] ?> kg</span></div>
        <div class="flex-between"><span style="color:var(--text3)">Gender</span><span><?= ucfirst($donor['gender']) ?></span></div>
        <div class="flex-between"><span style="color:var(--text3)">Phone</span><span><?= htmlspecialchars($donor['phone']) ?></span></div>
        <div class="flex-between"><span style="color:var(--text3)">Location</span><span><?= htmlspecialchars($donor['county']) ?>, <?= htmlspecialchars($donor['town']) ?></span></div>
        <div class="flex-between"><span style="color:var(--text3)">Donations This Year</span><span><?= $yearCount ?>/<?= DonorEligibility::MAX_DONATIONS_PER_YEAR ?></span></div>
        <?php if ($donor['deferral_until']): ?>
        <div class="flex-between"><span style="color:var(--text3)">Deferred Until</span><span class="text-red"><?= date('d M Y',strtotime($donor['deferral_until'])) ?></span></div>
        <div class="flex-between"><span style="color:var(--text3)">Reason</span><span><?= htmlspecialchars($donor['deferral_reason']??'') ?></span></div>
        <?php endif; ?>
        <?php if ($donor['medical_notes']): ?><div><span style="color:var(--text3);display:block;margin-bottom:4px">Medical Notes</span><p style="font-size:0.82rem;color:var(--text2)"><?= htmlspecialchars($donor['medical_notes']) ?></p></div><?php endif; ?>
      </div>
    </div>

    <!-- Deferral form -->
    <div class="card"><div class="card__header"><span class="card__title">Manage Deferral</span></div>
      <div class="card__body">
        <form method="POST" action="<?= BASE_URL ?>/staff/donors/<?= $donor['id'] ?>/defer" class="form-grid" style="gap:14px;">
          <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?>">
          <div class="field"><label>Defer Until <small style="color:var(--text3)">(leave blank to clear)</small></label><input type="date" name="deferral_until" value="<?= htmlspecialchars($donor['deferral_until']??'') ?>"></div>
          <div class="field"><label>Reason</label><input type="text" name="deferral_reason" value="<?= htmlspecialchars($donor['deferral_reason']??'') ?>" placeholder="e.g. Recent surgery, low haemoglobin"></div>
          <div class="form-actions"><button type="submit" class="btn btn--ghost btn--sm">Update Deferral</button></div>
        </form>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card__header"><span class="card__title">Donation History</span><span class="badge badge--blue"><?= count($history) ?></span></div>
    <div class="card__body" style="padding:0;">
      <?php if (empty($history)): ?><div class="empty-state" style="padding:32px;"><h3>No donations yet</h3></div>
      <?php else: ?>
        <div class="timeline" style="padding:20px;">
          <?php foreach ($history as $d):
            $statusDot = ['completed'=>'done','rejected'=>'fail','pending'=>'pend'][$d['status']]??'pend';
          ?>
          <div class="tl-item">
            <div class="tl-dot tl-dot--<?= $statusDot ?>">🩸</div>
            <div class="tl-content">
              <div class="tl-date"><?= date('d M Y',strtotime($d['donation_date'])) ?></div>
              <div class="tl-title"><?= $d['volume_ml'] ?>ml — <?= htmlspecialchars($d['donation_site']) ?></div>
              <div class="tl-sub">
                <?php if ($d['hemoglobin']): ?>Hb: <?= $d['hemoglobin'] ?> g/dL &nbsp;<?php endif; ?>
                <?php if ($d['unit_codes']): ?><span class="badge badge--blue" style="font-size:0.7rem;"><?= htmlspecialchars($d['unit_codes']) ?></span><?php endif; ?>
                <?php if ($d['rejection_reason']): ?><span class="text-red"><?= htmlspecialchars($d['rejection_reason']) ?></span><?php endif; ?>
              </div>
            </div>
            <?php $cls=['completed'=>'green','rejected'=>'red','pending'=>'amber'][$d['status']]??'grey'; ?>
            <span class="badge badge--<?= $cls ?>" style="align-self:flex-start"><?= ucfirst($d['status']) ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php require BASE_PATH.'/app/views/partials/layout_end.php'; ?>

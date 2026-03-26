<?php require BASE_PATH . '/app/views/partials/header.php'; ?>

<div class="page-wrap">
  <div class="page-header">
    <div>
      <h1 class="page-title">Welcome, <?= htmlspecialchars(explode(' ', $donor['name'])[0]) ?></h1>
      <p class="page-sub">Blood Type: <strong class="blood-badge blood-badge--<?= strtolower(str_replace(['+','-'],['p','n'],$donor['blood_type'])) ?>"><?= $donor['blood_type'] ?></strong></p>
    </div>
    <a href="<?= BASE_URL ?>/donor/appointments/book" class="btn btn--primary">Book Appointment</a>
  </div>

  <!-- Eligibility card -->
  <div class="elig-banner elig-banner--<?= $eligibility['eligible'] ? 'ok' : 'defer' ?>">
    <div class="elig-banner__icon"><?= $eligibility['eligible'] ? '✓' : '⏸' ?></div>
    <div class="elig-banner__body">
      <?php if ($eligibility['eligible']): ?>
        <strong>You are eligible to donate!</strong>
        <span>Age <?= $eligibility['age'] ?> &middot; <?= $donor['weight_kg'] ?> kg &middot; <?= $donor['blood_type'] ?></span>
      <?php else: ?>
        <strong>Currently not eligible to donate</strong>
        <?php foreach($eligibility['reasons'] as $r): ?>
        <span><?= htmlspecialchars($r) ?></span>
        <?php endforeach; ?>
        <?php if($eligibility['deferred_until']): ?>
        <span>Next eligible: <strong><?= date('d M Y', strtotime($eligibility['deferred_until'])) ?></strong></span>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Stats row -->
  <div class="stats-row">
    <div class="stat-card">
      <span class="stat-card__num"><?= count($history) ?></span>
      <span class="stat-card__label">Total Donations</span>
    </div>
    <div class="stat-card">
      <span class="stat-card__num"><?= $yearCount ?></span>
      <span class="stat-card__label">Donations This Year</span>
    </div>
    <div class="stat-card">
      <span class="stat-card__num"><?= count($history) * 3 ?></span>
      <span class="stat-card__label">Lives Potentially Saved</span>
    </div>
    <div class="stat-card">
      <span class="stat-card__num"><?= $lastDonation ? date('d M Y', strtotime($lastDonation['donation_date'])) : '—' ?></span>
      <span class="stat-card__label">Last Donation</span>
    </div>
  </div>

  <!-- Recent history -->
  <div class="card">
    <div class="card__head">
      <h2 class="card__title">Recent Donation History</h2>
      <a href="<?= BASE_URL ?>/donor/history" class="link-more">View all &rarr;</a>
    </div>
    <?php if (empty($history)): ?>
    <div class="empty-state">
      <p>No donations recorded yet.</p>
      <a href="<?= BASE_URL ?>/donor/appointments/book" class="btn btn--ghost btn--sm">Book your first appointment</a>
    </div>
    <?php else: ?>
    <table class="data-table">
      <thead><tr><th>Date</th><th>Component</th><th>Site</th><th>Volume</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach(array_slice($history,0,5) as $d): ?>
        <tr>
          <td><?= date('d M Y', strtotime($d['donation_date'])) ?></td>
          <td><?= ucfirst(str_replace('_',' ',$d['component']??'whole blood')) ?></td>
          <td><?= htmlspecialchars($d['donation_site']) ?></td>
          <td><?= $d['volume_ml'] ?> ml</td>
          <td><span class="badge badge--<?= $d['status']==='completed'?'green':($d['status']==='rejected'?'red':'grey') ?>"><?= ucfirst($d['status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <!-- Blood type compatibility info -->
  <div class="card">
    <div class="card__head"><h2 class="card__title">Your Blood Type: Who You Can Help</h2></div>
    <div class="compat-info">
      <?php
      $canDonateToTypes = [];
      foreach(BloodCompatibility::allTypes() as $type) {
        if(BloodCompatibility::isCompatible($donor['blood_type'], $type)) $canDonateToTypes[] = $type;
      }
      ?>
      <div class="compat-block">
        <p class="compat-block__label">You can donate whole blood to:</p>
        <div class="compat-badges">
          <?php foreach($canDonateToTypes as $t): ?>
          <span class="blood-badge blood-badge--<?= strtolower(str_replace(['+','-'],['p','n'],$t)) ?>"><?= $t ?></span>
          <?php endforeach; ?>
        </div>
      </div>
      <?php if($donor['blood_type'] === 'O-'): ?>
      <div class="universal-note">🌍 You are a <strong>universal donor</strong> — your blood can help anyone!</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require BASE_PATH . '/app/views/partials/footer.php'; ?>

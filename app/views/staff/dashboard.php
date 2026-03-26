<?php require BASE_PATH . '/app/views/partials/header.php'; ?>

<div class="page-wrap">
  <div class="page-header">
    <div>
      <h1 class="page-title">Staff Dashboard</h1>
      <p class="page-sub"><?= date('l, d F Y') ?></p>
    </div>
    <a href="<?= BASE_URL ?>/staff/inventory/expire" class="btn btn--ghost btn--sm" onclick="return confirm('Mark all expired units?')">Run Expiry Check</a>
  </div>

  <!-- Summary stats -->
  <div class="stats-row stats-row--5">
    <div class="stat-card stat-card--red">
      <span class="stat-card__num"><?= $stats['available_units'] ?></span>
      <span class="stat-card__label">Units Available</span>
    </div>
    <div class="stat-card stat-card--amber">
      <span class="stat-card__num"><?= $stats['expiring_7d'] ?></span>
      <span class="stat-card__label">Expiring in 7 Days</span>
    </div>
    <div class="stat-card">
      <span class="stat-card__num"><?= $stats['pending_requests'] ?></span>
      <span class="stat-card__label">Pending Requests</span>
    </div>
    <div class="stat-card stat-card--green">
      <span class="stat-card__num"><?= $stats['eligible_donors'] ?></span>
      <span class="stat-card__label">Eligible Donors</span>
    </div>
    <div class="stat-card">
      <span class="stat-card__num"><?= $stats['donations_30d'] ?></span>
      <span class="stat-card__label">Donations (30 days)</span>
    </div>
  </div>

  <!-- Stock Alerts -->
  <?php if(!empty($alerts)): ?>
  <div class="alerts-panel">
    <div class="alerts-panel__head">
      <h2>⚠ Stock Alerts <span class="badge badge--red"><?= count($alerts) ?></span></h2>
    </div>
    <div class="alerts-list">
      <?php foreach($alerts as $a): ?>
      <div class="alert-item alert-item--<?= $a['type'] ?>">
        <span class="alert-item__icon"><?= $a['type']==='critical'?'🔴':($a['type']==='expiry'?'🟡':'🟠') ?></span>
        <div class="alert-item__body">
          <strong><?= $a['type']==='critical'?'CRITICAL':($a['type']==='expiry'?'EXPIRY WARNING':'LOW STOCK') ?></strong>
          <span><?= htmlspecialchars($a['message']) ?></span>
        </div>
        <span class="alert-item__type"><?= $a['blood_type'] ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="dash-grid">
    <!-- Near expiry -->
    <div class="card">
      <div class="card__head">
        <h2 class="card__title">Units Expiring Soon <span class="badge badge--amber"><?= count($nearExpiry) ?></span></h2>
        <a href="<?= BASE_URL ?>/staff/inventory" class="link-more">Full inventory &rarr;</a>
      </div>
      <?php if(empty($nearExpiry)): ?>
      <div class="empty-state"><p>No units expiring within 7 days.</p></div>
      <?php else: ?>
      <table class="data-table">
        <thead><tr><th>Unit Code</th><th>Type</th><th>Expires</th><th>Days Left</th></tr></thead>
        <tbody>
          <?php foreach(array_slice($nearExpiry,0,8) as $u): ?>
          <?php $daysLeft = (int)(new DateTime())->diff(new DateTime($u['expiry_date']))->days; ?>
          <tr class="<?= $daysLeft <= 3 ? 'row--critical' : 'row--warn' ?>">
            <td><code><?= $u['unit_code'] ?></code></td>
            <td><span class="blood-badge blood-badge--sm blood-badge--<?= strtolower(str_replace(['+','-'],['p','n'],$u['blood_type'])) ?>"><?= $u['blood_type'] ?></span></td>
            <td><?= date('d M Y', strtotime($u['expiry_date'])) ?></td>
            <td><span class="badge badge--<?= $daysLeft<=3?'red':'amber' ?>"><?= $daysLeft ?>d</span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>

    <!-- Recent donations -->
    <div class="card">
      <div class="card__head">
        <h2 class="card__title">Recent Donations</h2>
        <a href="<?= BASE_URL ?>/staff/donors" class="link-more">All donors &rarr;</a>
      </div>
      <table class="data-table">
        <thead><tr><th>Donor</th><th>Type</th><th>Date</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach($recentDon as $d): ?>
          <tr>
            <td><?= htmlspecialchars($d['donor_name']) ?></td>
            <td><span class="blood-badge blood-badge--sm blood-badge--<?= strtolower(str_replace(['+','-'],['p','n'],$d['blood_type'])) ?>"><?= $d['blood_type'] ?></span></td>
            <td><?= date('d M', strtotime($d['donation_date'])) ?></td>
            <td><span class="badge badge--<?= $d['status']==='completed'?'green':'red' ?>"><?= $d['status'] ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Pending requests -->
    <div class="card card--full">
      <div class="card__head">
        <h2 class="card__title">Pending Blood Requests</h2>
        <a href="<?= BASE_URL ?>/staff/requests" class="link-more">All requests &rarr;</a>
      </div>
      <?php if(empty($pendingReq)): ?>
      <div class="empty-state"><p>No pending requests.</p></div>
      <?php else: ?>
      <table class="data-table data-table--full">
        <thead><tr><th>Hospital</th><th>Patient</th><th>Type</th><th>Units</th><th>Urgency</th><th>Requested</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach($pendingReq as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['hospital_name']) ?></td>
            <td><?= htmlspecialchars($r['patient_name']) ?></td>
            <td><span class="blood-badge blood-badge--sm blood-badge--<?= strtolower(str_replace(['+','-'],['p','n'],$r['blood_type'])) ?>"><?= $r['blood_type'] ?></span></td>
            <td><?= $r['units_requested'] ?> units</td>
            <td><span class="badge badge--<?= $r['urgency']==='emergency'?'red':($r['urgency']==='urgent'?'amber':'blue') ?>"><?= ucfirst($r['urgency']) ?></span></td>
            <td><?= date('d M H:i', strtotime($r['created_at'])) ?></td>
            <td><a href="<?= BASE_URL ?>/staff/requests/<?= $r['id'] ?>" class="btn btn--xs btn--primary">Process</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require BASE_PATH . '/app/views/partials/footer.php'; ?>

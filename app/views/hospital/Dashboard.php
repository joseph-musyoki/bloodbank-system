<?php require BASE_PATH.'/app/views/partials/layout_start.php'; ?>

<?php
$pending   = array_filter($myRequests, fn($r) => $r['status']==='pending');
$fulfilled = array_filter($myRequests, fn($r) => $r['status']==='fulfilled');
?>

<div class="stats-grid">
  <div class="stat-card stat-card--blue"><div class="stat-card__icon">📋</div><div class="stat-card__num"><?= count($myRequests) ?></div><div class="stat-card__label">Total Requests</div></div>
  <div class="stat-card stat-card--amber"><div class="stat-card__icon">⏳</div><div class="stat-card__num"><?= count($pending) ?></div><div class="stat-card__label">Pending</div></div>
  <div class="stat-card stat-card--green"><div class="stat-card__icon">✓</div><div class="stat-card__num"><?= count($fulfilled) ?></div><div class="stat-card__label">Fulfilled</div></div>
  <div class="stat-card stat-card--red">
    <div class="stat-card__icon">🩸</div>
    <div class="stat-card__num"><?= array_sum(array_column(array_filter($alerts,fn($a)=>$a['type']==='critical'),'units')) ?: '✓' ?></div>
    <div class="stat-card__label">Critical Alerts</div>
  </div>
</div>

<?php if (!empty($alerts)): ?>
<div class="alert-strip" style="margin-bottom:20px;">
  <?php foreach (array_slice($alerts, 0, 4) as $a): ?>
  <div class="alert alert--<?= $a['type']==='critical'?'critical':($a['type']==='expiry'?'expiry':'low') ?>">
    <span class="alert__icon"><?= $a['type']==='critical'?'🚨':'⚠️' ?></span>
    <?= htmlspecialchars($a['message']) ?>
    <span style="font-size:0.78rem;color:inherit;margin-left:auto;">Submit a request if you need this type</span>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="grid-2">
  <!-- Stock overview -->
  <div class="card">
    <div class="card__header"><span class="card__title">Current Blood Availability</span></div>
    <div class="card__body">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <?php
        $stockMap = [];
        foreach ($stock as $row) { if ($row['component']==='whole_blood') $stockMap[$row['blood_type']] = $row; }
        foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bt):
          $avail = (int)($stockMap[$bt]['available'] ?? 0);
          $level = $avail <= 5 ? 'critical' : ($avail <= 10 ? 'low' : 'ok');
          $color = ['critical'=>'var(--red)','low'=>'var(--amber)','ok'=>'var(--green)'][$level];
        ?>
        <div style="background:var(--bg3);border-radius:8px;padding:12px;text-align:center;">
          <div style="font-family:var(--font-display);font-size:1.3rem;font-weight:800;color:#fff"><?= $bt ?></div>
          <div style="font-size:1.5rem;font-weight:800;color:<?= $color ?>;line-height:1.2"><?= $avail ?></div>
          <div style="font-size:0.65rem;color:var(--text3)">units avail.</div>
        </div>
        <?php endforeach; ?>
      </div>
      <a href="<?= BASE_URL ?>/hospital/requests/new" class="btn btn--primary btn--full" style="margin-top:16px;">＋ Request Blood</a>
    </div>
  </div>

  <!-- Recent requests -->
  <div class="card">
    <div class="card__header"><span class="card__title">My Recent Requests</span><a href="<?= BASE_URL ?>/hospital/requests" class="btn btn--ghost btn--sm">All →</a></div>
    <div class="card__body" style="padding:0;">
      <?php if (empty($myRequests)): ?>
        <div class="empty-state" style="padding:32px;"><h3>No requests yet</h3><p>Submit a request to receive blood units.</p></div>
      <?php else: ?>
        <div class="table-wrap"><table>
          <thead><tr><th>Patient</th><th>Type</th><th>Units</th><th>Urgency</th><th>Status</th></tr></thead>
          <tbody>
            <?php foreach (array_slice($myRequests,0,6) as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r['patient_name']) ?></td>
              <td><span class="badge badge--blood"><?= $r['blood_type'] ?></span></td>
              <td><?= $r['units_fulfilled'] ?>/<?= $r['units_requested'] ?></td>
              <td><span data-urgency="<?= $r['urgency'] ?>"><?= ucfirst($r['urgency']) ?></span></td>
              <td><?php $cls=['pending'=>'amber','partial'=>'blue','fulfilled'=>'green','cancelled'=>'grey'][$r['status']]??'grey'; ?><span class="badge badge--<?= $cls ?>"><?= ucfirst($r['status']) ?></span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table></div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php require BASE_PATH.'/app/views/partials/layout_end.php'; ?>

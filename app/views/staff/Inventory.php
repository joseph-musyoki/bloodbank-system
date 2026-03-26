<?php require BASE_PATH.'/app/views/partials/layout_start.php'; ?>

<?php if (!empty($alerts)): ?>
<div class="alert-strip" style="margin-bottom:20px;">
  <?php foreach ($alerts as $a): ?>
  <div class="alert alert--<?= $a['type']==='critical'?'critical':($a['type']==='expiry'?'expiry':'low') ?>">
    <span class="alert__icon"><?= $a['type']==='critical'?'🚨':($a['type']==='expiry'?'⏳':'⚠️') ?></span>
    <?= htmlspecialchars($a['message']) ?>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="page-header">
  <div><h1 class="page-title">Blood Inventory</h1><p class="page-sub">Live stock levels with expiry monitoring</p></div>
  <form method="POST" action="<?= BASE_URL ?>/staff/inventory/expire">
    <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?>">
    <button type="submit" class="btn btn--ghost btn--sm" data-confirm="Mark all expired units? This cannot be undone.">🗑 Run Expiry Sweep</button>
  </form>
</div>

<!-- Whole Blood grid -->
<div class="card" style="margin-bottom:20px;">
  <div class="card__header"><span class="card__title">Whole Blood — All Types</span></div>
  <div class="card__body">
    <div class="inventory-grid">
      <?php
      $wb = array_filter($stock, fn($r) => $r['component']==='whole_blood');
      foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bt):
        $row = null;
        foreach ($wb as $r) { if ($r['blood_type']===$bt) { $row=$r; break; } }
        $avail = (int)($row['available']??0);
        $thr   = $thresholds[$bt.':whole_blood'] ?? ['min_units'=>10,'critical_units'=>5];
        $level = $avail<=$thr['critical_units']?'critical':($avail<=$thr['min_units']?'low':'ok');
        $pct   = min(100, $thr['min_units']>0 ? round($avail/$thr['min_units']*100) : 100);
      ?>
      <div class="inv-cell inv-cell--<?= $level ?>">
        <div class="inv-cell__type"><?= $bt ?></div>
        <div class="inv-cell__units"><?= $avail ?></div>
        <div class="inv-cell__label">units</div>
        <div class="progress-bar" style="margin-top:8px;">
          <div class="progress-bar__fill progress-bar__fill--<?= $level === 'ok' ? '' : $level ?>" data-pct="<?= $pct ?>"></div>
        </div>
        <?php if (!empty($row['expiring_soon']) && $row['expiring_soon']>0): ?>
          <div class="inv-cell__expiry">⏳ <?= $row['expiring_soon'] ?> soon</div>
        <?php endif; ?>
        <div style="font-size:0.65rem;color:var(--text3);margin-top:4px;">min: <?= $thr['min_units'] ?> | crit: <?= $thr['critical_units'] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Full table by component -->
<div class="card" style="margin-bottom:20px;">
  <div class="card__header"><span class="card__title">All Components — Detailed</span></div>
  <div class="card__body" style="padding:0;">
    <div class="table-wrap"><table>
      <thead><tr><th>Blood Type</th><th>Component</th><th>Available</th><th>Reserved</th><th>Expiring ≤7d</th><th>Nearest Expiry</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($stock as $row):
          $avail = (int)$row['available'];
          $thr   = $thresholds[$row['blood_type'].':'.$row['component']] ?? ['min_units'=>10,'critical_units'=>5];
          $level = $avail<=$thr['critical_units']?'critical':($avail<=$thr['min_units']?'low':'ok');
          $badge = ['critical'=>'red','low'=>'amber','ok'=>'green'][$level];
        ?>
        <tr>
          <td><span class="badge badge--blood"><?= $row['blood_type'] ?></span></td>
          <td><?= ucwords(str_replace('_',' ',$row['component'])) ?></td>
          <td><strong><?= $avail ?></strong></td>
          <td class="td-muted"><?= $row['reserved'] ?></td>
          <td><?= $row['expiring_soon'] > 0 ? '<span class="badge badge--amber">'.$row['expiring_soon'].'</span>' : '—' ?></td>
          <td><?= $row['nearest_expiry'] ? date('d M Y',strtotime($row['nearest_expiry'])) : '—' ?></td>
          <td><span class="badge badge--<?= $badge ?>"><?= ucfirst($level) ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table></div>
  </div>
</div>

<!-- Near expiry detail -->
<?php if (!empty($nearExpiry)): ?>
<div class="card">
  <div class="card__header"><span class="card__title">⏳ Units Expiring Within 14 Days</span><span class="badge badge--amber"><?= count($nearExpiry) ?></span></div>
  <div class="card__body" style="padding:0;"><div class="table-wrap"><table>
    <thead><tr><th>Unit Code</th><th>Blood Type</th><th>Component</th><th>Volume</th><th>Expiry Date</th><th>Countdown</th><th>Location</th></tr></thead>
    <tbody>
      <?php foreach ($nearExpiry as $u): ?>
      <tr>
        <td><code style="font-size:0.8rem;color:var(--accent2)"><?= htmlspecialchars($u['unit_code']) ?></code></td>
        <td><span class="badge badge--blood"><?= $u['blood_type'] ?></span></td>
        <td><?= ucwords(str_replace('_',' ',$u['component'])) ?></td>
        <td><?= $u['volume_ml'] ?>ml</td>
        <td><?= date('d M Y',strtotime($u['expiry_date'])) ?></td>
        <td><span class="badge" data-expiry="<?= $u['expiry_date'] ?>"></span></td>
        <td class="td-muted"><?= htmlspecialchars($u['location']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table></div></div>
</div>
<?php endif; ?>

<?php require BASE_PATH.'/app/views/partials/layout_end.php'; ?>

<?php require BASE_PATH.'/app/views/partials/layout_start.php'; ?>
<div class="page-header">
  <div><h1 class="page-title">Donation History</h1><p class="page-sub"><?= count($history) ?> total donation<?= count($history)!=1?'s':'' ?></p></div>
</div>

<?php if (empty($history)): ?>
  <div class="empty-state"><div class="empty-state__icon">🩸</div><h3>No donations recorded</h3><p>Your donation history will appear here after your first donation.</p></div>
<?php else: ?>
<div class="card">
  <div class="card__body" style="padding:0;">
    <div class="table-wrap">
      <table>
        <thead><tr><th>Date</th><th>Donation Site</th><th>Component</th><th>Volume</th><th>Hb (g/dL)</th><th>Units Created</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($history as $d): ?>
          <tr>
            <td><strong><?= date('d M Y', strtotime($d['donation_date'])) ?></strong></td>
            <td><?= htmlspecialchars($d['donation_site']) ?></td>
            <td><?= htmlspecialchars(ucwords(str_replace('_',' ',$d['component']??'whole_blood'))) ?></td>
            <td><?= $d['volume_ml'] ?>ml</td>
            <td><?= $d['hemoglobin'] ?? '—' ?></td>
            <td><?= $d['unit_codes'] ? '<span class="badge badge--blue">'.htmlspecialchars($d['unit_codes']).'</span>' : '—' ?></td>
            <td>
              <?php $cls=['completed'=>'green','rejected'=>'red','pending'=>'amber','discarded'=>'grey'][$d['status']]??'grey'; ?>
              <span class="badge badge--<?= $cls ?>"><?= ucfirst($d['status']) ?></span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="card" style="margin-top:20px;">
  <div class="card__header"><span class="card__title">Deferral Reference</span></div>
  <div class="card__body">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;font-size:0.85rem;">
      <?php foreach (DonorEligibility::DEFERRAL_DAYS as $comp=>$days): ?>
      <div style="background:var(--bg3);border-radius:8px;padding:12px;">
        <div style="font-weight:600;color:var(--text);margin-bottom:2px;"><?= ucwords(str_replace('_',' ',$comp)) ?></div>
        <div style="color:var(--text3);"><?= $days ?> days between donations</div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>
<?php require BASE_PATH.'/app/views/partials/layout_end.php'; ?>

<?php require BASE_PATH.'/app/views/partials/layout_start.php'; ?>
<div class="page-header">
  <div>
    <h1 class="page-title">Request #<?= $request['id'] ?></h1>
    <p class="page-sub"><?= htmlspecialchars($request['hospital_name']) ?></p>
  </div>
  <a href="<?= BASE_URL ?>/staff/requests" class="btn btn--ghost">← Back</a>
</div>

<?php $urgencyColor = ['emergency'=>'red','urgent'=>'amber','routine'=>'blue'][$request['urgency']]??'blue'; ?>
<?php if ($request['urgency']==='emergency'): ?>
<div class="alert alert--critical" style="margin-bottom:20px;"><span class="alert__icon">🚨</span><strong>EMERGENCY REQUEST</strong> — Required by: <?= $request['required_by'] ? date('d M Y H:i',strtotime($request['required_by'])) : 'ASAP' ?></div>
<?php elseif ($request['urgency']==='urgent'): ?>
<div class="alert alert--low" style="margin-bottom:20px;"><span class="alert__icon">⚠️</span><strong>Urgent Request</strong> — Priority processing required.</div>
<?php endif; ?>

<div class="grid-2" style="margin-bottom:20px;">
  <div class="card">
    <div class="card__header"><span class="card__title">Request Details</span>
      <?php $cls=['pending'=>'amber','partial'=>'blue','fulfilled'=>'green','cancelled'=>'grey'][$request['status']]??'grey'; ?>
      <span class="badge badge--<?= $cls ?>"><?= ucfirst($request['status']) ?></span>
    </div>
    <div class="card__body" style="font-size:0.875rem;display:grid;gap:12px;">
      <div class="flex-between"><span style="color:var(--text3)">Hospital</span><strong><?= htmlspecialchars($request['hospital_name']) ?></strong></div>
      <div class="flex-between"><span style="color:var(--text3)">Contact</span><span><?= htmlspecialchars($request['hospital_phone']??'—') ?></span></div>
      <div class="flex-between"><span style="color:var(--text3)">Patient</span><span><?= htmlspecialchars($request['patient_name']) ?><?= $request['patient_age']?' ('.$request['patient_age'].'y)':'' ?></span></div>
      <div class="flex-between"><span style="color:var(--text3)">Blood Type</span><span class="badge badge--blood"><?= $request['blood_type'] ?></span></div>
      <div class="flex-between"><span style="color:var(--text3)">Component</span><span><?= ucwords(str_replace('_',' ',$request['component'])) ?></span></div>
      <div class="flex-between"><span style="color:var(--text3)">Units</span><span><strong><?= $request['units_fulfilled'] ?></strong> / <?= $request['units_requested'] ?> fulfilled</span></div>
      <div class="flex-between"><span style="color:var(--text3)">Urgency</span><span class="badge badge--<?= $urgencyColor ?>"><?= ucfirst($request['urgency']) ?></span></div>
      <div class="flex-between"><span style="color:var(--text3)">Submitted</span><span><?= date('d M Y H:i',strtotime($request['created_at'])) ?></span></div>
      <?php if ($request['required_by']): ?><div class="flex-between"><span style="color:var(--text3)">Required By</span><span class="text-amber"><?= date('d M Y H:i',strtotime($request['required_by'])) ?></span></div><?php endif; ?>
      <?php if ($request['clinical_notes']): ?><div><span style="color:var(--text3);display:block;margin-bottom:4px">Clinical Notes</span><p style="font-size:0.82rem;background:var(--bg3);padding:10px;border-radius:6px;"><?= htmlspecialchars($request['clinical_notes']) ?></p></div><?php endif; ?>
    </div>
  </div>

  <!-- Compatibility Info -->
  <div class="card">
    <div class="card__header"><span class="card__title">Compatible Donor Types</span></div>
    <div class="card__body">
      <p style="font-size:0.8rem;color:var(--text3);margin-bottom:12px;">Patient needs <strong><?= $request['blood_type'] ?></strong> — compatible donor types for <?= $request['component'] ?>:</p>
      <div style="display:flex;flex-wrap:wrap;gap:8px;">
        <?php foreach (BloodCompatibility::getCompatibleDonors($request['blood_type'], $request['component']) as $compat): ?>
        <span class="badge badge--blood" style="font-size:0.9rem;padding:4px 12px;"><?= $compat ?></span>
        <?php endforeach; ?>
      </div>
      <?php if (BloodCompatibility::isUniversalDonor('O-', $request['component'])): ?>
      <p style="font-size:0.78rem;color:var(--text3);margin-top:12px;">💡 O- is universal donor for RBCs/whole blood</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Already issued -->
<?php if (!empty($issuedUnits)): ?>
<div class="card" style="margin-bottom:20px;">
  <div class="card__header"><span class="card__title">✓ Units Already Issued</span><span class="badge badge--green"><?= count($issuedUnits) ?></span></div>
  <div class="card__body" style="padding:0;"><div class="table-wrap"><table>
    <thead><tr><th>Unit Code</th><th>Blood Type</th><th>Component</th><th>Volume</th><th>Expiry</th><th>Issued At</th></tr></thead>
    <tbody>
      <?php foreach ($issuedUnits as $u): ?>
      <tr>
        <td><code style="color:var(--accent2)"><?= htmlspecialchars($u['unit_code']) ?></code></td>
        <td><span class="badge badge--blood"><?= $u['blood_type'] ?></span></td>
        <td><?= ucwords(str_replace('_',' ',$u['component'])) ?></td>
        <td><?= $u['volume_ml'] ?>ml</td>
        <td><?= date('d M Y',strtotime($u['expiry_date'])) ?></td>
        <td><?= date('d M Y H:i',strtotime($u['issued_at'])) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table></div></div>
</div>
<?php endif; ?>

<!-- Fulfill form -->
<?php if (in_array($request['status'],['pending','partial']) && !empty($compatible)): ?>
<div class="card">
  <div class="card__header">
    <span class="card__title">Select Units to Issue</span>
    <span style="font-size:0.8rem;color:var(--text2);">
      <span id="selected-count">0</span> selected
    </span>
  </div>
  <form method="POST" action="<?= BASE_URL ?>/staff/requests/<?= $request['id'] ?>/fulfill">
    <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?>">
    <div class="card__body" style="padding:0;">
      <div class="table-wrap"><table>
        <thead><tr>
          <th><input type="checkbox" id="select-all-units"></th>
          <th>Unit Code</th><th>Blood Type</th><th>Component</th><th>Volume</th><th>Expiry</th><th>Status</th>
        </tr></thead>
        <tbody>
          <?php foreach ($compatible as $u): ?>
          <tr>
            <td><input type="checkbox" class="unit-check" name="unit_ids[]" value="<?= $u['id'] ?>"></td>
            <td><code style="color:var(--accent2)"><?= htmlspecialchars($u['unit_code']) ?></code></td>
            <td><span class="badge badge--blood"><?= $u['blood_type'] ?></span></td>
            <td><?= ucwords(str_replace('_',' ',$u['component'])) ?></td>
            <td><?= $u['volume_ml'] ?>ml</td>
            <td><?= date('d M Y',strtotime($u['expiry_date'])) ?> <span class="badge" data-expiry="<?= $u['expiry_date'] ?>"></span></td>
            <td><span class="badge badge--green">Available</span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table></div>
    </div>
    <div style="padding:16px 20px;border-top:1px solid var(--border);">
      <button type="submit" id="fulfill-btn" class="btn btn--primary" disabled>Issue Selected Units</button>
      <span style="font-size:0.8rem;color:var(--text3);margin-left:12px;">Units will be marked as issued and removed from available stock.</span>
    </div>
  </form>
</div>
<?php elseif (in_array($request['status'],['pending','partial']) && empty($compatible)): ?>
<div class="alert alert--error"><span class="alert__icon">✕</span>No compatible units currently available for <?= $request['blood_type'] ?> <?= $request['component'] ?>.</div>
<?php endif; ?>

<?php require BASE_PATH.'/app/views/partials/layout_end.php'; ?>

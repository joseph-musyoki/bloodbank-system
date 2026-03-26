<?php require BASE_PATH.'/app/views/partials/layout_start.php'; ?>
<div class="page-header">
  <div><h1 class="page-title">Blood Requests</h1><p class="page-sub"><?= count($requests) ?> total requests</p></div>
</div>
<div class="card"><div class="card__body" style="padding:0;"><div class="table-wrap"><table>
  <thead><tr><th>ID</th><th>Hospital</th><th>Patient</th><th>Blood Type</th><th>Component</th><th>Units</th><th>Urgency</th><th>Required By</th><th>Status</th><th></th></tr></thead>
  <tbody>
    <?php if (empty($requests)): ?><tr><td colspan="10" style="text-align:center;padding:40px;color:var(--text3);">No requests found.</td></tr><?php endif; ?>
    <?php foreach ($requests as $r): ?>
    <tr>
      <td class="td-muted">#<?= $r['id'] ?></td>
      <td><strong><?= htmlspecialchars($r['hospital_name']) ?></strong><br><span class="td-muted"><?= htmlspecialchars($r['hospital_county']) ?></span></td>
      <td><?= htmlspecialchars($r['patient_name']) ?><?= $r['patient_age'] ? ' ('.$r['patient_age'].'y)':'' ?></td>
      <td><span class="badge badge--blood"><?= $r['blood_type'] ?></span></td>
      <td><?= ucwords(str_replace('_',' ',$r['component'])) ?></td>
      <td><strong><?= $r['units_fulfilled'] ?>/<?= $r['units_requested'] ?></strong></td>
      <td><span data-urgency="<?= $r['urgency'] ?>"><?= ucfirst($r['urgency']) ?></span></td>
      <td class="td-muted"><?= $r['required_by'] ? date('d M H:i',strtotime($r['required_by'])) : '—' ?></td>
      <td><?php $cls=['pending'=>'amber','partial'=>'blue','fulfilled'=>'green','cancelled'=>'grey'][$r['status']]??'grey'; ?><span class="badge badge--<?= $cls ?>"><?= ucfirst($r['status']) ?></span></td>
      <td><a href="<?= BASE_URL ?>/staff/requests/<?= $r['id'] ?>" class="btn btn--ghost btn--sm">Process →</a></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table></div></div></div>
<?php require BASE_PATH.'/app/views/partials/layout_end.php'; ?>

<?php require BASE_PATH.'/app/views/partials/layout_start.php'; ?>
<div class="page-header">
  <div><h1 class="page-title">My Appointments</h1></div>
  <a href="<?= BASE_URL ?>/donor/appointments/book" class="btn btn--primary">＋ Book Appointment</a>
</div>
<?php if (empty($appointments)): ?>
  <div class="empty-state"><div class="empty-state__icon">📅</div><h3>No appointments</h3><p>Book an appointment to schedule your next donation.</p><a href="<?= BASE_URL ?>/donor/appointments/book" class="btn btn--primary" style="margin-top:16px;">Book Now</a></div>
<?php else: ?>
<div class="card"><div class="card__body" style="padding:0;"><div class="table-wrap"><table>
  <thead><tr><th>Date & Time</th><th>Location</th><th>Notes</th><th>Status</th></tr></thead>
  <tbody>
    <?php foreach ($appointments as $a): ?>
    <tr>
      <td><strong><?= date('d M Y', strtotime($a['scheduled_at'])) ?></strong><br><span class="td-muted"><?= date('H:i', strtotime($a['scheduled_at'])) ?></span></td>
      <td><?= htmlspecialchars($a['location']) ?></td>
      <td><?= htmlspecialchars($a['notes'] ?? '—') ?></td>
      <td><?php $cls=['scheduled'=>'blue','completed'=>'green','cancelled'=>'grey','no_show'=>'red'][$a['status']]??'grey'; ?><span class="badge badge--<?= $cls ?>"><?= ucfirst(str_replace('_',' ',$a['status'])) ?></span></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table></div></div></div>
<?php endif; ?>
<?php require BASE_PATH.'/app/views/partials/layout_end.php'; ?>

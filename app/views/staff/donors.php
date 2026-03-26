<?php require BASE_PATH.'/app/views/partials/layout_start.php'; ?>
<div class="page-header">
  <div><h1 class="page-title">Donor Management</h1><p class="page-sub"><?= count($donors) ?> donors found</p></div>
</div>
<form class="filter-bar" method="GET" action="<?= BASE_URL ?>/staff/donors">
  <select name="bt"><option value="">All Blood Types</option><?php foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bt): ?><option value="<?= $bt ?>" <?= ($_GET['bt']??'')===$bt?'selected':'' ?>><?= $bt ?></option><?php endforeach; ?></select>
  <input type="text" name="county" placeholder="Filter by county…" value="<?= htmlspecialchars($_GET['county']??'') ?>">
  <select name="eligible"><option value="">All Status</option><option value="1" <?= ($_GET['eligible']??'')==='1'?'selected':'' ?>>Eligible</option><option value="0" <?= ($_GET['eligible']??'')==='0'?'selected':'' ?>>Deferred</option></select>
  <button type="submit" class="btn btn--primary btn--sm">Filter</button>
  <a href="<?= BASE_URL ?>/staff/donors" class="btn btn--ghost btn--sm">Clear</a>
</form>
<div class="card"><div class="card__body" style="padding:0;"><div class="table-wrap"><table>
  <thead><tr><th>Name</th><th>Blood Type</th><th>County</th><th>Phone</th><th>Weight</th><th>Eligibility</th><th>Actions</th></tr></thead>
  <tbody>
    <?php if (empty($donors)): ?><tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text3);">No donors found.</td></tr><?php endif; ?>
    <?php foreach ($donors as $d): ?>
    <tr>
      <td><strong><?= htmlspecialchars($d['name']) ?></strong><br><span class="td-muted"><?= htmlspecialchars($d['email']) ?></span></td>
      <td><span class="badge badge--blood"><?= $d['blood_type'] ?></span></td>
      <td><?= htmlspecialchars($d['county']) ?>, <?= htmlspecialchars($d['town']) ?></td>
      <td class="td-muted"><?= htmlspecialchars($d['phone']) ?></td>
      <td class="td-muted"><?= $d['weight_kg'] ?> kg</td>
      <td><?= $d['is_eligible'] ? '<span class="badge badge--green">Eligible</span>' : '<span class="badge badge--red">Deferred</span>' ?></td>
      <td><a href="<?= BASE_URL ?>/staff/donors/<?= $d['id'] ?>" class="btn btn--ghost btn--sm">View →</a></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table></div></div></div>
<?php require BASE_PATH.'/app/views/partials/layout_end.php'; ?>

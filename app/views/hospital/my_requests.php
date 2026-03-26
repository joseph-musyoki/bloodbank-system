<?php /** @var array $requests */ ?>
<?php require BASE_PATH.'/app/views/partials/layout_start.php'; ?>

<div class="page-header">
    <h1 class="page-title">
        <span class="icon">📋</span> My Blood Requests
    </h1>
    <a href="<?= BASE_URL ?>/hospital/request" class="btn btn-primary">
        <span>+</span> New Request
    </a>
</div>

<!-- Filters -->
<div class="filter-bar">
    <form method="GET" class="filter-form">
        <div class="filter-group">
            <label>Status</label>
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All</option>
                <?php foreach (['pending','approved','fulfilled','cancelled'] as $s): ?>
                    <option value="<?= $s ?>" <?= (($_GET['status'] ?? '') === $s) ? 'selected' : '' ?>>
                        <?= ucfirst($s) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label>Blood Type</label>
            <select name="blood_type" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All</option>
                <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bt): ?>
                    <option value="<?= $bt ?>" <?= (($_GET['blood_type'] ?? '') === $bt) ? 'selected' : '' ?>>
                        <?= $bt ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <a href="<?= BASE_URL ?>/hospital/requests" class="btn btn-sm btn-outline">Clear</a>
    </form>
    <span class="filter-count"><?= count($requests) ?> request(s)</span>
</div>

<?php if (empty($requests)): ?>
    <div class="empty-state">
        <div class="empty-state-icon">🩸</div>
        <h3>No requests found</h3>
        <p>You haven't submitted any blood requests yet, or none match the current filter.</p>
        <a href="<?= BASE_URL ?>/hospital/request" class="btn btn-primary">Submit First Request</a>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Ref #</th>
                    <th>Blood Type</th>
                    <th>Component</th>
                    <th>Units</th>
                    <th>Urgency</th>
                    <th>Department</th>
                    <th>Submitted</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $req): ?>
                    <tr>
                        <td>
                            <a href="<?= BASE_URL ?>/hospital/requests/<?= $req['id'] ?>" class="link-primary">
                                #<?= str_pad($req['id'], 5, '0', STR_PAD_LEFT) ?>
                            </a>
                        </td>
                        <td>
                            <span class="blood-badge blood-badge-<?= strtolower(str_replace(['+','-'], ['pos','neg'], $req['blood_type'])) ?>">
                                <?= htmlspecialchars($req['blood_type']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars(str_replace('_', ' ', ucfirst($req['component_type']))) ?></td>
                        <td class="text-center"><strong><?= $req['units_requested'] ?></strong></td>
                        <td>
                            <span class="badge badge-urgency-<?= $req['urgency'] ?>">
                                <?= ucfirst($req['urgency']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($req['department']) ?></td>
                        <td class="text-muted">
                            <time datetime="<?= $req['created_at'] ?>">
                                <?= date('d M Y, H:i', strtotime($req['created_at'])) ?>
                            </time>
                        </td>
                        <td>
                            <span class="status-pill status-<?= $req['status'] ?>">
                                <?= ucfirst($req['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="<?= BASE_URL ?>/hospital/requests/<?= $req['id'] ?>"
                                   class="btn btn-xs btn-outline" title="View details">View</a>

                                <?php if ($req['status'] === 'pending'): ?>
                                    <form method="POST" action="<?= BASE_URL ?>/hospital/requests/<?= $req['id'] ?>/cancel"
                                          onsubmit="return confirm('Cancel this request?')" class="inline-form">
                                        <input type="hidden" name="csrf_token"
                                               value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <button type="submit" class="btn btn-xs btn-danger" title="Cancel">
                                            Cancel
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php require BASE_PATH.'/app/views/partials/layout_end.php'; ?>

<?php /** @var array $request, $timeline */ ?>
<?php require BASE_PATH.'/app/views/partials/layout_start.php'; ?>

<?php
$statusColors = [
    'pending'   => 'warning',
    'approved'  => 'info',
    'fulfilled' => 'success',
    'cancelled' => 'danger',
];
$statusColor = $statusColors[$request['status']] ?? 'secondary';
?>

<div class="page-header">
    <div>
        <a href="<?= BASE_URL ?>/hospital/requests" class="back-link">← Back to My Requests</a>
        <h1 class="page-title">
            Request #<?= str_pad($request['id'], 5, '0', STR_PAD_LEFT) ?>
            <span class="status-pill status-<?= $request['status'] ?> ml-2">
                <?= ucfirst($request['status']) ?>
            </span>
        </h1>
    </div>
    <?php if ($request['status'] === 'pending'): ?>
        <form method="POST" action="<?= BASE_URL ?>/hospital/requests/<?= $request['id'] ?>/cancel"
              onsubmit="return confirm('Are you sure you want to cancel this request?')">
            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <button type="submit" class="btn btn-danger">Cancel Request</button>
        </form>
    <?php endif; ?>
</div>

<div class="detail-grid">

    <!-- Request Summary -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Request Details</h2>
        </div>
        <div class="card-body">
            <dl class="detail-list">
                <dt>Blood Type</dt>
                <dd>
                    <span class="blood-badge blood-badge-<?= strtolower(str_replace(['+','-'], ['pos','neg'], $request['blood_type'])) ?> blood-badge-lg">
                        <?= htmlspecialchars($request['blood_type']) ?>
                    </span>
                </dd>

                <dt>Component</dt>
                <dd><?= htmlspecialchars(str_replace('_', ' ', ucfirst($request['component_type']))) ?></dd>

                <dt>Units Requested</dt>
                <dd><strong><?= $request['units_requested'] ?> units</strong></dd>

                <dt>Units Fulfilled</dt>
                <dd>
                    <?php if ($request['units_fulfilled'] > 0): ?>
                        <span class="text-success"><strong><?= $request['units_fulfilled'] ?> units</strong></span>
                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </dd>

                <dt>Urgency</dt>
                <dd>
                    <span class="badge badge-urgency-<?= $request['urgency'] ?>">
                        <?= ucfirst($request['urgency']) ?>
                    </span>
                </dd>

                <dt>Department</dt>
                <dd><?= htmlspecialchars($request['department']) ?></dd>

                <?php if (!empty($request['patient_id'])): ?>
                    <dt>Patient Reference</dt>
                    <dd><?= htmlspecialchars($request['patient_id']) ?></dd>
                <?php endif; ?>

                <dt>Submitted</dt>
                <dd><?= date('d M Y, H:i', strtotime($request['created_at'])) ?></dd>

                <?php if (!empty($request['fulfilled_at'])): ?>
                    <dt>Fulfilled At</dt>
                    <dd><?= date('d M Y, H:i', strtotime($request['fulfilled_at'])) ?></dd>
                <?php endif; ?>

                <?php if (!empty($request['notes'])): ?>
                    <dt>Clinical Notes</dt>
                    <dd class="notes-text"><?= nl2br(htmlspecialchars($request['notes'])) ?></dd>
                <?php endif; ?>
            </dl>
        </div>
    </div>

    <!-- Status Timeline -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Status Timeline</h2>
        </div>
        <div class="card-body">
            <div class="timeline">
                <?php
                $steps = [
                    ['key' => 'pending',   'label' => 'Request Submitted',    'icon' => '📋'],
                    ['key' => 'approved',  'label' => 'Approved by Staff',    'icon' => '✅'],
                    ['key' => 'fulfilled', 'label' => 'Units Dispatched',     'icon' => '🩸'],
                ];
                $statusOrder = ['pending' => 0, 'approved' => 1, 'fulfilled' => 2, 'cancelled' => -1];
                $currentOrder = $statusOrder[$request['status']] ?? 0;

                foreach ($steps as $step):
                    $stepOrder = $statusOrder[$step['key']] ?? 0;
                    $isDone    = ($currentOrder >= $stepOrder && $request['status'] !== 'cancelled');
                    $isCurrent = ($request['status'] === $step['key']);
                ?>
                    <div class="timeline-item <?= $isDone ? 'done' : '' ?> <?= $isCurrent ? 'current' : '' ?>">
                        <div class="timeline-icon"><?= $step['icon'] ?></div>
                        <div class="timeline-content">
                            <strong><?= $step['label'] ?></strong>
                            <?php if (!empty($timeline[$step['key']])): ?>
                                <time><?= date('d M Y, H:i', strtotime($timeline[$step['key']])) ?></time>
                            <?php elseif ($isCurrent): ?>
                                <time>In progress...</time>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if ($request['status'] === 'cancelled'): ?>
                    <div class="timeline-item cancelled">
                        <div class="timeline-icon">❌</div>
                        <div class="timeline-content">
                            <strong>Request Cancelled</strong>
                            <?php if (!empty($request['cancelled_at'])): ?>
                                <time><?= date('d M Y, H:i', strtotime($request['cancelled_at'])) ?></time>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<!-- Help box -->
<?php if ($request['status'] === 'pending' && $request['urgency'] === 'emergency'): ?>
    <div class="alert alert-warning mt-4">
        <strong>🚨 Emergency Request:</strong> Please call the blood bank directly at <strong>ext. 1100</strong>
        to ensure expedited processing. Do not wait for system confirmation.
    </div>
<?php endif; ?>

<?php if ($request['status'] === 'fulfilled'): ?>
    <div class="alert alert-success mt-4">
        <strong>✅ Fulfilled:</strong> <?= $request['units_fulfilled'] ?> unit(s) of
        <?= htmlspecialchars($request['blood_type']) ?> have been dispatched to
        <strong><?= htmlspecialchars($request['department']) ?></strong>.
        Please collect from the blood bank dispatch counter with this reference number.
    </div>
<?php endif; ?>
<?php require BASE_PATH.'/app/views/partials/layout_end.php'; ?>

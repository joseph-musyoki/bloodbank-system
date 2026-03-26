<?php /** @var array $inventory, $availability */ ?>
<?php require BASE_PATH.'/app/views/partials/layout_start.php'; ?>

<div class="page-header">
    <h1 class="page-title">
        <span class="icon">🏥</span> Blood Availability
    </h1>
    <div class="header-meta text-muted">
        Last updated: <?= date('d M Y, H:i') ?>
        <button class="btn btn-sm btn-outline ml-2" onclick="location.reload()">↻ Refresh</button>
    </div>
</div>

<!-- Availability Grid -->
<div class="card mb-4">
    <div class="card-header">
        <h2 class="card-title">Current Stock by Blood Type</h2>
        <span class="card-subtitle">Levels are indicative — actual allocation subject to staff approval</span>
    </div>
    <div class="card-body">
        <div class="blood-grid">
            <?php
            $bloodTypes = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];
            foreach ($bloodTypes as $bt):
                $units = $inventory[$bt]['total'] ?? 0;
                $level = $units === 0 ? 'empty' : ($units < 5 ? 'critical' : ($units < 15 ? 'low' : 'ok'));
                $pct   = min(100, round($units / 30 * 100));
            ?>
                <div class="blood-card blood-card-<?= $level ?>">
                    <div class="blood-card-type"><?= $bt ?></div>
                    <div class="blood-card-units"><?= $units ?></div>
                    <div class="blood-card-label">units available</div>
                    <div class="blood-level-bar">
                        <div class="blood-level-fill" style="width: <?= $pct ?>%"></div>
                    </div>
                    <div class="blood-card-status">
                        <?php if ($level === 'empty'): ?>
                            <span class="badge badge-danger">Out of Stock</span>
                        <?php elseif ($level === 'critical'): ?>
                            <span class="badge badge-danger">Critical</span>
                        <?php elseif ($level === 'low'): ?>
                            <span class="badge badge-warning">Low</span>
                        <?php else: ?>
                            <span class="badge badge-success">Available</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Component Breakdown -->
<div class="card mb-4">
    <div class="card-header">
        <h2 class="card-title">Availability by Component</h2>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Blood Type</th>
                        <th>Whole Blood</th>
                        <th>Packed RBC</th>
                        <th>Plasma (FFP)</th>
                        <th>Platelets</th>
                        <th>Cryoprecipitate</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bloodTypes as $bt):
                        $inv = $inventory[$bt] ?? [];
                        $total = $inv['total'] ?? 0;
                    ?>
                        <tr class="<?= $total === 0 ? 'row-unavailable' : '' ?>">
                            <td>
                                <span class="blood-badge blood-badge-<?= strtolower(str_replace(['+','-'], ['pos','neg'], $bt)) ?>">
                                    <?= $bt ?>
                                </span>
                            </td>
                            <td class="text-center"><?= $inv['whole_blood'] ?? 0 ?></td>
                            <td class="text-center"><?= $inv['packed_rbc'] ?? 0 ?></td>
                            <td class="text-center"><?= $inv['plasma'] ?? 0 ?></td>
                            <td class="text-center"><?= $inv['platelets'] ?? 0 ?></td>
                            <td class="text-center"><?= $inv['cryoprecipitate'] ?? 0 ?></td>
                            <td class="text-center"><strong><?= $total ?></strong></td>
                            <td>
                                <?php if ($total > 0): ?>
                                    <a href="<?= BASE_URL ?>/hospital/request?blood_type=<?= urlencode($bt) ?>"
                                       class="btn btn-xs btn-primary">Request</a>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Compatibility Reference -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">ABO/Rh Compatibility Reference</h2>
        <span class="card-subtitle">Which blood types can receive from which donors</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="data-table compatibility-matrix">
                <thead>
                    <tr>
                        <th>Recipient ↓ / Donor →</th>
                        <?php foreach ($bloodTypes as $donor): ?>
                            <th class="text-center"><?= $donor ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                <?php
                $compat = [
                    'A+'  => ['A+','A-','O+','O-'],
                    'A-'  => ['A-','O-'],
                    'B+'  => ['B+','B-','O+','O-'],
                    'B-'  => ['B-','O-'],
                    'AB+' => ['A+','A-','B+','B-','AB+','AB-','O+','O-'],
                    'AB-' => ['A-','B-','AB-','O-'],
                    'O+'  => ['O+','O-'],
                    'O-'  => ['O-'],
                ];
                foreach ($bloodTypes as $recipient): ?>
                    <tr>
                        <td><strong><?= $recipient ?></strong></td>
                        <?php foreach ($bloodTypes as $donor): ?>
                            <td class="text-center compat-cell">
                                <?= in_array($donor, $compat[$recipient]) ? '<span class="compat-yes">✓</span>' : '<span class="compat-no">✗</span>' ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require BASE_PATH.'/app/views/partials/layout_end.php'; ?>

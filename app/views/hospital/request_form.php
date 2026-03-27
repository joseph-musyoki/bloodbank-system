<?php /** @var array $bloodTypes */ ?>
<?php require BASE_PATH.'/app/views/partials/layout_start.php'; ?>

<div class="page-header">
    <h1 class="page-title">
        <span class="icon">🩸</span> Submit Blood Request
    </h1>
    <p class="page-subtitle">Request blood units from the central bank inventory</p>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <strong>Please fix the following errors:</strong>
        <ul>
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Request Details</h2>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/hospital/request" class="form" id="requestForm">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <div class="form-grid">
                <!-- Patient Name -->
                <div class="form-group">
                    <label class="form-label required" for="patient_name">Patient Name</label>
                    <input type="text" id="patient_name" name="patient_name"
                           class="form-control" required
                           placeholder="e.g. John Doe"
                           value="<?= htmlspecialchars($_POST['patient_name'] ?? '') ?>">
                </div>

                <!-- Patient Age -->
                <div class="form-group">
                    <label class="form-label" for="patient_age">Patient Age</label>
                    <input type="number" id="patient_age" name="patient_age"
                           class="form-control" min="0" max="120"
                           placeholder="e.g. 45 (optional)"
                           value="<?= htmlspecialchars($_POST['patient_age'] ?? '') ?>">
                </div>

                <!-- Blood Type -->
                <div class="form-group">
                    <label class="form-label required" for="blood_type">Blood Type Required</label>
                    <select id="blood_type" name="blood_type" class="form-select" required>
                        <option value="">— Select blood type —</option>
                        <?php foreach ($bloodTypes as $bt): ?>
                            <option value="<?= $bt ?>"
                                <?= (($_POST['blood_type'] ?? '') === $bt) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($bt) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Units Requested -->
                <div class="form-group">
                    <label class="form-label required" for="units_requested">Units Requested</label>
                    <input type="number" id="units_requested" name="units_requested"
                           class="form-control" min="1" max="50"
                           value="<?= htmlspecialchars($_POST['units_requested'] ?? '1') ?>"
                           required>
                    <span class="form-hint">Maximum 50 units per request</span>
                </div>

                <!-- Component Type -->
                <div class="form-group">
                    <label class="form-label required" for="component">Component Type</label>
                    <select id="component" name="component" class="form-select" required>
                        <option value="">— Select component —</option>
                        <option value="whole_blood"   <?= (($_POST['component'] ?? '') === 'whole_blood')   ? 'selected' : '' ?>>Whole Blood</option>
                        <option value="packed_rbc"    <?= (($_POST['component'] ?? '') === 'packed_rbc')    ? 'selected' : '' ?>>Packed Red Blood Cells (pRBC)</option>
                        <option value="plasma"        <?= (($_POST['component'] ?? '') === 'plasma')        ? 'selected' : '' ?>>Fresh Frozen Plasma (FFP)</option>
                        <option value="platelets"     <?= (($_POST['component'] ?? '') === 'platelets')     ? 'selected' : '' ?>>Platelets</option>
                        <option value="cryoprecipitate" <?= (($_POST['component'] ?? '') === 'cryoprecipitate') ? 'selected' : '' ?>>Cryoprecipitate</option>
                    </select>
                </div>

                <!-- Urgency -->
                <div class="form-group">
                    <label class="form-label required" for="urgency">Urgency Level</label>
                    <select id="urgency" name="urgency" class="form-select" required>
                        <option value="">— Select urgency —</option>
                        <option value="routine"   <?= (($_POST['urgency'] ?? '') === 'routine')   ? 'selected' : '' ?>>Routine (within 24 hours)</option>
                        <option value="urgent"    <?= (($_POST['urgency'] ?? '') === 'urgent')    ? 'selected' : '' ?>>Urgent (within 6 hours)</option>
                        <option value="emergency" <?= (($_POST['urgency'] ?? '') === 'emergency') ? 'selected' : '' ?>>Emergency (within 1 hour)</option>
                    </select>
                    <span class="form-hint urgency-hint" id="urgencyHint"></span>
                </div>

                <!-- Required By Date -->
                <div class="form-group">
                    <label class="form-label" for="required_by">Required By Date/Time</label>
                    <input type="datetime-local" id="required_by" name="required_by"
                           class="form-control"
                           value="<?= htmlspecialchars($_POST['required_by'] ?? '') ?>">
                </div>
            </div>

            <!-- Clinical Notes -->
            <div class="form-group">
                <label class="form-label" for="clinical_notes">Clinical Notes / Diagnosis</label>
                <textarea id="clinical_notes" name="clinical_notes" class="form-control" rows="3"
                          placeholder="Optional clinical context for this request..."><?= htmlspecialchars($_POST['clinical_notes'] ?? '') ?></textarea>
            </div>

            <!-- Compatibility info box -->
            <div class="info-box" id="compatibilityInfo" style="display:none;">
                <div class="info-box-icon">ℹ️</div>
                <div class="info-box-body">
                    <strong>Compatible Blood Types</strong>
                    <p id="compatibilityText"></p>
                </div>
            </div>

            <div class="form-actions">
                <a href="<?= BASE_URL ?>/hospital/dashboard" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                    <span class="btn-icon">📋</span> Submit Request
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Compatibility data for JS -->
<script>
const compatibility = {
    'A+':  ['A+','A-','O+','O-'],
    'A-':  ['A-','O-'],
    'B+':  ['B+','B-','O+','O-'],
    'B-':  ['B-','O-'],
    'AB+': ['A+','A-','B+','B-','AB+','AB-','O+','O-'],
    'AB-': ['A-','B-','AB-','O-'],
    'O+':  ['O+','O-'],
    'O-':  ['O-'],
};

document.getElementById('blood_type').addEventListener('change', function() {
    const bt = this.value;
    const box = document.getElementById('compatibilityInfo');
    const txt = document.getElementById('compatibilityText');
    if (bt && compatibility[bt]) {
        txt.textContent = 'Can receive from: ' + compatibility[bt].join(', ');
        box.style.display = 'flex';
    } else {
        box.style.display = 'none';
    }
});

document.getElementById('urgency').addEventListener('change', function() {
    const hints = {
        routine: '✅ Standard processing — fulfilled within 24 hours.',
        urgent: '⚠️ Priority processing — notify blood bank staff immediately after submission.',
        emergency: '🚨 EMERGENCY — call blood bank directly at ext. 1100 to expedite release.',
    };
    const hint = document.getElementById('urgencyHint');
    hint.textContent = hints[this.value] || '';
    hint.className = 'form-hint urgency-hint urgency-' + this.value;
});
</script>
<?php require BASE_PATH.'/app/views/partials/layout_end.php'; ?>

<?php
declare(strict_types=1);

/**
 * assets_infra/create.php - Register Asset & Structural Data
 * CREATE: registers a new asset and logs its first structural inspection (one transaction).
 */
require_once __DIR__ . '/../includes/assets.php';

require_login();

if (!can_manage()) {
    http_response_code(403);
    exit('You do not have permission to register infrastructure assets.');
}

$pageTitle = 'Register Infrastructure Asset';
$activePage = 'assets';

$types = $pdo->query('SELECT id, name, prefix FROM asset_types ORDER BY name')->fetchAll();
$typeIds = array_map(fn($t) => (int)$t['id'], $types);

$old = [
    'name' => '', 'type_id' => '', 'line_name' => '', 'chainage_km' => '',
    'latitude' => '', 'longitude' => '', 'condition_status' => 'Good',
    'inspected_on' => date('Y-m-d'), 'notes' => '',
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf'] ?? '')) {
        $errors['_form'] = 'Your session expired. Reload the page and submit the form again.';
    } else {
        foreach ($old as $k => $_) { $old[$k] = str_in($_POST, $k); }

        /* ---------- Server-side validation ---------- */
        $len = mb_strlen($old['name']);
        if ($len < 3 || $len > 100) {
            $errors['name'] = 'Enter a name of 3 to 100 characters.';
        } elseif (!preg_match('/^[\p{L}\p{N} .,\-\/()#]+$/u', $old['name'])) {
            $errors['name'] = 'Use letters, numbers, spaces and . , - / ( ) # only.';
        }

        if (!ctype_digit($old['type_id']) || !in_array((int)$old['type_id'], $typeIds, true)) {
            $errors['type_id'] = 'Choose an asset type from the list.';
        }
        if (!in_array($old['line_name'], LINES, true)) {
            $errors['line_name'] = 'Choose a railway line from the list.';
        }
        if (!preg_match('/^\d{1,4}(\.\d)?$/', $old['chainage_km'])) {
            $errors['chainage_km'] = 'Enter chainage in km, for example 45.2 (0 to 9999.9).';
        }

        $lat = $old['latitude'];
        $lon = $old['longitude'];
        if ($lat !== '' && (!is_numeric($lat) || (float)$lat < -90 || (float)$lat > 90)) {
            $errors['latitude'] = 'Latitude must be between -90 and 90.';
        }
        if ($lon !== '' && (!is_numeric($lon) || (float)$lon < -180 || (float)$lon > 180)) {
            $errors['longitude'] = 'Longitude must be between -180 and 180.';
        }
        if (!isset($errors['latitude'], $errors['longitude']) && (($lat === '') !== ($lon === ''))) {
            $errors['longitude'] = $errors['longitude'] ?? 'Enter both latitude and longitude, or leave both empty.';
        }

        if (!in_array($old['condition_status'], CONDITIONS, true)) {
            $errors['condition_status'] = 'Choose a condition rating from the list.';
        }

        $d = DateTime::createFromFormat('Y-m-d', $old['inspected_on']);
        if (!$d || $d->format('Y-m-d') !== $old['inspected_on']) {
            $errors['inspected_on'] = 'Choose a valid inspection date.';
        } elseif ($old['inspected_on'] > date('Y-m-d')) {
            $errors['inspected_on'] = 'The inspection date cannot be in the future.';
        }

        if (mb_strlen($old['notes']) > 500) {
            $errors['notes'] = 'Notes can have at most 500 characters.';
        }

        /* ---------- Duplicate check ---------- */
        if (!$errors) {
            $dup = $pdo->prepare('SELECT COUNT(*) FROM assets WHERE name = :n AND type_id = :t');
            $dup->execute([':n' => $old['name'], ':t' => (int)$old['type_id']]);
            if ((int)$dup->fetchColumn() > 0) {
                $errors['name'] = 'An asset with this name and type is already registered.';
            }
        }

        /* ---------- Save (transaction: asset + first inspection) ---------- */
        if (!$errors) {
            try {
                $pdo->beginTransaction();

                $ins = $pdo->prepare(
                    'INSERT INTO assets (asset_code, name, type_id, line_name, chainage_km,
                                         latitude, longitude, condition_status, created_by)
                     VALUES (:code, :name, :type, :line, :km, :lat, :lon, :cond, :uid)'
                );
                $userId = (int)(current_user()['id'] ?? 1);
                $ins->execute([
                    ':code' => 'TMP' . bin2hex(random_bytes(6)),
                    ':name' => $old['name'],
                    ':type' => (int)$old['type_id'],
                    ':line' => $old['line_name'],
                    ':km'   => $old['chainage_km'],
                    ':lat'  => $lat === '' ? null : $lat,
                    ':lon'  => $lon === '' ? null : $lon,
                    ':cond' => $old['condition_status'],
                    ':uid'  => $userId,
                ]);
                $assetId = (int)$pdo->lastInsertId();

                $prefix = '';
                foreach ($types as $t) {
                    if ((int)$t['id'] === (int)$old['type_id']) { $prefix = $t['prefix']; }
                }
                $code = $prefix . str_pad((string)$assetId, 3, '0', STR_PAD_LEFT);
                $pdo->prepare('UPDATE assets SET asset_code = :c WHERE id = :id')
                    ->execute([':c' => $code, ':id' => $assetId]);

                $pdo->prepare(
                    'INSERT INTO inspections (asset_id, inspector_id, inspected_on, condition_status, notes)
                     VALUES (:a, :u, :d, :c, :n)'
                )->execute([
                    ':a' => $assetId,
                    ':u' => $userId,
                    ':d' => $old['inspected_on'],
                    ':c' => $old['condition_status'],
                    ':n' => $old['notes'] === '' ? null : $old['notes'],
                ]);

                $pdo->commit();

                log_event($pdo, 'Asset create', 'Registered asset ' . $code . ' (' . $old['name'] . ').');
                flash_set('success', 'Inspection saved. ' . $old['name'] . ' is registered as ' . $code . '.');
                header('Location: index.php');
                exit;
            } catch (PDOException $ex) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
                error_log('assets_infra/create: ' . $ex->getMessage());
                $errors['_form'] = 'The inspection could not be saved. Try again, or contact the administrator if it keeps happening.';
            }
        }
    }
}

function field_error(array $errors, string $name): string
{
    $msg = $errors[$name] ?? '';
    return '<p class="field-error" id="err-' . e($name) . '"' . ($msg === '' ? ' hidden' : ' role="alert"') . '>' . e($msg) . '</p>';
}
function invalid(array $errors, string $name): string
{
    return isset($errors[$name]) ? ' aria-invalid="true"' : '';
}

$extraScripts = ['assets/js/asset_form.js'];
require __DIR__ . '/../includes/header.php';
?>

<div class="page-intro">
    <div>
        <h2>Log New Inspection</h2>
        <p>Register a railway asset and record its first structural inspection.</p>
    </div>
    <a class="btn btn-light" href="index.php">← Back to Assets</a>
</div>

<?php if (isset($errors['_form'])): ?>
    <div class="alert error" role="alert"><?= e($errors['_form']) ?></div>
<?php elseif ($errors): ?>
    <div class="alert error" role="alert">Fix the highlighted fields and save again.</div>
<?php endif; ?>

<form id="assetForm" class="card" style="max-width: 850px;" method="post" action="create.php" novalidate>
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

    <fieldset class="form-section">
        <legend>Asset Details</legend>
        <div class="form-grid">
            <div class="field full">
                <label for="name">ASSET NAME</label>
                <input id="name" name="name" type="text" maxlength="100" required value="<?= e($old['name']) ?>"<?= invalid($errors, 'name') ?> aria-describedby="err-name">
                <?= field_error($errors, 'name') ?>
            </div>
            <div class="field">
                <label for="type_id">ASSET TYPE</label>
                <select id="type_id" name="type_id" required<?= invalid($errors, 'type_id') ?> aria-describedby="err-type_id">
                    <option value="">Choose a type</option>
                    <?php foreach ($types as $t): ?>
                        <option value="<?= (int)$t['id'] ?>"<?= $old['type_id'] === (string)$t['id'] ? ' selected' : '' ?>><?= e($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= field_error($errors, 'type_id') ?>
            </div>
            <div class="field">
                <label for="line_name">RAILWAY LINE</label>
                <select id="line_name" name="line_name" required<?= invalid($errors, 'line_name') ?> aria-describedby="err-line_name">
                    <option value="">Choose a line</option>
                    <?php foreach (LINES as $l): ?>
                        <option<?= $old['line_name'] === $l ? ' selected' : '' ?>><?= e($l) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= field_error($errors, 'line_name') ?>
            </div>
            <div class="field">
                <label for="chainage_km">CHAINAGE (KM)</label>
                <input id="chainage_km" name="chainage_km" type="text" inputmode="decimal" required value="<?= e($old['chainage_km']) ?>"<?= invalid($errors, 'chainage_km') ?> aria-describedby="hint-km err-chainage_km">
                <p class="hint" id="hint-km">Distance along the line, one decimal place.</p>
                <?= field_error($errors, 'chainage_km') ?>
            </div>
            <div class="field">
                <label for="latitude">LATITUDE (OPTIONAL)</label>
                <input id="latitude" name="latitude" type="text" inputmode="decimal" value="<?= e($old['latitude']) ?>"<?= invalid($errors, 'latitude') ?> aria-describedby="err-latitude">
                <?= field_error($errors, 'latitude') ?>
            </div>
            <div class="field">
                <label for="longitude">LONGITUDE (OPTIONAL)</label>
                <input id="longitude" name="longitude" type="text" inputmode="decimal" value="<?= e($old['longitude']) ?>"<?= invalid($errors, 'longitude') ?> aria-describedby="hint-gps err-longitude">
                <p class="hint" id="hint-gps">Used to place the asset on the GIS map.</p>
                <?= field_error($errors, 'longitude') ?>
            </div>
        </div>
    </fieldset>

    <fieldset class="form-section">
        <legend>Structural Inspection</legend>
        <div class="form-grid">
            <div class="field">
                <label for="condition_status">CONDITION RATING</label>
                <select id="condition_status" name="condition_status" required<?= invalid($errors, 'condition_status') ?> aria-describedby="err-condition_status">
                    <?php foreach (CONDITIONS as $c): ?>
                        <option<?= $old['condition_status'] === $c ? ' selected' : '' ?>><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= field_error($errors, 'condition_status') ?>
            </div>
            <div class="field">
                <label for="inspected_on">INSPECTION DATE</label>
                <input id="inspected_on" name="inspected_on" type="date" required max="<?= e(date('Y-m-d')) ?>" value="<?= e($old['inspected_on']) ?>"<?= invalid($errors, 'inspected_on') ?> aria-describedby="err-inspected_on">
                <?= field_error($errors, 'inspected_on') ?>
            </div>
            <div class="field full">
                <label for="notes">INSPECTION NOTES (OPTIONAL)</label>
                <textarea id="notes" name="notes" rows="4" maxlength="500"<?= invalid($errors, 'notes') ?> aria-describedby="notesCount err-notes"><?= e($old['notes']) ?></textarea>
                <p class="hint" id="notesCount">0 / 500</p>
                <?= field_error($errors, 'notes') ?>
            </div>
        </div>
    </fieldset>

    <div class="form-actions">
        <button class="btn btn-primary" type="submit">Save Inspection</button>
        <a class="btn btn-light" href="index.php">Cancel</a>
    </div>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>

<?php
declare(strict_types=1);

/**
 * assets_infra/index.php - Infrastructure Assets Directory
 * READ: browse, search, filter, sort and paginate railway assets.
 */
require_once __DIR__ . '/../includes/assets.php';

require_login();

$pageTitle = 'Infrastructure Assets';
$activePage = 'assets';

/* ---------- 1. Read and whitelist every filter ---------- */
$q      = mb_substr(str_in($_GET, 'q'), 0, 60);
$typeId = (int)str_in($_GET, 'type');
$cond   = in_array(str_in($_GET, 'cond'), CONDITIONS, true) ? str_in($_GET, 'cond') : '';
$line   = in_array(str_in($_GET, 'line'), LINES, true) ? str_in($_GET, 'line') : '';

$sortCols = [
    'code'      => 'a.asset_code',
    'name'      => 'a.name',
    'type'      => 't.name',
    'condition' => 'a.condition_status',
    'inspected' => 'last_inspected',
];
$sort = array_key_exists(str_in($_GET, 'sort'), $sortCols) ? str_in($_GET, 'sort') : 'code';
$dir  = str_in($_GET, 'dir') === 'desc' ? 'desc' : 'asc';

$perPage = 10;
$page    = max(1, (int)str_in($_GET, 'page'));

/* ---------- 2. Build the WHERE clause with prepared-statement parameters ---------- */
$where  = [];
$params = [];
if ($q !== '') {
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $where[]       = '(a.name LIKE :q1 OR a.asset_code LIKE :q2)';
    $params[':q1'] = $like;
    $params[':q2'] = $like;
}
if ($typeId > 0) { $where[] = 'a.type_id = :type';           $params[':type'] = $typeId; }
if ($cond !== '') { $where[] = 'a.condition_status = :cond'; $params[':cond'] = $cond; }
if ($line !== '') { $where[] = 'a.line_name = :line';        $params[':line'] = $line; }
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

/* ---------- 3. Query ---------- */
$assets = [];
$total = 0;
$stats = [];
$types = [];
$dbError = false;

try {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM assets a JOIN asset_types t ON t.id = a.type_id $whereSql");
    $countStmt->execute($params);
    $total      = (int)$countStmt->fetchColumn();
    $totalPages = max(1, (int)ceil($total / $perPage));
    $page       = min($page, $totalPages);
    $offset     = ($page - 1) * $perPage;

    $orderSql = $sortCols[$sort] . ' ' . strtoupper($dir) . ', a.id ASC';
    $sql = "SELECT a.id, a.asset_code, a.name, t.name AS type_name, a.line_name, a.chainage_km,
                   a.condition_status,
                   (SELECT MAX(i.inspected_on) FROM inspections i WHERE i.asset_id = a.id) AS last_inspected
            FROM assets a
            JOIN asset_types t ON t.id = a.type_id
            $whereSql
            ORDER BY $orderSql
            LIMIT :lim OFFSET :off";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
    $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $assets = $stmt->fetchAll();

    $stats = $pdo->query('SELECT condition_status, COUNT(*) FROM assets GROUP BY condition_status')
                 ->fetchAll(PDO::FETCH_KEY_PAIR);
    $types = $pdo->query('SELECT id, name FROM asset_types ORDER BY name')->fetchAll();
} catch (PDOException $ex) {
    error_log('assets_infra/index: ' . $ex->getMessage());
    $dbError    = true;
    $totalPages = 1;
}

/* ---------- 4. Small view helpers ---------- */
function list_url(array $override = []): string
{
    global $q, $typeId, $cond, $line, $sort, $dir, $page;
    $p = array_merge([
        'q' => $q, 'type' => $typeId ?: '', 'cond' => $cond, 'line' => $line,
        'sort' => $sort, 'dir' => $dir, 'page' => $page,
    ], $override);
    $p = array_filter($p, fn($v) => $v !== '' && $v !== null);
    return 'index.php' . ($p ? '?' . http_build_query($p) : '');
}

function sort_th(string $key, string $label): string
{
    global $sort, $dir;
    $isCur = ($sort === $key);
    $next  = ($isCur && $dir === 'asc') ? 'desc' : 'asc';
    $arrow = $isCur ? ($dir === 'asc' ? ' &#9650;' : ' &#9660;') : '';
    $aria  = $isCur ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none';
    return '<th scope="col" aria-sort="' . $aria . '"><a href="' . e(list_url(['sort' => $key, 'dir' => $next, 'page' => 1]))
         . '">' . e($label) . $arrow . '</a></th>';
}

$flash = flash_get();
$hasFilter = ($q !== '' || $typeId > 0 || $cond !== '' || $line !== '');
$extraScripts = ['assets/js/assets_list.js'];

require __DIR__ . '/../includes/header.php';
?>

<div class="page-intro">
    <div>
        <h2>Infrastructure Assets</h2>
        <p>Tracks, overhead catenary lines, bridges and tunnels under monitoring.</p>
    </div>
    <?php if (can_manage()): ?>
        <a class="btn btn-primary" href="create.php">＋ Log New Inspection</a>
    <?php endif; ?>
</div>

<?php if ($flash): ?>
    <div class="alert <?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div>
<?php endif; ?>
<?php if ($dbError): ?>
    <div class="alert error" role="alert">The asset list could not be loaded. Check the database connection and try again.</div>
<?php endif; ?>

<div class="stats">
    <div class="stat">
        <span class="stat-icon">↗</span>
        <div class="stat-label">Total Assets</div>
        <div class="stat-value"><?= (int)array_sum($stats) ?></div>
        <div class="stat-note">Registered infrastructure</div>
    </div>
    <div class="stat good">
        <span class="stat-icon">✓</span>
        <div class="stat-label">Good Condition</div>
        <div class="stat-value"><?= (int)($stats['Good'] ?? 0) ?></div>
        <div class="stat-note">Normal operating state</div>
    </div>
    <div class="stat fair">
        <span class="stat-icon">!</span>
        <div class="stat-label">Fair Condition</div>
        <div class="stat-value"><?= (int)($stats['Fair'] ?? 0) ?></div>
        <div class="stat-note">Scheduled monitoring</div>
    </div>
    <div class="stat poor">
        <span class="stat-icon">⚠</span>
        <div class="stat-label">Poor Condition</div>
        <div class="stat-value"><?= (int)($stats['Poor'] ?? 0) ?></div>
        <div class="stat-note">Requires urgent repair</div>
    </div>
</div>

<form class="card filters" method="get" action="index.php" role="search" aria-label="Search and filter assets">
    <div>
        <label for="q">Search by Name or Code</label>
        <input id="q" name="q" type="search" maxlength="60" value="<?= e($q) ?>" placeholder="e.g. Muar or BRG003">
    </div>
    <div>
        <label for="type">Asset Type</label>
        <select id="type" name="type" data-autosubmit>
            <option value="">All types</option>
            <?php foreach ($types as $t): ?>
                <option value="<?= (int)$t['id'] ?>"<?= $typeId === (int)$t['id'] ? ' selected' : '' ?>><?= e($t['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="cond">Condition</label>
        <select id="cond" name="cond" data-autosubmit>
            <option value="">All conditions</option>
            <?php foreach (CONDITIONS as $c): ?>
                <option<?= $cond === $c ? ' selected' : '' ?>><?= e($c) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="line">Railway Line</label>
        <select id="line" name="line" data-autosubmit>
            <option value="">All lines</option>
            <?php foreach (LINES as $l): ?>
                <option<?= $line === $l ? ' selected' : '' ?>><?= e($l) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div style="display:flex; gap:8px;">
        <button class="btn" type="submit">Search</button>
        <?php if ($hasFilter): ?><a class="btn btn-light" href="index.php">Clear</a><?php endif; ?>
    </div>
</form>

<section class="card">
    <p class="stat-note" style="margin-bottom: 12px;" aria-live="polite">
        <?= $total ?> asset<?= $total === 1 ? '' : 's' ?> found<?= $hasFilter ? ' for the current filters' : '' ?>.
    </p>
    <?php if (!$assets): ?>
        <div class="empty">
            <?= $hasFilter ? 'No assets match these filters. Clear the search or choose another type.' : 'No assets are registered yet. Log the first inspection to get started.' ?>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <?= sort_th('code', 'Code') ?>
                        <?= sort_th('name', 'Asset Name') ?>
                        <?= sort_th('type', 'Type') ?>
                        <th scope="col">Line</th>
                        <th scope="col">Chainage (km)</th>
                        <?= sort_th('condition', 'Condition') ?>
                        <?= sort_th('inspected', 'Last Inspected') ?>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($assets as $a): ?>
                    <?php
                        $badgeClass = 'badge-green';
                        if ($a['condition_status'] === 'Fair') { $badgeClass = 'badge-amber'; }
                        elseif ($a['condition_status'] === 'Poor') { $badgeClass = 'badge-red'; }
                    ?>
                    <tr>
                        <td><b><?= e($a['asset_code']) ?></b></td>
                        <td><?= e($a['name']) ?></td>
                        <td><?= e($a['type_name']) ?></td>
                        <td><?= e($a['line_name']) ?></td>
                        <td><?= e(number_format((float)$a['chainage_km'], 1)) ?></td>
                        <td><span class="badge <?= $badgeClass ?>"><?= e($a['condition_status']) ?></span></td>
                        <td><?= e($a['last_inspected'] ?? 'Not yet inspected') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav class="pager" aria-label="Pagination">
                <?php if ($page > 1): ?><a href="<?= e(list_url(['page' => $page - 1])) ?>">Previous</a><?php else: ?><span class="dis">Previous</span><?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i === $page): ?><span class="cur" aria-current="page"><?= $i ?></span>
                    <?php else: ?><a href="<?= e(list_url(['page' => $i])) ?>"><?= $i ?></a><?php endif; ?>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?><a href="<?= e(list_url(['page' => $page + 1])) ?>">Next</a><?php else: ?><span class="dis">Next</span><?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>


<?php

require_once __DIR__ . '/../config/app.php';

require_login();


// Page configuration
$pageTitle = 'Station Management';
$activePage = 'stations';


// Get current action and record ID
$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);
$error = '';


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Check permission to manage records
    if (!can_manage()) {
        http_response_code(403);
        exit('You do not have permission to change records.');
    }

    $mode = $_POST['mode'] ?? 'create';

    try {

        // Delete station record
        if ($mode === 'delete') {

            $stmt = $pdo->prepare(
                "DELETE FROM stations WHERE id = ?"
            );

            $stmt->execute([$id]);

            log_event(
                $pdo,
                'Delete',
                'Deleted a station record.'
            );

            header('Location: index.php');
            exit;
        }


        // Collect and clean form data
        $stationCode = trim((string)($_POST['station_code'] ?? ''));
        $stationName = trim((string)($_POST['station_name'] ?? ''));
        $city = trim((string)($_POST['city'] ?? ''));
        $platformCount = trim((string)($_POST['platform_count'] ?? ''));
        $status = trim((string)($_POST['status'] ?? ''));

        $values = [
            $stationCode,
            $stationName,
            $city,
            $platformCount,
            $status
        ];


        // Update existing station
        if ($mode === 'update') {

            $values[] = $id;

            $stmt = $pdo->prepare(
                "UPDATE stations
                 SET station_code = ?,
                     station_name = ?,
                     city = ?,
                     platform_count = ?,
                     status = ?
                 WHERE id = ?"
            );

            $stmt->execute($values);

            log_event(
                $pdo,
                'Update',
                'Updated a station record.'
            );

        } else {

            // Create new station
            $stmt = $pdo->prepare(
                "INSERT INTO stations
                    (station_code, station_name, city, platform_count, status)
                 VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->execute($values);

            log_event(
                $pdo,
                'Create',
                'Created a station record.'
            );
        }


        // Redirect after successful operation
        header('Location: index.php');
        exit;

    } catch (Throwable $ex) {

        $error = 'Unable to save this record. Check that the values are valid and unique.';
    }
}


// Retrieve all station records
$rows = $pdo->query(
    "SELECT * FROM stations ORDER BY station_name"
)->fetchAll();


// Retrieve station details for editing
$record = null;

if ($action === 'edit' && $id) {

    $stmt = $pdo->prepare(
        "SELECT * FROM stations WHERE id = ?"
    );

    $stmt->execute([$id]);

    $record = $stmt->fetch();

    if (!$record) {
        $action = 'list';
    }
}


// Include page header
require __DIR__ . '/../includes/header.php';

?>

<!-- Page introduction -->
<div class="page-intro">

    <div>
        <h2>Station Management</h2>
        <p>Manage and maintain your railway operations records.</p>
    </div>

    <?php if ($action === 'list' && can_manage()): ?>
        <a class="btn btn-primary" href="?action=create">
            ＋ Add new
        </a>
    <?php endif; ?>

</div>


<?php if ($action === 'create' || $action === 'edit'): ?>

    <!-- Create / Edit Station Form -->
    <section class="card" style="max-width:850px">

        <h3 class="section-title">
            <?= $action === 'edit' ? 'Edit record' : 'Create new record' ?>
        </h3>

        <?php if ($error): ?>
            <div class="alert">
                <?= e($error) ?>
            </div>
        <?php endif; ?>


        <form method="post">

            <input
                type="hidden"
                name="mode"
                value="<?= $action === 'edit' ? 'update' : 'create' ?>"
            >

            <div class="form-grid">

                <!-- Station Code -->
                <div class="field">
                    <label>STATION CODE</label>

                    <input
                        type="text"
                        name="station_code"
                        value="<?= e($record['station_code'] ?? '') ?>"
                        required
                    >
                </div>


                <!-- Station Name -->
                <div class="field">
                    <label>STATION NAME</label>

                    <input
                        type="text"
                        name="station_name"
                        value="<?= e($record['station_name'] ?? '') ?>"
                        required
                    >
                </div>


                <!-- City / State -->
                <div class="field">
                    <label>CITY / STATE</label>

                    <input
                        type="text"
                        name="city"
                        value="<?= e($record['city'] ?? '') ?>"
                        required
                    >
                </div>


                <!-- Platform Count -->
                <div class="field">
                    <label>NUMBER OF PLATFORMS</label>

                    <input
                        type="number"
                        name="platform_count"
                        value="<?= e($record['platform_count'] ?? '') ?>"
                        required
                    >
                </div>


                <!-- Station Status -->
                <div class="field">
                    <label>STATION STATUS</label>

                    <select name="status" required>

                        <option
                            value="Active"
                            <?= (($record['status'] ?? '') === 'Active') ? 'selected' : '' ?>
                        >
                            Active
                        </option>

                        <option
                            value="Maintenance"
                            <?= (($record['status'] ?? '') === 'Maintenance') ? 'selected' : '' ?>
                        >
                            Maintenance
                        </option>

                        <option
                            value="Closed"
                            <?= (($record['status'] ?? '') === 'Closed') ? 'selected' : '' ?>
                        >
                            Closed
                        </option>

                    </select>
                </div>

            </div>


            <!-- Form Actions -->
            <div class="form-actions">

                <button class="btn btn-primary">
                    Save record
                </button>

                <a class="btn btn-light" href="index.php">
                    Cancel
                </a>

            </div>

        </form>

    </section>


<?php else: ?>

    <!-- Station Records Table -->
    <section class="card">

        <div class="toolbar">

            <input
                class="search"
                id="tableSearch"
                placeholder="Search records..."
                oninput="filterTable()"
            >

        </div>


        <div class="table-wrap">

            <table id="dataTable">

                <tr>
                    <th>Code</th>
                    <th>Station</th>
                    <th>City / state</th>
                    <th>Platforms</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>


                <?php foreach ($rows as $row): ?>

                    <tr>

                        <td><?= e($row['station_code'] ?? '') ?></td>

                        <td><?= e($row['station_name'] ?? '') ?></td>

                        <td><?= e($row['city'] ?? '') ?></td>

                        <td><?= e($row['platform_count'] ?? '') ?></td>

                        <td><?= e($row['status'] ?? '') ?></td>


                        <!-- Record Actions -->
                        <td>

                            <?php if (can_manage()): ?>

                                <a
                                    class="btn btn-light"
                                    href="?action=edit&id=<?= (int)$row['id'] ?>"
                                >
                                    Edit
                                </a>


                                <form
                                    method="post"
                                    action="?action=delete&id=<?= (int)$row['id'] ?>"
                                    style="display:inline"
                                    onsubmit="return confirm('Delete this record?')"
                                >

                                    <input
                                        type="hidden"
                                        name="mode"
                                        value="delete"
                                    >

                                    <button class="btn btn-danger">
                                        Delete
                                    </button>

                                </form>

                            <?php else: ?>

                                <span class="badge">
                                    View only
                                </span>

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endforeach; ?>


                <?php if (!$rows): ?>

                    <tr>
                        <td colspan="20" class="empty">
                            No records available yet.
                        </td>
                    </tr>

                <?php endif; ?>

            </table>

        </div>

    </section>

<?php endif; ?>


<!-- Table Search Function -->
<script>

function filterTable() {

    let query = document
        .getElementById('tableSearch')
        .value
        .toLowerCase();

    document
        .querySelectorAll('#dataTable tr:not(:first-child)')
        .forEach(row => {

            row.style.display = row.innerText
                .toLowerCase()
                .includes(query) ? '' : 'none';

        });

}

</script>


<?php

// Include page footer
require __DIR__ . '/../includes/footer.php';

?>

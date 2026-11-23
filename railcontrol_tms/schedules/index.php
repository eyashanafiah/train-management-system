
<?php

require_once __DIR__ . '/../config/app.php';

require_login();


// Page configuration
$pageTitle = 'Schedule Management';
$activePage = 'schedules';


// Get current action and schedule ID
$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);
$error = '';


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Check user permission
    if (!can_manage()) {
        http_response_code(403);
        exit('You do not have permission to change records.');
    }

    $mode = $_POST['mode'] ?? 'create';

    try {

        // Delete schedule record
        if ($mode === 'delete') {

            $stmt = $pdo->prepare(
                "DELETE FROM schedules WHERE id = ?"
            );

            $stmt->execute([$id]);

            log_event(
                $pdo,
                'Delete',
                'Deleted a schedules record.'
            );

            header('Location: index.php');
            exit;
        }


        // Collect and clean form data
        $scheduleCode = trim((string)($_POST['schedule_code'] ?? ''));
        $trainId = trim((string)($_POST['train_id'] ?? ''));
        $routeId = trim((string)($_POST['route_id'] ?? ''));
        $departureTime = trim((string)($_POST['departure_time'] ?? ''));
        $arrivalTime = trim((string)($_POST['arrival_time'] ?? ''));

        $values = [
            $scheduleCode,
            $trainId,
            $routeId,
            $departureTime,
            $arrivalTime
        ];


        // Update existing schedule
        if ($mode === 'update') {

            $values[] = $id;

            $stmt = $pdo->prepare(
                "UPDATE schedules
                 SET schedule_code = ?,
                     train_id = ?,
                     route_id = ?,
                     departure_time = ?,
                     arrival_time = ?
                 WHERE id = ?"
            );

            $stmt->execute($values);

            log_event(
                $pdo,
                'Update',
                'Updated a schedules record.'
            );

        } else {

            // Create new schedule
            $stmt = $pdo->prepare(
                "INSERT INTO schedules
                    (
                        schedule_code,
                        train_id,
                        route_id,
                        departure_time,
                        arrival_time
                    )
                 VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->execute($values);

            log_event(
                $pdo,
                'Create',
                'Created a schedules record.'
            );
        }


        // Redirect after successful operation
        header('Location: index.php');
        exit;

    } catch (Throwable $ex) {

        $error = 'Unable to save this record. Check that the values are valid and unique.';
    }
}


// Retrieve schedules with train and route information
$rows = $pdo->query(
    "SELECT
        s.*,
        t.train_code,
        r.route_name
     FROM schedules s
     JOIN trains t
        ON s.train_id = t.id
     JOIN routes r
        ON s.route_id = r.id
     ORDER BY s.departure_time DESC"
)->fetchAll();


// Retrieve schedule details for editing
$record = null;

if ($action === 'edit' && $id) {

    $stmt = $pdo->prepare(
        "SELECT * FROM schedules WHERE id = ?"
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


<!-- Page Introduction -->
<div class="page-intro">

    <div>
        <h2>Schedule Management</h2>

        <p>
            Manage and maintain your railway operations records.
        </p>
    </div>

    <?php if ($action === 'list' && can_manage()): ?>

        <a class="btn btn-primary" href="?action=create">
            ＋ Add new
        </a>

    <?php endif; ?>

</div>


<?php if ($action === 'create' || $action === 'edit'): ?>

    <!-- Create / Edit Schedule Form -->
    <section class="card" style="max-width:850px">

        <h3 class="section-title">
            <?= $action === 'edit' ? 'Edit record' : 'Create new record' ?>
        </h3>


        <!-- Error Message -->
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

                <!-- Schedule Code -->
                <div class="field">

                    <label>SCHEDULE CODE</label>

                    <input
                        type="text"
                        name="schedule_code"
                        value="<?= e($record['schedule_code'] ?? '') ?>"
                        required
                    >

                </div>


                <!-- Train ID -->
                <div class="field">

                    <label>TRAIN ID</label>

                    <input
                        type="number"
                        name="train_id"
                        value="<?= e($record['train_id'] ?? '') ?>"
                        required
                    >

                </div>


                <!-- Route ID -->
                <div class="field">

                    <label>ROUTE ID</label>

                    <input
                        type="number"
                        name="route_id"
                        value="<?= e($record['route_id'] ?? '') ?>"
                        required
                    >

                </div>


                <!-- Departure Date and Time -->
                <div class="field">

                    <label>DEPARTURE DATE AND TIME</label>

                    <input
                        type="datetime-local"
                        name="departure_time"
                        value="<?= e($record['departure_time'] ?? '') ?>"
                        required
                    >

                </div>


                <!-- Arrival Date and Time -->
                <div class="field">

                    <label>ARRIVAL DATE AND TIME</label>

                    <input
                        type="datetime-local"
                        name="arrival_time"
                        value="<?= e($record['arrival_time'] ?? '') ?>"
                        required
                    >

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

    <!-- Schedule Records Table -->
    <section class="card">

        <!-- Search Toolbar -->
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

                <!-- Table Header -->
                <tr>
                    <th>Schedule</th>
                    <th>Train</th>
                    <th>Route</th>
                    <th>Departure</th>
                    <th>Arrival</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>


                <!-- Display Schedule Records -->
                <?php foreach ($rows as $row): ?>

                    <tr>

                        <!-- Schedule Code -->
                        <td>
                            <?= e($row['schedule_code'] ?? '') ?>
                        </td>


                        <!-- Train Code -->
                        <td>
                            <?= e($row['train_code'] ?? '') ?>
                        </td>


                        <!-- Route Name -->
                        <td>
                            <?= e($row['route_name'] ?? '') ?>
                        </td>


                        <!-- Departure Time -->
                        <td>
                            <?= e($row['departure_time'] ?? '') ?>
                        </td>


                        <!-- Arrival Time -->
                        <td>
                            <?= e($row['arrival_time'] ?? '') ?>
                        </td>


                        <!-- Schedule Status -->
                        <td>
                            <?= e($row['schedule_status'] ?? '') ?>
                        </td>


                        <!-- Record Actions -->
                        <td>

                            <?php if (can_manage()): ?>

                                <!-- Edit Button -->
                                <a
                                    class="btn btn-light"
                                    href="?action=edit&id=<?= (int)$row['id'] ?>"
                                >
                                    Edit
                                </a>


                                <!-- Delete Button -->
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


                <!-- Empty Records Message -->
                <?php if (!$rows): ?>

                    <tr>
                        <td colspan="7" class="empty">
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

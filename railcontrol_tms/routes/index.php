
<?php

require_once __DIR__ . '/../config/app.php';

require_login();


// Page configuration
$pageTitle = 'Route Management';
$activePage = 'routes';


// Get current action and route ID
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

        // Delete route record
        if ($mode === 'delete') {

            $stmt = $pdo->prepare(
                "DELETE FROM routes WHERE id = ?"
            );

            $stmt->execute([$id]);

            log_event(
                $pdo,
                'Delete',
                'Deleted a routes record.'
            );

            header('Location: index.php');
            exit;
        }


        // Collect and clean form data
        $routeCode = trim((string)($_POST['route_code'] ?? ''));
        $routeName = trim((string)($_POST['route_name'] ?? ''));
        $originStationId = trim((string)($_POST['origin_station_id'] ?? ''));
        $destinationStationId = trim((string)($_POST['destination_station_id'] ?? ''));
        $distanceKm = trim((string)($_POST['distance_km'] ?? ''));

        $values = [
            $routeCode,
            $routeName,
            $originStationId,
            $destinationStationId,
            $distanceKm
        ];


        // Update existing route
        if ($mode === 'update') {

            $values[] = $id;

            $stmt = $pdo->prepare(
                "UPDATE routes
                 SET route_code = ?,
                     route_name = ?,
                     origin_station_id = ?,
                     destination_station_id = ?,
                     distance_km = ?
                 WHERE id = ?"
            );

            $stmt->execute($values);

            log_event(
                $pdo,
                'Update',
                'Updated a routes record.'
            );

        } else {

            // Create new route
            $stmt = $pdo->prepare(
                "INSERT INTO routes
                    (
                        route_code,
                        route_name,
                        origin_station_id,
                        destination_station_id,
                        distance_km
                    )
                 VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->execute($values);

            log_event(
                $pdo,
                'Create',
                'Created a routes record.'
            );
        }


        // Redirect after successful operation
        header('Location: index.php');
        exit;

    } catch (Throwable $ex) {

        $error = 'Unable to save this record. Check that the values are valid and unique.';
    }
}


// Retrieve routes with origin and destination stations
$rows = $pdo->query(
    "SELECT
        r.*,
        CONCAT(o.station_name, ' → ', d.station_name) AS station_pair
     FROM routes r
     JOIN stations o
        ON r.origin_station_id = o.id
     JOIN stations d
        ON r.destination_station_id = d.id
     ORDER BY r.id DESC"
)->fetchAll();


// Retrieve route details for editing
$record = null;

if ($action === 'edit' && $id) {

    $stmt = $pdo->prepare(
        "SELECT * FROM routes WHERE id = ?"
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
        <h2>Route Management</h2>

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

    <!-- Create / Edit Route Form -->
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

                <!-- Route Code -->
                <div class="field">

                    <label>ROUTE CODE</label>

                    <input
                        type="text"
                        name="route_code"
                        value="<?= e($record['route_code'] ?? '') ?>"
                        required
                    >

                </div>


                <!-- Route Name -->
                <div class="field">

                    <label>ROUTE NAME</label>

                    <input
                        type="text"
                        name="route_name"
                        value="<?= e($record['route_name'] ?? '') ?>"
                        required
                    >

                </div>


                <!-- Origin Station ID -->
                <div class="field">

                    <label>ORIGIN STATION ID</label>

                    <input
                        type="number"
                        name="origin_station_id"
                        value="<?= e($record['origin_station_id'] ?? '') ?>"
                        required
                    >

                </div>


                <!-- Destination Station ID -->
                <div class="field">

                    <label>DESTINATION STATION ID</label>

                    <input
                        type="number"
                        name="destination_station_id"
                        value="<?= e($record['destination_station_id'] ?? '') ?>"
                        required
                    >

                </div>


                <!-- Distance -->
                <div class="field">

                    <label>DISTANCE (KM)</label>

                    <input
                        type="number"
                        name="distance_km"
                        value="<?= e($record['distance_km'] ?? '') ?>"
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

    <!-- Route Records Table -->
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
                    <th>Code</th>
                    <th>Route</th>
                    <th>Stations</th>
                    <th>Distance (km)</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>


                <!-- Display Route Records -->
                <?php foreach ($rows as $row): ?>

                    <tr>

                        <!-- Route Code -->
                        <td>
                            <?= e($row['route_code'] ?? '') ?>
                        </td>


                        <!-- Route Name -->
                        <td>
                            <?= e($row['route_name'] ?? '') ?>
                        </td>


                        <!-- Origin and Destination -->
                        <td>
                            <?= e($row['station_pair'] ?? '') ?>
                        </td>


                        <!-- Distance -->
                        <td>
                            <?= e($row['distance_km'] ?? '') ?>
                        </td>


                        <!-- Route Status -->
                        <td>
                            <?= e($row['status'] ?? '') ?>
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
                        <td colspan="6" class="empty">
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

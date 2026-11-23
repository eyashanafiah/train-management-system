
<?php

require_once __DIR__ . '/../config/app.php';

require_login();


// Page configuration
$pageTitle = 'Train Management';
$activePage = 'trains';


// Initialize variables
$action = $_GET['action'] ?? 'list';
$id = (int) ($_GET['id'] ?? 0);
$error = '';


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Check user permissions
    if (!can_manage()) {
        http_response_code(403);
        exit('You do not have permission to change records.');
    }

    $mode = $_POST['mode'] ?? 'create';

    try {

        // Delete train record
        if ($mode === 'delete') {

            $st = $pdo->prepare("
                DELETE FROM trains
                WHERE id = ?
            ");

            $st->execute([$id]);

            log_event(
                $pdo,
                'Delete',
                'Deleted a trains record.'
            );

            header('Location: index.php');
            exit;
        }


        // Collect form values
        $vals = [];

        $vals[] = trim((string) ($_POST['train_code'] ?? ''));
        $vals[] = trim((string) ($_POST['train_name'] ?? ''));
        $vals[] = trim((string) ($_POST['train_type'] ?? ''));
        $vals[] = trim((string) ($_POST['capacity'] ?? ''));
        $vals[] = trim((string) ($_POST['status'] ?? ''));


        // Update existing train record
        if ($mode === 'update') {

            $vals[] = $id;

            $st = $pdo->prepare("
                UPDATE trains
                SET
                    train_code = ?,
                    train_name = ?,
                    train_type = ?,
                    capacity = ?,
                    status = ?
                WHERE id = ?
            ");

            $st->execute($vals);

            log_event(
                $pdo,
                'Update',
                'Updated a trains record.'
            );

        } else {

            // Create new train record
            $st = $pdo->prepare("
                INSERT INTO trains (
                    train_code,
                    train_name,
                    train_type,
                    capacity,
                    status
                )
                VALUES (?, ?, ?, ?, ?)
            ");

            $st->execute($vals);

            log_event(
                $pdo,
                'Create',
                'Created a trains record.'
            );

        }


        // Redirect after successful operation
        header('Location: index.php');
        exit;


    } catch (Throwable $ex) {

        // Display error message
        $error = 'Unable to save this record. Check that the values are valid and unique.';

    }

}


// Retrieve all train records
$rows = $pdo->query("
    SELECT *
    FROM trains
    ORDER BY id DESC
")->fetchAll();


// Retrieve selected train for editing
$record = null;

if ($action === 'edit' && $id) {

    $st = $pdo->prepare("
        SELECT *
        FROM trains
        WHERE id = ?
    ");

    $st->execute([$id]);

    $record = $st->fetch();

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

        <h2>
            Train Management
        </h2>

        <p>
            Manage and maintain your railway operations records.
        </p>

    </div>


    <!-- Add New Train Button -->
    <?php if ($action === 'list' && can_manage()): ?>

        <a class="btn btn-primary" href="?action=create">
            ＋ Add New
        </a>

    <?php endif; ?>

</div>



<!-- Create / Edit Train Form -->
<?php if ($action === 'create' || $action === 'edit'): ?>

    <section class="card" style="max-width: 850px">

        <h3 class="section-title">

            <?= $action === 'edit'
                ? 'Edit Record'
                : 'Create New Record'
            ?>

        </h3>


        <!-- Display Error Message -->
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


                <!-- Train Code -->
                <div class="field">

                    <label>TRAIN CODE</label>

                    <input
                        type="text"
                        name="train_code"
                        value="<?= e($record['train_code'] ?? '') ?>"
                        required
                    >

                </div>


                <!-- Train Name -->
                <div class="field">

                    <label>TRAIN NAME</label>

                    <input
                        type="text"
                        name="train_name"
                        value="<?= e($record['train_name'] ?? '') ?>"
                        required
                    >

                </div>


                <!-- Train Type -->
                <div class="field">

                    <label>TRAIN TYPE</label>

                    <select name="train_type" required>

                        <option
                            value="Passenger"
                            <?= (($record['train_type'] ?? '') === 'Passenger') ? 'selected' : '' ?>
                        >
                            Passenger
                        </option>

                        <option
                            value="Cargo"
                            <?= (($record['train_type'] ?? '') === 'Cargo') ? 'selected' : '' ?>
                        >
                            Cargo
                        </option>

                        <option
                            value="Maintenance"
                            <?= (($record['train_type'] ?? '') === 'Maintenance') ? 'selected' : '' ?>
                        >
                            Maintenance
                        </option>

                    </select>

                </div>


                <!-- Train Capacity -->
                <div class="field">

                    <label>PASSENGER / LOAD CAPACITY</label>

                    <input
                        type="number"
                        name="capacity"
                        value="<?= e($record['capacity'] ?? '') ?>"
                        required
                    >

                </div>


                <!-- Operational Status -->
                <div class="field">

                    <label>OPERATIONAL STATUS</label>

                    <select name="status" required>

                        <option
                            value="Available"
                            <?= (($record['status'] ?? '') === 'Available') ? 'selected' : '' ?>
                        >
                            Available
                        </option>

                        <option
                            value="In Service"
                            <?= (($record['status'] ?? '') === 'In Service') ? 'selected' : '' ?>
                        >
                            In Service
                        </option>

                        <option
                            value="Under Maintenance"
                            <?= (($record['status'] ?? '') === 'Under Maintenance') ? 'selected' : '' ?>
                        >
                            Under Maintenance
                        </option>

                        <option
                            value="Out of Service"
                            <?= (($record['status'] ?? '') === 'Out of Service') ? 'selected' : '' ?>
                        >
                            Out of Service
                        </option>

                    </select>

                </div>


            </div>


            <!-- Form Actions -->
            <div class="form-actions">

                <button type="submit" class="btn btn-primary">
                    Save Record
                </button>

                <a class="btn btn-light" href="index.php">
                    Cancel
                </a>

            </div>

        </form>

    </section>



<!-- Train Records Listing -->
<?php else: ?>

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


        <!-- Train Records Table -->
        <div class="table-wrap">

            <table id="dataTable">

                <tr>

                    <th>Train Code</th>
                    <th>Train Name</th>
                    <th>Type</th>
                    <th>Capacity</th>
                    <th>Status</th>
                    <th>Actions</th>

                </tr>


                <?php foreach ($rows as $row): ?>

                    <tr>


                        <!-- Train Information -->
                        <td>
                            <?= e($row['train_code'] ?? '') ?>
                        </td>

                        <td>
                            <?= e($row['train_name'] ?? '') ?>
                        </td>

                        <td>
                            <?= e($row['train_type'] ?? '') ?>
                        </td>

                        <td>
                            <?= e($row['capacity'] ?? '') ?>
                        </td>


                        <!-- Train Status -->
                        <td>
                            <?= e($row['status'] ?? '') ?>
                        </td>


                        <!-- Record Actions -->
                        <td>

                            <?php if (can_manage()): ?>

                                <!-- Edit Button -->
                                <a
                                    class="btn btn-light"
                                    href="?action=edit&id=<?= (int) $row['id'] ?>"
                                >
                                    Edit
                                </a>


                                <!-- Delete Button -->
                                <form
                                    method="post"
                                    action="?action=delete&id=<?= (int) $row['id'] ?>"
                                    style="display: inline"
                                    onsubmit="return confirm('Delete this record?')"
                                >

                                    <input
                                        type="hidden"
                                        name="mode"
                                        value="delete"
                                    >

                                    <button
                                        type="submit"
                                        class="btn btn-danger"
                                    >
                                        Delete
                                    </button>

                                </form>


                            <?php else: ?>

                                <span class="badge">
                                    View Only
                                </span>

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endforeach; ?>


                <!-- Empty Table Message -->
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

        let searchInput = document
            .getElementById('tableSearch');

        let query = searchInput.value.toLowerCase();

        let rows = document.querySelectorAll(
            '#dataTable tr:not(:first-child)'
        );


        rows.forEach(function(row) {

            let rowText = row.innerText.toLowerCase();

            row.style.display = rowText.includes(query)
                ? ''
                : 'none';

        });

    }

</script>



<?php

// Include page footer
require __DIR__ . '/../includes/footer.php';

?>

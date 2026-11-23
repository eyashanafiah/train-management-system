
<?php

require_once __DIR__ . '/../config/app.php';

require_login();


// Page configuration
$pageTitle = 'Crew Management';
$activePage = 'crews';


// Get current action and crew ID
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

        // Delete crew record
        if ($mode === 'delete') {

            $stmt = $pdo->prepare(
                "DELETE FROM crews WHERE id = ?"
            );

            $stmt->execute([$id]);

            log_event(
                $pdo,
                'Delete',
                'Deleted a crews record.'
            );

            header('Location: index.php');
            exit;
        }


        // Collect and clean form data
        $staffCode = trim((string)($_POST['staff_code'] ?? ''));
        $fullName = trim((string)($_POST['full_name'] ?? ''));
        $position = trim((string)($_POST['position'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? ''));
        $status = trim((string)($_POST['status'] ?? ''));

        $values = [
            $staffCode,
            $fullName,
            $position,
            $phone,
            $status
        ];


        // Update existing crew record
        if ($mode === 'update') {

            $values[] = $id;

            $stmt = $pdo->prepare(
                "UPDATE crews
                 SET staff_code = ?,
                     full_name = ?,
                     position = ?,
                     phone = ?,
                     status = ?
                 WHERE id = ?"
            );

            $stmt->execute($values);

            log_event(
                $pdo,
                'Update',
                'Updated a crews record.'
            );

        } else {

            // Create new crew record
            $stmt = $pdo->prepare(
                "INSERT INTO crews
                    (
                        staff_code,
                        full_name,
                        position,
                        phone,
                        status
                    )
                 VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->execute($values);

            log_event(
                $pdo,
                'Create',
                'Created a crews record.'
            );
        }


        // Redirect after successful operation
        header('Location: index.php');
        exit;

    } catch (Throwable $ex) {

        $error = 'Unable to save this record. Check that the values are valid and unique.';
    }
}


// Retrieve all crew records
$rows = $pdo->query(
    "SELECT *
     FROM crews
     ORDER BY full_name"
)->fetchAll();


// Retrieve crew details for editing
$record = null;

if ($action === 'edit' && $id) {

    $stmt = $pdo->prepare(
        "SELECT * FROM crews WHERE id = ?"
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

        <h2>Crew Management</h2>

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

    <!-- Create / Edit Crew Form -->
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

                <!-- Staff Code -->
                <div class="field">

                    <label>STAFF CODE</label>

                    <input
                        type="text"
                        name="staff_code"
                        value="<?= e($record['staff_code'] ?? '') ?>"
                        required
                    >

                </div>


                <!-- Full Name -->
                <div class="field">

                    <label>FULL NAME</label>

                    <input
                        type="text"
                        name="full_name"
                        value="<?= e($record['full_name'] ?? '') ?>"
                        required
                    >

                </div>


                <!-- Position -->
                <div class="field">

                    <label>POSITION</label>

                    <input
                        type="text"
                        name="position"
                        value="<?= e($record['position'] ?? '') ?>"
                        required
                    >

                </div>


                <!-- Phone Number -->
                <div class="field">

                    <label>PHONE NUMBER</label>

                    <input
                        type="text"
                        name="phone"
                        value="<?= e($record['phone'] ?? '') ?>"
                        required
                    >

                </div>


                <!-- Crew Availability -->
                <div class="field">

                    <label>AVAILABILITY</label>

                    <select name="status" required>

                        <option
                            value="Available"
                            <?= (($record['status'] ?? '') === 'Available') ? 'selected' : '' ?>
                        >
                            Available
                        </option>

                        <option
                            value="Assigned"
                            <?= (($record['status'] ?? '') === 'Assigned') ? 'selected' : '' ?>
                        >
                            Assigned
                        </option>

                        <option
                            value="Off Duty"
                            <?= (($record['status'] ?? '') === 'Off Duty') ? 'selected' : '' ?>
                        >
                            Off Duty
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

    <!-- Crew Records Table -->
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
                    <th>Staff code</th>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Phone</th>
                    <th>Availability</th>
                    <th>Actions</th>
                </tr>


                <!-- Display Crew Records -->
                <?php foreach ($rows as $row): ?>

                    <tr>

                        <!-- Staff Code -->
                        <td>
                            <?= e($row['staff_code'] ?? '') ?>
                        </td>


                        <!-- Full Name -->
                        <td>
                            <?= e($row['full_name'] ?? '') ?>
                        </td>


                        <!-- Position -->
                        <td>
                            <?= e($row['position'] ?? '') ?>
                        </td>


                        <!-- Phone Number -->
                        <td>
                            <?= e($row['phone'] ?? '') ?>
                        </td>


                        <!-- Crew Availability -->
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


<?php

require_once __DIR__ . '/../config/app.php';

require_login();


// Page configuration
$pageTitle = 'Maintenance and Issue Management';
$activePage = 'maintenance';


// Get current action and issue ID
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

        // Delete maintenance issue
        if ($mode === 'delete') {

            $stmt = $pdo->prepare(
                "DELETE FROM maintenance_issues WHERE id = ?"
            );

            $stmt->execute([$id]);

            log_event(
                $pdo,
                'Delete',
                'Deleted a maintenance_issues record.'
            );

            header('Location: index.php');
            exit;
        }


        // Collect and clean form data
        $issueCode = trim((string)($_POST['issue_code'] ?? ''));
        $issueTitle = trim((string)($_POST['issue_title'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $priority = trim((string)($_POST['priority'] ?? ''));
        $issueStatus = trim((string)($_POST['issue_status'] ?? ''));

        $values = [
            $issueCode,
            $issueTitle,
            $description,
            $priority,
            $issueStatus
        ];


        // Update existing maintenance issue
        if ($mode === 'update') {

            $values[] = $id;

            $stmt = $pdo->prepare(
                "UPDATE maintenance_issues
                 SET issue_code = ?,
                     issue_title = ?,
                     description = ?,
                     priority = ?,
                     issue_status = ?
                 WHERE id = ?"
            );

            $stmt->execute($values);

            log_event(
                $pdo,
                'Update',
                'Updated a maintenance_issues record.'
            );

        } else {

            // Create new maintenance issue
            $stmt = $pdo->prepare(
                "INSERT INTO maintenance_issues
                    (
                        issue_code,
                        issue_title,
                        description,
                        priority,
                        issue_status
                    )
                 VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->execute($values);

            log_event(
                $pdo,
                'Create',
                'Created a maintenance_issues record.'
            );
        }


        // Redirect after successful operation
        header('Location: index.php');
        exit;

    } catch (Throwable $ex) {

        $error = 'Unable to save this record. Check that the values are valid and unique.';
    }
}


// Retrieve all maintenance issues
$rows = $pdo->query(
    "SELECT *
     FROM maintenance_issues
     ORDER BY reported_at DESC"
)->fetchAll();


// Retrieve maintenance issue details for editing
$record = null;

if ($action === 'edit' && $id) {

    $stmt = $pdo->prepare(
        "SELECT * FROM maintenance_issues WHERE id = ?"
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

        <h2>Maintenance and Issue Management</h2>

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

    <!-- Create / Edit Maintenance Issue Form -->
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

                <!-- Issue Code -->
                <div class="field">

                    <label>ISSUE CODE</label>

                    <input
                        type="text"
                        name="issue_code"
                        value="<?= e($record['issue_code'] ?? '') ?>"
                        required
                    >

                </div>


                <!-- Issue Title -->
                <div class="field">

                    <label>ISSUE TITLE</label>

                    <input
                        type="text"
                        name="issue_title"
                        value="<?= e($record['issue_title'] ?? '') ?>"
                        required
                    >

                </div>


                <!-- Issue Description -->
                <div class="field">

                    <label>DESCRIPTION</label>

                    <input
                        type="text"
                        name="description"
                        value="<?= e($record['description'] ?? '') ?>"
                        required
                    >

                </div>


                <!-- Issue Priority -->
                <div class="field">

                    <label>PRIORITY</label>

                    <select name="priority" required>

                        <option
                            value="Low"
                            <?= (($record['priority'] ?? '') === 'Low') ? 'selected' : '' ?>
                        >
                            Low
                        </option>

                        <option
                            value="Medium"
                            <?= (($record['priority'] ?? '') === 'Medium') ? 'selected' : '' ?>
                        >
                            Medium
                        </option>

                        <option
                            value="High"
                            <?= (($record['priority'] ?? '') === 'High') ? 'selected' : '' ?>
                        >
                            High
                        </option>

                        <option
                            value="Critical"
                            <?= (($record['priority'] ?? '') === 'Critical') ? 'selected' : '' ?>
                        >
                            Critical
                        </option>

                    </select>

                </div>


                <!-- Issue Status -->
                <div class="field">

                    <label>ISSUE STATUS</label>

                    <select name="issue_status" required>

                        <option
                            value="Reported"
                            <?= (($record['issue_status'] ?? '') === 'Reported') ? 'selected' : '' ?>
                        >
                            Reported
                        </option>

                        <option
                            value="Assigned"
                            <?= (($record['issue_status'] ?? '') === 'Assigned') ? 'selected' : '' ?>
                        >
                            Assigned
                        </option>

                        <option
                            value="In Progress"
                            <?= (($record['issue_status'] ?? '') === 'In Progress') ? 'selected' : '' ?>
                        >
                            In Progress
                        </option>

                        <option
                            value="Resolved"
                            <?= (($record['issue_status'] ?? '') === 'Resolved') ? 'selected' : '' ?>
                        >
                            Resolved
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

    <!-- Maintenance Issues Table -->
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
                    <th>Issue</th>
                    <th>Title</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Reported</th>
                    <th>Actions</th>
                </tr>


                <!-- Display Maintenance Issues -->
                <?php foreach ($rows as $row): ?>

                    <tr>

                        <!-- Issue Code -->
                        <td>
                            <?= e($row['issue_code'] ?? '') ?>
                        </td>


                        <!-- Issue Title -->
                        <td>
                            <?= e($row['issue_title'] ?? '') ?>
                        </td>


                        <!-- Issue Priority -->
                        <td>
                            <?= e($row['priority'] ?? '') ?>
                        </td>


                        <!-- Issue Status -->
                        <td>
                            <?= e($row['issue_status'] ?? '') ?>
                        </td>


                        <!-- Reported Date -->
                        <td>
                            <?= e($row['reported_at'] ?? '') ?>
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

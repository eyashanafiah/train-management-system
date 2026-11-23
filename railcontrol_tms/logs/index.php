
<?php

require_once __DIR__ . '/../config/app.php';

require_login();


// Page configuration
$pageTitle = 'Dispatch Logs';
$activePage = 'logs';


// Retrieve the latest 200 operational activity logs
$rows = $pdo->query(
    "SELECT
        l.*,
        u.full_name
     FROM dispatch_logs l
     LEFT JOIN users u
        ON l.user_id = u.id
     ORDER BY l.created_at DESC
     LIMIT 200"
)->fetchAll();


// Include page header
require __DIR__ . '/../includes/header.php';

?>


<!-- Page Introduction -->
<div class="page-intro">

    <div>

        <h2>Operational Activity</h2>

        <p>
            Audit trail of sign-ins and system record changes.
        </p>

    </div>

</div>


<!-- Operational Activity Logs Table -->
<section class="card">

    <div class="table-wrap">

        <table>

            <!-- Table Header -->
            <tr>
                <th>Date / time</th>
                <th>User</th>
                <th>Event</th>
                <th>Description</th>
            </tr>


            <!-- Display Activity Logs -->
            <?php foreach ($rows as $row): ?>

                <tr>

                    <!-- Activity Date and Time -->
                    <td>
                        <?= e($row['created_at']) ?>
                    </td>


                    <!-- User Who Performed the Action -->
                    <td>
                        <?= e($row['full_name'] ?? 'System') ?>
                    </td>


                    <!-- Event Type -->
                    <td>

                        <span class="badge badge-blue">
                            <?= e($row['event_type']) ?>
                        </span>

                    </td>


                    <!-- Activity Description -->
                    <td>
                        <?= e($row['description']) ?>
                    </td>

                </tr>

            <?php endforeach; ?>


            <!-- Empty Activity Logs Message -->
            <?php if (!$rows): ?>

                <tr>

                    <td colspan="4" class="empty">
                        No activity recorded yet.
                    </td>

                </tr>

            <?php endif; ?>

        </table>

    </div>

</section>


<?php

// Include page footer
require __DIR__ . '/../includes/footer.php';

?>

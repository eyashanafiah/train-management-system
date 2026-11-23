
<?php

require_once __DIR__ . '/../config/app.php';

require_login();


// Page configuration
$pageTitle = 'Reports and Analytics';
$activePage = 'reports';


// Get total number of trains
$trainCount = (int) $pdo->query(
    "SELECT COUNT(*) FROM trains"
)->fetchColumn();


// Get total number of stations
$stationCount = (int) $pdo->query(
    "SELECT COUNT(*) FROM stations"
)->fetchColumn();


// Get total number of schedules
$scheduleCount = (int) $pdo->query(
    "SELECT COUNT(*) FROM schedules"
)->fetchColumn();


// Get maintenance issues grouped by priority
$issueRows = $pdo->query(
    "SELECT
        priority,
        COUNT(*) AS total
     FROM maintenance_issues
     GROUP BY priority"
)->fetchAll();


// Include page header
require __DIR__ . '/../includes/header.php';

?>


<!-- Page Introduction -->
<div class="page-intro">

    <div>

        <h2>Operations Summary</h2>

        <p>
            Current database totals for railway operations.
        </p>

    </div>


    <!-- Print Report Button -->
    <button
        class="btn btn-light"
        onclick="window.print()"
    >
        Print report
    </button>

</div>


<!-- Statistics Overview -->
<div class="stats">

    <!-- Total Trains -->
    <div class="stat">

        <div class="stat-label">
            Total trains
        </div>

        <div class="stat-value">
            <?= $trainCount ?>
        </div>

    </div>


    <!-- Total Stations -->
    <div class="stat">

        <div class="stat-label">
            Total stations
        </div>

        <div class="stat-value">
            <?= $stationCount ?>
        </div>

    </div>


    <!-- Total Schedules -->
    <div class="stat">

        <div class="stat-label">
            Schedules
        </div>

        <div class="stat-value">
            <?= $scheduleCount ?>
        </div>

    </div>

</div>


<!-- Maintenance Issues Report -->
<section class="card">

    <h3 class="section-title">
        Maintenance Issues by Priority
    </h3>


    <div class="table-wrap">

        <table>

            <!-- Table Header -->
            <tr>
                <th>Priority</th>
                <th>Number of issues</th>
            </tr>


            <!-- Display Maintenance Issue Records -->
            <?php foreach ($issueRows as $row): ?>

                <tr>

                    <!-- Issue Priority -->
                    <td>
                        <?= e($row['priority']) ?>
                    </td>


                    <!-- Total Issues -->
                    <td>
                        <?= e($row['total']) ?>
                    </td>

                </tr>

            <?php endforeach; ?>

        </table>

    </div>

</section>


<?php

// Include page footer
require __DIR__ . '/../includes/footer.php';

?>

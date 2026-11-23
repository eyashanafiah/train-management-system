<?php

require_once __DIR__ . '/config/app.php';

require_login();

$pageTitle = 'Operations Overview';
$activePage = 'dashboard';

// Dashboard statistics
$counts = [];

$tables = [
    'trains' => 'Trains in System',
    'stations' => 'Active Stations',
    'routes' => 'Active Routes',
    'schedules' => 'Upcoming Schedules'
];

// Retrieve total records for each section
foreach ($tables as $table => $label) {

    if ($table === 'schedules') {

        $sql = "
            SELECT COUNT(*)
            FROM schedules
            WHERE departure_time >= NOW()
            AND schedule_status NOT IN ('Cancelled', 'Arrived')
        ";

    } elseif ($table === 'stations') {

        $sql = "
            SELECT COUNT(*)
            FROM stations
            WHERE status = 'Active'
        ";

    } elseif ($table === 'routes') {

        $sql = "
            SELECT COUNT(*)
            FROM routes
            WHERE status = 'Active'
        ";

    } else {

        $sql = "SELECT COUNT(*) FROM trains";

    }

    $counts[$label] = (int) $pdo->query($sql)->fetchColumn();
}


// Count unresolved maintenance issues
$openIssues = (int) $pdo->query("
    SELECT COUNT(*)
    FROM maintenance_issues
    WHERE issue_status <> 'Resolved'
")->fetchColumn();


// Retrieve the five latest train schedules
$recent = $pdo->query("
    SELECT
        s.schedule_code,
        t.train_code,
        r.route_name,
        s.departure_time,
        s.schedule_status

    FROM schedules s

    JOIN trains t
        ON s.train_id = t.id

    JOIN routes r
        ON s.route_id = r.id

    ORDER BY s.departure_time DESC

    LIMIT 5
")->fetchAll();


// Include dashboard header
require __DIR__ . '/includes/header.php';

?>

<!-- Dashboard Introduction -->
<div class="page-intro">

    <div>
        <h2>
            Good day, <?= e(current_user()['full_name']) ?>
        </h2>

        <p>
            Here is the latest overview of your railway network.
        </p>
    </div>

    <span class="badge badge-green">
        ● Live Workspace
    </span>

</div>


<!-- Dashboard Statistics -->
<div class="stats">

    <?php foreach ($counts as $label => $value): ?>

        <div class="stat">

            <span class="stat-icon">↗</span>

            <div class="stat-label">
                <?= e($label) ?>
            </div>

            <div class="stat-value">
                <?= $value ?>
            </div>

            <div class="stat-note">
                Current database records
            </div>

        </div>

    <?php endforeach; ?>

</div>


<!-- Dashboard Main Content -->
<div class="dashboard-grid">


    <!-- Latest Train Schedules -->
    <section class="card">

        <h3 class="section-title">
            Latest Train Schedules
        </h3>

        <div class="table-wrap">

            <table>

                <tr>
                    <th>Schedule</th>
                    <th>Train</th>
                    <th>Route</th>
                    <th>Departure</th>
                    <th>Status</th>
                </tr>

                <?php foreach ($recent as $r): ?>

                    <tr>

                        <td>
                            <?= e($r['schedule_code']) ?>
                        </td>

                        <td>
                            <?= e($r['train_code']) ?>
                        </td>

                        <td>
                            <?= e($r['route_name']) ?>
                        </td>

                        <td>
                            <?= e(date(
                                'd M, H:i',
                                strtotime($r['departure_time'])
                            )) ?>
                        </td>

                        <td>
                            <span class="badge badge-blue">
                                <?= e($r['schedule_status']) ?>
                            </span>
                        </td>

                    </tr>

                <?php endforeach; ?>


                <?php if (!$recent): ?>

                    <tr>
                        <td colspan="5" class="empty">
                            No schedules found.
                        </td>
                    </tr>

                <?php endif; ?>

            </table>

        </div>

    </section>


    <!-- Operations Watch -->
    <section class="card">

        <h3 class="section-title">
            Operations Watch
        </h3>

        <div class="stat-label">
            Open Maintenance Issues
        </div>

        <div class="stat-value">
            <?= $openIssues ?>
        </div>

        <p class="stat-note">
            Issues that still require attention.
        </p>

        <a class="btn btn-light" href="maintenance/index.php">
            Review Maintenance →
        </a>


        <hr style="
            border: 0;
            border-top: 1px solid #edf1f5;
            margin: 20px 0;
        ">


        <!-- Quick Access -->
        <h3 class="section-title">
            Quick Access
        </h3>

        <div class="quick-grid">

            <a class="quick-link" href="trains/index.php">
                <span>🚆</span>
                Trains
            </a>

            <a class="quick-link" href="schedules/index.php">
                <span>▦</span>
                Schedules
            </a>

            <a class="quick-link" href="tracking/index.php">
                <span>⌖</span>
                Tracking
            </a>

            <a class="quick-link" href="signals/index.php">
                <span>◉</span>
                Signals
            </a>

        </div>

    </section>

</div>


<?php

// Include dashboard footer
require __DIR__ . '/includes/footer.php';

?>

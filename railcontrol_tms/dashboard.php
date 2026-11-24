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

// 4 Color themes matching index.php cards
$statThemes = ['stat-teal', 'stat-sky', 'stat-rose', 'stat-amber'];
$statIndex = 0;

// Helper for dynamic schedule status badge colors
function get_schedule_badge_class(string $status): string {
    $s = strtolower(trim($status));
    return match ($s) {
        'on time', 'active', 'arrived', 'completed' => 'badge-status-green',
        'delayed', 'pending', 'in transit'          => 'badge-status-amber',
        'cancelled', 'suspended', 'halted'          => 'badge-status-red',
        default                                     => 'badge-status-blue',
    };
}

// Include dashboard header
require __DIR__ . '/includes/header.php';

?>

<!-- Unified Welcome + Live Control Clock Hero Card -->
<section class="welcome-hero-card" aria-label="Operations Welcome and Master Clock">
    <div class="welcome-hero-left">
        <h2 class="welcome-hero-title">Welcome back,</h2>
        <div class="welcome-hero-user"><?= e(current_user()['full_name']) ?></div>
        <p class="welcome-hero-sub">
            Here is the latest overview of your railway network.
        </p>
    </div>

    <div class="welcome-hero-right">
        <div class="welcome-hero-top">
            <span class="hero-live-pill">
                <span class="hero-live-dot"></span> Live Workspace
            </span>
        </div>

        <div class="welcome-hero-clock">
            <div class="hero-clock-row">
                <span class="hero-clock-digits" id="rcLiveTime">--:--:--</span>
                <span class="hero-clock-tz">MYT</span>
            </div>
            <div class="hero-clock-date" id="rcLiveDate">Loading date...</div>
        </div>
    </div>
</section>


<!-- Dashboard Statistics -->
<div class="stats">

    <?php foreach ($counts as $label => $value):
        $themeClass = $statThemes[$statIndex % count($statThemes)];
        $statIndex++;
    ?>

        <div class="stat <?= $themeClass ?>">

            <span class="stat-icon">↗</span>

            <div class="stat-label">
                <?= e($label) ?>
            </div>

            <div class="stat-value">
                <?= $value ?>
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

                <?php foreach ($recent as $r):
                    $statusClass = get_schedule_badge_class($r['schedule_status'] ?? 'Scheduled');
                ?>

                    <tr>

                        <td>
                            <strong><?= e($r['schedule_code']) ?></strong>
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
                            <span class="badge <?= $statusClass ?>">
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

        <div class="watch-alert-box <?= $openIssues > 0 ? 'watch-alert-warn' : 'watch-alert-ok' ?>">
            <div>
                <div class="watch-num"><?= $openIssues ?></div>
                <div class="watch-subtext">
                    <?= $openIssues > 0 ? 'Issues requiring attention' : 'All track & fleet systems clear' ?>
                </div>
            </div>
            <span class="watch-tag">
                <?= $openIssues > 0 ? 'Action Needed' : 'Normal' ?>
            </span>
        </div>

        <a class="btn-review-theme" href="maintenance/index.php">
            Review Maintenance &rarr;
        </a>


        <hr class="watch-divider">


        <!-- Quick Access -->
        <h3 class="section-title">
            Quick Access
        </h3>

        <div class="quick-grid">

            <a class="quick-link" href="trains/index.php">
                <span class="qa-svg-badge qa-teal">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="4" y="3" width="16" height="15" rx="3"/>
                        <path d="M4 11h16"/>
                        <path d="M12 3v8"/>
                        <circle cx="8.5" cy="14.5" r="1"/>
                        <circle cx="15.5" cy="14.5" r="1"/>
                        <path d="M8 18l-2 3"/>
                        <path d="M16 18l2 3"/>
                    </svg>
                </span>
                <span>Trains</span>
            </a>

            <a class="quick-link" href="schedules/index.php">
                <span class="qa-svg-badge qa-sky">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                        <path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01"/>
                    </svg>
                </span>
                <span>Schedules</span>
            </a>

            <a class="quick-link" href="tracking/index.php">
                <span class="qa-svg-badge qa-green">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="8"/>
                        <line x1="12" y1="2" x2="12" y2="6"/>
                        <line x1="12" y1="18" x2="12" y2="22"/>
                        <line x1="2" y1="12" x2="6" y2="12"/>
                        <line x1="18" y1="12" x2="22" y2="12"/>
                        <circle cx="12" cy="12" r="2"/>
                    </svg>
                </span>
                <span>Tracking</span>
            </a>

            <a class="quick-link" href="signals/index.php">
                <span class="qa-svg-badge qa-amber">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="7" y="2" width="10" height="16" rx="5"/>
                        <circle cx="12" cy="7" r="1.5"/>
                        <circle cx="12" cy="13" r="1.5"/>
                        <line x1="12" y1="18" x2="12" y2="22"/>
                        <line x1="8" y1="22" x2="16" y2="22"/>
                    </svg>
                </span>
                <span>Signals</span>
            </a>

        </div>

    </section>

</div>

<script>
(function () {
    const timeEl = document.getElementById('rcLiveTime');
    const dateEl = document.getElementById('rcLiveDate');
    if (!timeEl || !dateEl) return;

    function updateControlClock() {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('en-GB', {
            timeZone: 'Asia/Kuala_Lumpur',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        });
        const dateStr = now.toLocaleDateString('en-GB', {
            timeZone: 'Asia/Kuala_Lumpur',
            weekday: 'long',
            day: '2-digit',
            month: 'long',
            year: 'numeric'
        });
        timeEl.textContent = timeStr;
        dateEl.textContent = dateStr;
    }

    updateControlClock();
    setInterval(updateControlClock, 1000);
})();
</script>

<?php

// Include dashboard footer
require __DIR__ . '/includes/footer.php';

?>
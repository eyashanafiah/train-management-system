
<?php

require_once __DIR__ . '/../config/app.php';

require_login();


// Page configuration
$pageTitle = 'Train Tracking';
$activePage = 'tracking';


// Retrieve latest train position records
$rows = $pdo->query("
    SELECT
        p.*,
        t.train_code,
        t.train_name,
        s.station_name

    FROM train_positions p

    JOIN trains t
        ON p.train_id = t.id

    LEFT JOIN stations s
        ON p.station_id = s.id

    ORDER BY p.recorded_at DESC
")->fetchAll();


// Include page header
require __DIR__ . '/../includes/header.php';

?>


<!-- Page Introduction -->
<div class="page-intro">

    <div>

        <h2>
            Network Tracking
        </h2>

        <p>
            Latest recorded train positions and movement status.
        </p>

    </div>

    <span class="badge badge-blue">
        Database Position Records
    </span>

</div>



<!-- Railway Network Map -->
<div class="card">

    <div class="map-placeholder">

        <div>

            <div style="font-size: 32px;">
                ⌖
            </div>

            <b>
                Railway Network Map
            </b>

            <p>
                Map integration is prepared for a later Leaflet.js milestone.
                <br>
                Current version displays recorded train positions below.
            </p>

        </div>

    </div>

</div>



<!-- Latest Train Positions -->
<section class="card">

    <h3 class="section-title">
        Latest Train Positions
    </h3>


    <div class="table-wrap">

        <table>

            <tr>
                <th>Train</th>
                <th>Last Station</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Movement</th>
                <th>Recorded</th>
            </tr>


            <?php foreach ($rows as $r): ?>

                <tr>

                    <!-- Train Information -->
                    <td>
                        <?= e($r['train_code'] . ' · ' . $r['train_name']) ?>
                    </td>


                    <!-- Station Information -->
                    <td>
                        <?= e($r['station_name'] ?? 'Not specified') ?>
                    </td>


                    <!-- Train Coordinates -->
                    <td>
                        <?= e($r['latitude'] ?? '—') ?>
                    </td>

                    <td>
                        <?= e($r['longitude'] ?? '—') ?>
                    </td>


                    <!-- Movement Status -->
                    <td>

                        <span class="badge badge-blue">
                            <?= e($r['movement_status']) ?>
                        </span>

                    </td>


                    <!-- Record Timestamp -->
                    <td>
                        <?= e($r['recorded_at']) ?>
                    </td>

                </tr>

            <?php endforeach; ?>


            <!-- Empty Table Message -->
            <?php if (!$rows): ?>

                <tr>

                    <td colspan="6" class="empty">
                        No train position records found.
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


<?php

require_once __DIR__ . '/../config/app.php';

require_login();


// Page configuration
$pageTitle = 'Signal Control';
$activePage = 'signals';


// Handle signal aspect updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && can_manage()) {

    // Get signal ID and selected aspect
    $id = (int)($_POST['id'] ?? 0);
    $aspect = $_POST['aspect'] ?? 'Red';


    // Validate selected signal aspect
    if (in_array($aspect, ['Red', 'Yellow', 'Green'], true)) {

        // Update signal aspect in database
        $stmt = $pdo->prepare(
            "UPDATE signals
             SET signal_aspect = ?
             WHERE id = ?"
        );

        $stmt->execute([$aspect, $id]);


        // Record activity log
        log_event(
            $pdo,
            'Signal update',
            'Signal ' . $id . ' changed to ' . $aspect . '.'
        );
    }


    // Redirect after updating
    header('Location: index.php');
    exit;
}


// Retrieve all signals and their associated routes
$rows = $pdo->query(
    "SELECT
        s.*,
        r.route_name
     FROM signals s
     LEFT JOIN routes r
        ON s.route_id = r.id
     ORDER BY s.signal_code"
)->fetchAll();


// Include page header
require __DIR__ . '/../includes/header.php';

?>


<!-- Page Introduction -->
<div class="page-intro">

    <div>
        <h2>Signal Status Board</h2>

        <p>
            View and simulate signal aspects for academic demonstration.
        </p>
    </div>

    <span class="badge badge-amber">
        Simulation only · not connected to railway hardware
    </span>

</div>


<!-- Signal Status Table -->
<section class="card">

    <div class="table-wrap">

        <table>

            <!-- Table Header -->
            <tr>
                <th>Signal</th>
                <th>Location</th>
                <th>Route</th>
                <th>Current aspect</th>
                <th>Simulation</th>
            </tr>


            <!-- Display Signal Records -->
            <?php foreach ($rows as $row): ?>

                <tr>

                    <!-- Signal Code -->
                    <td>
                        <b>
                            <?= e($row['signal_code']) ?>
                        </b>
                    </td>


                    <!-- Signal Location -->
                    <td>
                        <?= e($row['location_description']) ?>
                    </td>


                    <!-- Associated Route -->
                    <td>
                        <?= e($row['route_name'] ?? '—') ?>
                    </td>


                    <!-- Current Signal Aspect -->
                    <td>

                        <span
                            class="signal-light signal-<?= strtolower($row['signal_aspect']) ?>"
                        ></span>

                        <?= e($row['signal_aspect']) ?>

                    </td>


                    <!-- Signal Simulation Controls -->
                    <td>

                        <?php if (can_manage()): ?>

                            <form
                                method="post"
                                style="display:flex; gap:6px"
                            >

                                <!-- Signal ID -->
                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int)$row['id'] ?>"
                                >


                                <!-- Select Signal Aspect -->
                                <select name="aspect">

                                    <option>Red</option>
                                    <option>Yellow</option>
                                    <option>Green</option>

                                </select>


                                <!-- Update Button -->
                                <button class="btn btn-light">
                                    Update
                                </button>

                            </form>

                        <?php else: ?>

                            View only

                        <?php endif; ?>

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

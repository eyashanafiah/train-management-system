
<?php

require_once __DIR__ . '/config/app.php';


// Record logout activity if user is logged in
if (!empty($_SESSION['user'])) {

    log_event(
        $pdo,
        'Logout',
        'User ' . ($_SESSION['user']['username'] ?? '') . ' signed out.'
    );

}


// Clear all session data
$_SESSION = [];


// Destroy the current session
session_destroy();


// Redirect user to login page
header('Location: login.php');
exit;

?>

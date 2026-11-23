
<?php

require_once __DIR__ . '/config/app.php';


// Check whether the user is logged in
if (!empty($_SESSION['user'])) {

    // Redirect logged-in users to dashboard
    header('Location: dashboard.php');

} else {

    // Redirect guests to login page
    header('Location: login.php');

}


// Stop script execution
exit;

?>


<?php

require_once __DIR__ . '/config/app.php';


// Redirect users who are already logged in
if (!empty($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}


// Initialize error message
$error = '';


// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';


    // Find active user account
    $stmt = $pdo->prepare("
        SELECT *
        FROM users
        WHERE username = ?
        AND status = 'Active'
        LIMIT 1
    ");

    $stmt->execute([$username]);

    $user = $stmt->fetch();


    // Verify username and password
    if ($user && password_verify($password, $user['password_hash'])) {

        // Regenerate session ID for security
        session_regenerate_id(true);


        // Store user information in session
        $_SESSION['user'] = [
            'id'        => $user['id'],
            'full_name' => $user['full_name'],
            'username'  => $user['username'],
            'role'      => $user['role']
        ];


        // Record login activity
        log_event(
            $pdo,
            'Login',
            'User ' . $username . ' signed in.'
        );


        // Redirect to dashboard
        header('Location: dashboard.php');
        exit;

    } else {

        // Display login error
        $error = 'Username or password is incorrect.';

    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Sign In | RAILCONTROL</title>

    <link rel="stylesheet" href="assets/css/style.css">

</head>


<body>

    <div class="login-page">


        <!-- Login Branding Section -->
        <section class="login-art">

            <div class="brand">

                <span class="brand-mark">
                    R
                </span>

                <span>
                    RAIL<span class="brand-light">CONTROL</span>

                    <small>
                        OPERATIONS PLATFORM
                    </small>
                </span>

            </div>


            <h1>
                One network.<br>
                One control centre.
            </h1>

            <p>
                Coordinate railway operations, manage train schedules
                and keep every journey moving with confidence.
            </p>

        </section>



        <!-- Login Form Section -->
        <section class="login-panel">

            <form class="login-box" method="post">

                <span class="eyebrow">
                    RAILWAY OPERATIONS PLATFORM
                </span>


                <h2>
                    Welcome back
                </h2>

                <p>
                    Sign in to access the RAILCONTROL workspace.
                </p>


                <!-- Login Error Message -->
                <?php if ($error): ?>

                    <div class="alert">
                        <?= e($error) ?>
                    </div>

                <?php endif; ?>



                <!-- Username Field -->
                <div class="field">

                    <label>USERNAME</label>

                    <input
                        type="text"
                        name="username"
                        required
                        autocomplete="username"
                        placeholder="Enter your username"
                    >

                </div>



                <!-- Password Field -->
                <div class="field">

                    <label>PASSWORD</label>

                    <input
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="Enter your password"
                    >

                </div>



                <!-- Sign In Button -->
                <button
                    type="submit"
                    class="btn btn-primary"
                    style="width: 100%; justify-content: center; margin-top: 8px;"
                >
                    Sign in to workspace →
                </button>



                
                

            </form>

        </section>

    </div>

</body>

</html>

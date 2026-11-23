
<?php

require_once __DIR__ . '/../config/app.php';

require_login();


// Check administrator access
if (current_user()['role'] !== 'Administrator') {

    http_response_code(403);

    exit('Administrator access required.');

}


// Page configuration
$pageTitle = 'User Management';
$activePage = 'users';


// Initialize error message
$error = '';


// Handle user registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collect form data
    $name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'Inspector';
    $password = $_POST['password'] ?? '';


    // Validate password requirements
    $validPassword =
        strlen($password) >= 8 &&
        preg_match('/[A-Z]/', $password) &&
        preg_match('/[a-z]/', $password) &&
        preg_match('/[0-9]/', $password) &&
        preg_match('/[^A-Za-z0-9]/', $password);


    // Validate user information
    if (
        $name &&
        $username &&
        $validPassword &&
        in_array(
            $role,
            ['Administrator', 'Dispatcher', 'Engineer', 'Inspector'],
            true
        )
    ) {

        // Insert new user into database
        $st = $pdo->prepare("
            INSERT INTO users (
                full_name,
                username,
                email,
                password_hash,
                role
            )
            VALUES (?, ?, ?, ?, ?)
        ");

        $st->execute([
            $name,
            $username,
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            $role
        ]);


        // Record user creation activity
        log_event(
            $pdo,
            'User create',
            'Created user ' . $username
        );


        // Redirect after successful registration
        header('Location: index.php');
        exit;

    } else {

        // Display validation error
        if (!$validPassword) {

            $error = 'Password must contain at least 8 characters, one uppercase letter, one lowercase letter, one number and one special character.';

        } else {

            $error = 'Please complete all required fields with valid information.';

        }

    }

}


// Retrieve all registered users
$rows = $pdo->query("
    SELECT
        id,
        full_name,
        username,
        email,
        role,
        status,
        created_at

    FROM users

    ORDER BY id
")->fetchAll();


// Include page header
require __DIR__ . '/../includes/header.php';

?>


<!-- Page Introduction -->
<div class="page-intro">

    <div>

        <h2>
            System Users
        </h2>

        <p>
            Create staff accounts and assign access roles.
        </p>

    </div>

</div>



<!-- Add New User Section -->
<section class="card">

    <h3 class="section-title">
        Add User
    </h3>


    <!-- Display Error Message -->
    <?php if ($error): ?>

        <div class="alert">
            <?= e($error) ?>
        </div>

    <?php endif; ?>


    <form method="post" class="form-grid">


        <!-- Full Name -->
        <div class="field">

            <label>FULL NAME</label>

            <input
                type="text"
                name="full_name"
                required
            >

        </div>


        <!-- Username -->
        <div class="field">

            <label>USERNAME</label>

            <input
                type="text"
                name="username"
                required
            >

        </div>


        <!-- Email Address -->
        <div class="field">

            <label>EMAIL</label>

            <input
                type="email"
                name="email"
            >

        </div>


        <!-- Password -->
        <div class="field">

            <label>INITIAL PASSWORD</label>

            <input
                type="password"
                name="password"
                minlength="8"
                required
                placeholder="Enter a strong password"
                autocomplete="new-password"
            >

         

        </div>


        <!-- User Role -->
        <div class="field">

            <label>ROLE</label>

            <select name="role">

                <option value="Inspector">
                    Inspector
                </option>

                <option value="Dispatcher">
                    Dispatcher
                </option>

                <option value="Engineer">
                    Engineer
                </option>

                <option value="Administrator">
                    Administrator
                </option>

            </select>

        </div>


        <!-- Create Account Button -->
        <div class="field" style="align-self: end">

            <button
                type="submit"
                class="btn btn-primary"
            >
                Create Account
            </button>

        </div>


    </form>

</section>



<!-- Registered Users Section -->
<section class="card">

    <h3 class="section-title">
        Registered Users
    </h3>


    <div class="table-wrap">

        <table>

            <tr>

                <th>Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>

            </tr>


            <?php foreach ($rows as $r): ?>

                <tr>

                    <td>
                        <?= e($r['full_name']) ?>
                    </td>

                    <td>
                        <?= e($r['username']) ?>
                    </td>

                    <td>
                        <?= e($r['email']) ?>
                    </td>

                    <td>
                        <?= e($r['role']) ?>
                    </td>

                    <td>
                        <?= e($r['status']) ?>
                    </td>

                </tr>

            <?php endforeach; ?>


            <!-- Empty Table Message -->
            <?php if (!$rows): ?>

                <tr>

                    <td colspan="5" class="empty">
                        No users registered yet.
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

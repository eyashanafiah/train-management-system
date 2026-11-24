<?php
require_once __DIR__ . '/../config/app.php';
require_login();

$me = current_user();
$userId = (int)$me['id'];

$pageTitle = 'My Profile';
$activePage = 'profile';

$error = '';
$success = '';

// Proses tukar kata laluan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPass = $_POST['current_password'] ?? '';
    $newPass     = $_POST['new_password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $row = $stmt->fetch();

    // Syarat password selaras dengan sistem kumpulan
    $validPassword =
        strlen($newPass) >= 8 &&
        preg_match('/[A-Z]/', $newPass) &&
        preg_match('/[a-z]/', $newPass) &&
        preg_match('/[0-9]/', $newPass) &&
        preg_match('/[^A-Za-z0-9]/', $newPass);

    if (!$row || !password_verify($currentPass, $row['password_hash'])) {
        $error = 'Current password is incorrect.';
    } elseif (!$validPassword) {
        $error = 'Password must contain at least 8 characters, one uppercase letter, one lowercase letter, one number and one special character.';
    } elseif ($newPass !== $confirmPass) {
        $error = 'New password and confirmation do not match.';
    } else {
        $upd = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $upd->execute([password_hash($newPass, PASSWORD_DEFAULT), $userId]);

        log_event($pdo, 'Password change', 'User ' . $me['username'] . ' updated password');
        $success = 'Password updated successfully.';
    }
}

// Ambil maklumat terkini pengguna dari database
$stmt = $pdo->prepare("
    SELECT id, full_name, username, email, role, status, created_at
    FROM users
    WHERE id = ?
    LIMIT 1
");
$stmt->execute([$userId]);
$user = $stmt->fetch();

require __DIR__ . '/../includes/header.php';
?>

<!-- Page Introduction -->
<div class="page-intro">
    <div>
        <h2>My Profile</h2>
        <p>View your account details, access role, and update your password.</p>
    </div>
</div>

<!-- Maklumat Akaun -->
<section class="card">
    <h3 class="section-title">Account Information</h3>

    <div class="table-wrap">
        <table>
            <tr>
                <th style="width: 220px;">Full Name</th>
                <td><?= e($user['full_name']) ?></td>
            </tr>
            <tr>
                <th>Username</th>
                <td><?= e($user['username']) ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?= e($user['email'] ?: '—') ?></td>
            </tr>
            <tr>
                <th>Role</th>
                <td><strong><?= e($user['role']) ?></strong></td>
            </tr>
            <tr>
                <th>Status</th>
                <td><?= e($user['status']) ?></td>
            </tr>
            <tr>
                <th>Member Since</th>
                <td><?= e($user['created_at']) ?></td>
            </tr>
        </table>
    </div>
</section>

<!-- Tukar Kata Laluan -->
<section class="card">
    <h3 class="section-title">Change Password</h3>

    <?php if ($error): ?>
        <div class="alert"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert" style="background: #e6f4ea; color: #137333; border-color: #ceead6;">
            <?= e($success) ?>
        </div>
    <?php endif; ?>

    <form method="post" class="form-grid">
        <div class="field">
            <label>CURRENT PASSWORD</label>
            <input
                type="password"
                name="current_password"
                required
                autocomplete="current-password"
            >
        </div>

        <div class="field">
            <label>NEW PASSWORD</label>
            <input
                type="password"
                name="new_password"
                minlength="8"
                required
                placeholder="8+ chars, upper, lower, number & symbol"
                autocomplete="new-password"
            >
        </div>

        <div class="field">
            <label>CONFIRM NEW PASSWORD</label>
            <input
                type="password"
                name="confirm_password"
                minlength="8"
                required
                autocomplete="new-password"
            >
        </div>

        <div class="field" style="align-self: end;">
            <button type="submit" class="btn btn-primary">
                Update Password
            </button>
        </div>
    </form>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
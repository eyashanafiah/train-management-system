
<?php

// Start PHP session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set Malaysia timezone
date_default_timezone_set('Asia/Kuala_Lumpur');

// Connect to database
require_once __DIR__ . '/database.php';


// Escape HTML characters to prevent XSS
function e($value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}


// Check if user is logged in
function require_login(): void
{
    if (empty($_SESSION['user'])) {
        header('Location: /railcontrol_tms/login.php');
        exit;
    }
}


// Get current logged-in user
function current_user(): array
{
    return $_SESSION['user'] ?? [];
}


// Check permission to manage records
function can_manage(): bool
{
    return in_array(
        current_user()['role'] ?? '',
        [
            'Administrator',
            'Dispatcher',
            'Engineer'
        ],
        true
    );
}


// Record system activity
function log_event(
    PDO $pdo,
    string $type,
    string $description
): void
{
    $stmt = $pdo->prepare(
        "INSERT INTO dispatch_logs
            (user_id, event_type, description)
         VALUES (?, ?, ?)"
    );

    $stmt->execute([
        current_user()['id'] ?? null,
        $type,
        $description
    ]);
}

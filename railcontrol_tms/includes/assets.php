<?php
declare(strict_types=1);

/**
 * Helper functions for Infrastructure Assets module (CSRF, flash messages, safe string input).
 * Uses the unified RAILCONTROL session and database connection.
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/constants.php';

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }
}

if (!function_exists('csrf_verify')) {
    function csrf_verify($token): bool
    {
        return is_string($token)
            && !empty($_SESSION['csrf'])
            && hash_equals($_SESSION['csrf'], $token);
    }
}

if (!function_exists('flash_set')) {
    function flash_set(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }
}

if (!function_exists('flash_get')) {
    function flash_get(): ?array
    {
        $f = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $f;
    }
}

if (!function_exists('str_in')) {
    function str_in(array $source, string $key): string
    {
        return (isset($source[$key]) && is_string($source[$key])) ? trim($source[$key]) : '';
    }
}

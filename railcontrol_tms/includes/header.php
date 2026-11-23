
<?php

require_once __DIR__ . '/../config/app.php';


// Page configuration
$pageTitle = $pageTitle ?? 'Operations overview';
$activePage = $activePage ?? '';


// Determine the correct path prefix
// for pages located inside subfolders
$rootPrefix = (
    strpos($_SERVER['SCRIPT_NAME'], '/trains/') !== false ||
    strpos($_SERVER['SCRIPT_NAME'], '/stations/') !== false ||
    strpos($_SERVER['SCRIPT_NAME'], '/routes/') !== false ||
    strpos($_SERVER['SCRIPT_NAME'], '/schedules/') !== false ||
    strpos($_SERVER['SCRIPT_NAME'], '/tracking/') !== false ||
    strpos($_SERVER['SCRIPT_NAME'], '/signals/') !== false ||
    strpos($_SERVER['SCRIPT_NAME'], '/crews/') !== false ||
    strpos($_SERVER['SCRIPT_NAME'], '/maintenance/') !== false ||
    strpos($_SERVER['SCRIPT_NAME'], '/logs/') !== false ||
    strpos($_SERVER['SCRIPT_NAME'], '/reports/') !== false ||
    strpos($_SERVER['SCRIPT_NAME'], '/users/') !== false
) ? '../' : '';

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        <?= e($pageTitle) ?> | RAILCONTROL
    </title>

    <!-- Main Stylesheet -->
    <link
        rel="stylesheet"
        href="<?= $rootPrefix ?>assets/css/style.css"
    >

</head>

<body>

<div class="app-shell">

    <!-- Sidebar Navigation -->
    <aside class="sidebar" id="sidebar">

        <!-- Website Branding -->
        <a
            class="brand"
            href="<?= $rootPrefix ?>dashboard.php"
        >

            <span class="brand-mark">
                R
            </span>

            <span>
                RAIL<span class="brand-light">CONTROL</span>

                <small>
                    OPERATIONS PLATFORM
                </small>
            </span>

        </a>


        <!-- Workspace Navigation -->
        <div class="nav-label">
            WORKSPACE
        </div>


        <!-- Dashboard -->
        <a
            class="nav-link <?= $activePage === 'dashboard' ? 'active' : '' ?>"
            href="<?= $rootPrefix ?>dashboard.php"
        >
            ▦ <span>Dashboard</span>
        </a>


        <!-- Railway Operations Navigation -->
        <div class="nav-label">
            RAILWAY OPERATIONS
        </div>


        <!-- Train Management -->
        <a
            class="nav-link <?= $activePage === 'trains' ? 'active' : '' ?>"
            href="<?= $rootPrefix ?>trains/index.php"
        >
            🚆 <span>Train management</span>
        </a>


        <!-- Station Management -->
        <a
            class="nav-link <?= $activePage === 'stations' ? 'active' : '' ?>"
            href="<?= $rootPrefix ?>stations/index.php"
        >
            ▤ <span>Stations</span>
        </a>


        <!-- Route Management -->
        <a
            class="nav-link <?= $activePage === 'routes' ? 'active' : '' ?>"
            href="<?= $rootPrefix ?>routes/index.php"
        >
            ⌁ <span>Routes</span>
        </a>


        <!-- Schedule Management -->
        <a
            class="nav-link <?= $activePage === 'schedules' ? 'active' : '' ?>"
            href="<?= $rootPrefix ?>schedules/index.php"
        >
            ▦ <span>Schedules</span>
        </a>


        <!-- Train Tracking -->
        <a
            class="nav-link <?= $activePage === 'tracking' ? 'active' : '' ?>"
            href="<?= $rootPrefix ?>tracking/index.php"
        >
            ⌖ <span>Train tracking</span>
        </a>


        <!-- Signal Control -->
        <a
            class="nav-link <?= $activePage === 'signals' ? 'active' : '' ?>"
            href="<?= $rootPrefix ?>signals/index.php"
        >
            ◉ <span>Signal control</span>
        </a>


        <!-- Resources and Control Navigation -->
        <div class="nav-label">
            RESOURCES & CONTROL
        </div>


        <!-- Crew Management -->
        <a
            class="nav-link <?= $activePage === 'crews' ? 'active' : '' ?>"
            href="<?= $rootPrefix ?>crews/index.php"
        >
            ♙ <span>Crew management</span>
        </a>


        <!-- Maintenance Management -->
        <a
            class="nav-link <?= $activePage === 'maintenance' ? 'active' : '' ?>"
            href="<?= $rootPrefix ?>maintenance/index.php"
        >
            ⚒ <span>Maintenance</span>
        </a>


        <!-- Dispatch Logs -->
        <a
            class="nav-link <?= $activePage === 'logs' ? 'active' : '' ?>"
            href="<?= $rootPrefix ?>logs/index.php"
        >
            ≡ <span>Dispatch logs</span>
        </a>


        <!-- Reports and Analytics -->
        <a
            class="nav-link <?= $activePage === 'reports' ? 'active' : '' ?>"
            href="<?= $rootPrefix ?>reports/index.php"
        >
            ▥ <span>Reports</span>
        </a>


        <!-- Administrator Only: User Management -->
        <?php if ((current_user()['role'] ?? '') === 'Administrator'): ?>

            <a
                class="nav-link <?= $activePage === 'users' ? 'active' : '' ?>"
                href="<?= $rootPrefix ?>users/index.php"
            >
                ♙ <span>User management</span>
            </a>

        <?php endif; ?>


        <!-- Sidebar Bottom Section -->
        <div class="sidebar-bottom">

            <!-- Current User Information -->
            <div class="user-chip">

                <!-- User Avatar -->
                <div class="avatar">
                    <?= e(strtoupper(substr(current_user()['full_name'] ?? 'U', 0, 1))) ?>
                </div>


                <!-- User Name and Role -->
                <div>

                    <b>
                        <?= e(current_user()['full_name'] ?? 'User') ?>
                    </b>

                    <small>
                        <?= e(current_user()['role'] ?? '') ?>
                    </small>

                </div>

            </div>


            <!-- Logout Link -->
            <a
                class="logout-link"
                href="<?= $rootPrefix ?>logout.php"
            >
                Sign out ↗
            </a>

        </div>

    </aside>


    <!-- Main Content Area -->
    <main class="main-area">

        <!-- Top Navigation Bar -->
        <header class="topbar">

            <!-- Mobile Sidebar Toggle -->
            <button
                class="menu-toggle"
                onclick="document.getElementById('sidebar').classList.toggle('open')"
            >
                ☰
            </button>


            <!-- Page Title -->
            <div>

                <span class="eyebrow">
                    RAILWAY CONTROL CENTRE
                </span>

                <h1>
                    <?= e($pageTitle) ?>
                </h1>

            </div>


            <!-- System Status and Current User -->
            <div class="topbar-right">

                <!-- System Status -->
                <span class="system-live">
                    <i></i> SYSTEM ONLINE
                </span>


                <!-- Logged-in User Name -->
                <span class="top-user">
                    <?= e(current_user()['full_name'] ?? '') ?>
                </span>

            </div>

        </header>


        <!-- Page Content Container -->
        <div class="content">

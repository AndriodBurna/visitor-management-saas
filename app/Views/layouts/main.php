<?php
$savedTheme = $_COOKIE['vms_theme'] ?? 'light';

if (!in_array($savedTheme, ['light', 'dark'], true)) {
    $savedTheme = 'light';
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?= esc($savedTheme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= isset($title) ? esc($title) . ' · ' : '' ?>
        <?= esc($appName ?? 'Visitor Management') ?>
    </title>

    <!-- Prevent incorrect browser first-paint background -->
    <style>
        html,
        body {
            margin: 0;
            min-height: 100%;
        }

        html[data-theme="light"],
        html[data-theme="light"] body {
            background: #F8FAFC;
            color: #0F172A;
        }

        html[data-theme="dark"],
        html[data-theme="dark"] body {
            background: #0B1220;
            color: #F1F5F9;
        }
    </style>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/tokens.css') ?>"
    >

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/app.css') ?>"
    >

    <?= $this->renderSection('styles') ?>
</head>

<body>

<div class="app-shell">

    <div
        class="sidebar-overlay"
        data-sidebar-overlay
    ></div>

    <?= $this->include('partials/sidebar') ?>

    <div class="app-main">

        <?= $this->include('partials/topbar') ?>

        <main class="app-content">

            <?= $this->include('partials/flash_messages') ?>

            <?= $this->renderSection('content') ?>

        </main>

    </div>

</div>

<script src="<?= base_url('assets/js/app.js') ?>"></script>

<?= $this->renderSection('scripts') ?>

</body>
</html>
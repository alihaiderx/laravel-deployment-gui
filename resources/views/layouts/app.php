<?php

use App\Support\Config;
use App\Actions\CheckServerRequirements;
use App\Actions\CheckPermissions;
use App\Actions\CheckSourceFiles;

$sidebarColor = Config::get('app.sidebarColor', '#18181b');
$accentColor = Config::get('app.accentColor', '#6366f1');
$appName = Config::get('app.name', 'Laravel Installer');
$appVersion = Config::get('app.version', '');
$baseUrl = Config::get('app.url', '');
$serverRequirements = (new CheckServerRequirements())->check();
$permissions = (new CheckPermissions())->check();
$sourceFiles = (new CheckSourceFiles())->check();
$licenseUrl = Config::get('installation.licenseUrl', '');

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($appName) ?></title>
    <style>:root { --sidebar: <?= htmlspecialchars($sidebarColor) ?>; --accent: <?= htmlspecialchars($accentColor) ?>; }</style>
    <link rel="stylesheet" href="<?= $baseUrl ?>/resources/css/app.css">
</head>
<body>
    <div id="app">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <span class="brand-name"><?= htmlspecialchars($appName) ?></span>
                <span class="brand-version"><?= htmlspecialchars($appVersion) ?></span>
            </div>
            <nav id="steps-nav" class="steps-nav"></nav>
        </aside>
        <main class="main">
            <div id="step-content" class="step-content"></div>
        </main>
    </div>
    <script>
        window.__baseUrl = <?= json_encode($baseUrl) ?>;
        window.__serverRequirements = <?= json_encode($serverRequirements) ?>;
        window.__permissions = <?= json_encode($permissions) ?>;
        window.__sourceFiles = <?= json_encode($sourceFiles) ?>;
        window.__licenseUrl = <?= json_encode($licenseUrl) ?>;
        window.__symlinks = <?= json_encode(Config::get('installation.symlinks', [])) ?>;
        window.__symlinkBase = <?= json_encode(dirname(Config::basePath())) ?>;
    </script>
    <script src="<?= $baseUrl ?>/resources/js/app.js"></script>
</body>
</html>

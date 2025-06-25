<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once CONFIG_PATH . '/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
?>

<body>
    <!-- Loader -->
    <div id="loader">
        <img src="<?= ASSETS_URL ?>/images/media/media-79.svg" alt="Memuat...">
    </div>
    <div class="page">
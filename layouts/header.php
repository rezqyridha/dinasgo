<?php
if (!isset($_SESSION)) session_start();
require_once __DIR__ . '/../config/koneksi.php';
?>

<body>
    <!-- Loader -->
    <div id="loader">
        <img src="<?= BASE_URL ?>/assets/images/media/media-79.svg" alt="">
    </div>
    <div class="page">
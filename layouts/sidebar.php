<?php

$role = $_SESSION['role'] ?? 'guest';
$menuFile = __DIR__ . "/menu_$role.php";
?>
<!-- Sidebar ATAS -->
<aside class="app-sidebar sticky" id="sidebar">
    <!-- Start::main-sidebar-header -->
    <div class="main-sidebar-header">
        <a href="<?= BASE_URL ?>/modules/<?= $_SESSION['role'] ?>/dashboard.php" class="header-logo">
            <img src="<?= BASE_URL ?>/assets/images/brand-logos/PUPR.png" alt="logo sidebar" style="height: 40px; ">
        </a>
    </div>
    <!-- End::main-sidebar-header -->


    <!-- Sidebar Bawah -->
    <div class="main-sidebar" id="sidebar-scroll">
        <?php
        if (file_exists($menuFile)) {
            require_once $menuFile;
        } else {
            echo "<div class='text-danger p-3'>Menu untuk role <strong>$role</strong> tidak ditemukan.</div>";
        }
        ?>
    </div>
    <!-- End::main-sidebar -->
</aside>
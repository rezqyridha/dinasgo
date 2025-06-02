<?php
require_once __DIR__ . '/../auth/session.php'; // Pastikan sesi aktif

$role = $_SESSION['role'] ?? 'guest';
$menuFile = __DIR__ . "/menu_$role.php";
?>
<header class="app-header">
    <div class="main-header-container container-fluid">
        <div class="header-content-left">
            <div class="header-element">
                <div class="horizontal-logo">
                    <a href="<?= BASE_URL ?>/modules/<?= $_SESSION['role'] ?>/dashboard.php" class="header-logo">
                        <img src="<?= BASE_URL ?>/assets/images/brand-logos/PUPR.png" alt="logo topbar" style="height: 40px; ">
                    </a>
                </div>
            </div>
            <div class="header-element">
                <a aria-label="Hide Sidebar" class="sidemenu-toggle header-link animated-arrow hor-toggle horizontal-navtoggle" data-bs-toggle="sidebar" href="javascript:void(0);"><span></span></a>
            </div>
        </div>
        <div class="header-content-right">
            <div class="header-element">
                <a href="javascript:void(0);" class="header-link dropdown-toggle" id="mainHeaderProfile" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="d-flex align-items-center">
                        <div class="header-link-icon">
                            <img src="<?= BASE_URL ?>/assets/images/faces/1.jpg" alt="img" class="rounded-circle" width="32" height="32">
                        </div>
                    </div>
                </a>
                <ul class="main-header-dropdown dropdown-menu header-profile-dropdown dropdown-menu-end" aria-labelledby="mainHeaderProfile">
                    <li>
                        <div class="header-navheading border-bottom">
                            <h6 class="main-notification-title"> Nama : <?= $_SESSION['nama'] ?? 'User'; ?></h6>
                            <p class="main-notification-text mb-0"> Jabatan : <?= ucfirst($_SESSION['role']) ?? ''; ?></p>
                        </div>
                    </li>
                    <li><a class="dropdown-item d-flex border-bottom" href="#"><i class="fe fe-user fs-16 me-2"></i>Profile</a></li>
                    <li><a class="dropdown-item d-flex" href="<?= BASE_URL ?>/auth/logout.php"><i class="fe fe-power fs-16 me-2"></i>Log Out</a></li>
                </ul>
            </div>
        </div>
    </div>
</header>
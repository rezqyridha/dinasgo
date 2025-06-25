<?php
$role = $_SESSION['role'] ?? 'guest';
$menuFile = __DIR__ . "/menu_$role.php";
?>

<!-- Sidebar -->
<aside class="app-sidebar sticky" id="sidebar">
    <!-- Logo Sidebar -->
    <div class="main-sidebar-header">
        <a href="<?= BASE_URL ?>/modules/<?= $role ?>/dashboard.php" class="header-logo">
            <img src="<?= ASSETS_URL ?>/images/brand-logos/PUPR.png" alt="logo sidebar" style="height: 40px;">
        </a>
    </div>

    <!-- Menu Sidebar -->
    <div class="main-sidebar" id="sidebar-scroll">
        <?php if (file_exists($menuFile)): ?>
            <?php require_once $menuFile; ?>
        <?php else: ?>
            <div class='text-danger p-3'>
                Menu untuk role <strong><?= htmlspecialchars($role) ?></strong> tidak ditemukan.
            </div>
        <?php endif; ?>
    </div>
</aside>
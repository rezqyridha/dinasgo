<?php
require_once CONFIG_PATH . '/constants.php';

$current_uri = $_SERVER['REQUEST_URI'];

// Fungsi bantu cocokkan URI
function is_uri_match(array $patterns): bool
{
    global $current_uri;
    foreach ($patterns as $pattern) {
        if (strpos($current_uri, $pattern) !== false) return true;
    }
    return false;
}
?>

<nav class="main-menu-container nav nav-pills flex-column sub-open">
    <ul class="main-menu">

        <!-- DASHBOARD -->
        <li class="slide__category"><span class="category-name">Dashboard</span></li>
        <li class="slide <?= str_contains($current_uri, '/dashboard.php') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/modules/bendahara/dashboard.php" class="side-menu__item">
                <i class="ti-home side-menu__icon"></i>
                <span class="side-menu__label">Dashboard</span>
            </a>
        </li>

        <!-- DATA PERJALANAN -->
        <li class="slide__category"><span class="category-name">Data Perjalanan</span></li>
        <?php
        $data_uri = ['/rincian_biaya/', '/pencairan_dana/'];
        ?>
        <li class="slide has-sub <?= is_uri_match($data_uri) ? 'open active' : '' ?>">
            <a href="#" class="side-menu__item">
                <i class="ti-wallet side-menu__icon"></i>
                <span class="side-menu__label">Manajemen Dana</span>
                <i class="fe fe-chevron-right side-menu__angle"></i>
            </a>
            <ul class="slide-menu child2">
                <li>
                    <a href="<?= BASE_URL ?>/modules/shared/rincian_biaya/index.php"
                        class="side-menu__item <?= str_contains($current_uri, '/rincian_biaya/') ? 'active' : '' ?>">
                        Rincian Biaya
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/modules/shared/pencairan_dana/index.php"
                        class="side-menu__item <?= str_contains($current_uri, '/pencairan_dana/') ? 'active' : '' ?>">
                        Pencairan Dana
                    </a>
                </li>
            </ul>
        </li>

        <!-- LAPORAN -->
        <li class="slide__category"><span class="category-name">Laporan</span></li>
        <?php
        $laporan_uri = ['/laporan/'];
        ?>
        <li class="slide has-sub <?= is_uri_match($laporan_uri) ? 'open active' : '' ?>">
            <a href="#" class="side-menu__item">
                <i class="ti-bar-chart side-menu__icon"></i>
                <span class="side-menu__label">Laporan</span>
                <i class="fe fe-chevron-right side-menu__angle"></i>
            </a>
            <ul class="slide-menu child2">
                <li>
                    <a href="<?= BASE_URL ?>/modules/shared/laporan/index.php"
                        class="side-menu__item <?= str_contains($current_uri, '/laporan/') ? 'active' : '' ?>">
                        Laporan Keuangan
                    </a>
                </li>
            </ul>
        </li>

    </ul>
</nav>
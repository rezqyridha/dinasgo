<?php
require_once CONFIG_PATH . '/constants.php';

$current_uri = $_SERVER['REQUEST_URI'];

// Fungsi bantu untuk cocokkan URI
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
        <li class="slide <?= str_contains($current_uri, '/atasan/dashboard.php') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/modules/atasan/dashboard.php" class="side-menu__item">
                <i class="ti-home side-menu__icon"></i>
                <span class="side-menu__label">Dashboard</span>
            </a>
        </li>

        <!-- DATA -->
        <li class="slide__category"><span class="category-name">Data</span></li>
        <?php
        $manajemen_uri = ['/pengajuan/', '/sppd/', '/dokumen/', '/evaluasi/'];
        ?>
        <li class="slide has-sub <?= is_uri_match($manajemen_uri) ? 'open active' : '' ?>">
            <a href="#" class="side-menu__item">
                <i class="ti ti-database side-menu__icon"></i>
                <span class="side-menu__label">Manajemen</span>
                <i class="fe fe-chevron-right side-menu__angle"></i>
            </a>
            <ul class="slide-menu child2">
                <li>
                    <a href="<?= BASE_URL ?>/modules/shared/pengajuan/index.php"
                        class="side-menu__item <?= str_contains($current_uri, '/pengajuan/') ? 'active' : '' ?>">
                        Verifikasi Pengajuan
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/modules/shared/sppd/index.php"
                        class="side-menu__item <?= str_contains($current_uri, '/sppd/') ? 'active' : '' ?>">
                        Lihat SPPD
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/modules/shared/dokumen/index.php"
                        class="side-menu__item <?= str_contains($current_uri, '/dokumen/') ? 'active' : '' ?>">
                        Dokumen Pegawai
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/modules/shared/evaluasi/index.php"
                        class="side-menu__item <?= str_contains($current_uri, '/evaluasi/') ? 'active' : '' ?>">
                        Evaluasi Perjalanan
                    </a>
                </li>
            </ul>
        </li>

        <!-- LAPORAN -->
        <li class="slide__category"><span class="category-name">Laporan</span></li>
        <?php
        $laporan_uri = ['/laporan-evaluasi'];
        ?>
        <li class="slide has-sub <?= is_uri_match($laporan_uri) ? 'open active' : '' ?>">
            <a href="#" class="side-menu__item">
                <i class="ti-bar-chart side-menu__icon"></i>
                <span class="side-menu__label">Laporan</span>
                <i class="fe fe-chevron-right side-menu__angle"></i>
            </a>
            <ul class="slide-menu child2">
                <li>
                    <a href="<?= BASE_URL ?>/modules/atasan/laporan/laporan-evaluasi.php"
                        class="side-menu__item <?= str_contains($current_uri, '/laporan-evaluasi') ? 'active' : '' ?>">
                        Evaluasi Perjalanan
                    </a>
                </li>
            </ul>
        </li>

    </ul>
</nav>
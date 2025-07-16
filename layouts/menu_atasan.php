<?php
require_once CONFIG_PATH . '/constants.php';

$current_uri = $_SERVER['REQUEST_URI'];

// Helper URI
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

        <!-- PENGELOLAAN PERJALANAN -->
        <li class="slide__category"><span class="category-name">Perjalanan Dinas</span></li>
        <?php
        $proses_uri = ['/pengajuan/', '/sppd/', '/dokumen/', '/evaluasi/'];
        ?>
        <li class="slide has-sub <?= is_uri_match($proses_uri) ? 'open active' : '' ?>">
            <a href="#" class="side-menu__item">
                <i class="  ti-briefcase side-menu__icon"></i>
                <span class="side-menu__label">Proses Bawahan</span>
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
        $laporan_uri = ['/perjalanan_dinas', 'laporan/evaluasi'];
        ?>
        <li class="slide has-sub <?= is_uri_match($laporan_uri) ? 'open active' : '' ?>">
            <a href="#" class="side-menu__item">
                <i class="ti-bar-chart side-menu__icon"></i>
                <span class="side-menu__label">Laporan</span>
                <i class="fe fe-chevron-right side-menu__angle"></i>
            </a>
            <ul class="slide-menu child2">
                <li>
                    <a href="<?= BASE_URL ?>/modules/shared/laporan/perjalanan_dinas.php"
                        class="side-menu__item <?= str_contains($current_uri, '/perjalanan_dinas') ? 'active' : '' ?>">
                        Data Perjalanan</a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/modules/shared/laporan/evaluasi.php"
                        class="side-menu__item <?= str_contains($current_uri, '/evaluasi') ? 'active' : '' ?>">
                        Laporan Evaluasi
                    </a>
                </li>
            </ul>
        </li>

    </ul>
</nav>
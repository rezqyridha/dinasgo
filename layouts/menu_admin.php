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
        <li class="slide <?= str_contains($current_uri, '/dashboard.php') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/modules/admin/dashboard.php" class="side-menu__item">
                <i class="ti-home side-menu__icon"></i>
                <span class="side-menu__label">Dashboard</span>
            </a>
        </li>

        <!-- DATA -->
        <li class="slide__category"><span class="category-name">Data</span></li>
        <?php
        $manajemen_uri = ['/user/', '/pegawai/', '/spt/', '/rincian_biaya/', '/pengajuan/', '/pencairan_dana/',  '/sppd/', '/dokumen/', '/evaluasi/'];
        ?>
        <li class="slide has-sub <?= is_uri_match($manajemen_uri) ? 'open active' : '' ?>">
            <a href="#" class="side-menu__item">
                <i class="ti ti-database side-menu__icon"></i>
                <span class="side-menu__label">Manajemen</span>
                <i class="fe fe-chevron-right side-menu__angle"></i>
            </a>
            <ul class="slide-menu child2">
                <li><a href="<?= BASE_URL ?>/modules/admin/pegawai/index.php" class="side-menu__item <?= str_contains($current_uri, '/pegawai/') ? 'active' : '' ?>">Manajemen Pegawai</a></li>
                <li><a href="<?= BASE_URL ?>/modules/admin/user/index.php" class="side-menu__item <?= str_contains($current_uri, '/user/') ? 'active' : '' ?>">Manajemen User</a></li>
                <li><a href="<?= BASE_URL ?>/modules/shared/pengajuan/index.php" class="side-menu__item <?= str_contains($current_uri, '/pengajuan/') ? 'active' : '' ?>">Pengajuan Perjalanan</a></li>
                <li><a href="<?= BASE_URL ?>/modules/shared/spt/index.php" class="side-menu__item <?= str_contains($current_uri, '/spt/') ? 'active' : '' ?>">Manajemen SPT</a></li>
                <li><a href="<?= BASE_URL ?>/modules/shared/rincian_biaya/index.php" class="side-menu__item <?= str_contains($current_uri, '/rincian_biaya/') ? 'active' : '' ?>">Rincian Biaya</a></li>
                <li><a href="<?= BASE_URL ?>/modules/shared/pencairan_dana/index.php" class="side-menu__item <?= str_contains($current_uri, '/pencairan_dana/') ? 'active' : '' ?>">Pencairan Dana</a></li>
                <li><a href="<?= BASE_URL ?>/modules/admin/sppd/index.php" class="side-menu__item <?= str_contains($current_uri, '/sppd/') ? 'active' : '' ?>">Proses SPPD</a></li>
                <li><a href="<?= BASE_URL ?>/modules/admin/dokumen/index.php" class="side-menu__item <?= str_contains($current_uri, '/dokumen/') ? 'active' : '' ?>">Upload Dokumen</a></li>
                <li><a href="<?= BASE_URL ?>/modules/admin/evaluasi/index.php" class="side-menu__item <?= str_contains($current_uri, '/evaluasi/') ? 'active' : '' ?>">Evaluasi Perjalanan</a></li>

            </ul>
        </li>

        <!-- LAPORAN -->
        <li class="slide__category"><span class="category-name">Laporan</span></li>
        <?php
        $laporan_uri = ['/laporan-data', '/laporan-biaya', '/laporan-evaluasi', '/laporan-persetujuan', '/laporan-anggaran', '/laporan-sppd', '/laporan-efektivitas'];
        ?>
        <li class="slide has-sub <?= is_uri_match($laporan_uri) ? 'open active' : '' ?>">
            <a href="#" class="side-menu__item">
                <i class="ti-bar-chart side-menu__icon"></i>
                <span class="side-menu__label">Laporan</span>
                <i class="fe fe-chevron-right side-menu__angle"></i>
            </a>
            <ul class="slide-menu child2">
                <li><a href="<?= BASE_URL ?>/modules/admin/laporan/laporan-data.php" class="side-menu__item <?= str_contains($current_uri, '/laporan-data') ? 'active' : '' ?>">Data Perjalanan</a></li>
                <li><a href="<?= BASE_URL ?>/modules/admin/laporan/laporan-biaya.php" class="side-menu__item <?= str_contains($current_uri, '/laporan-biaya') ? 'active' : '' ?>">Biaya Perjalanan</a></li>
                <li><a href="<?= BASE_URL ?>/modules/admin/laporan/laporan-evaluasi.php" class="side-menu__item <?= str_contains($current_uri, '/laporan-evaluasi') ? 'active' : '' ?>">Evaluasi</a></li>
                <li><a href="<?= BASE_URL ?>/modules/admin/laporan/laporan-persetujuan.php" class="side-menu__item <?= str_contains($current_uri, '/laporan-persetujuan') ? 'active' : '' ?>">Persetujuan</a></li>
                <li><a href="<?= BASE_URL ?>/modules/admin/laporan/laporan-anggaran.php" class="side-menu__item <?= str_contains($current_uri, '/laporan-anggaran') ? 'active' : '' ?>">Monitoring Anggaran</a></li>
                <li><a href="<?= BASE_URL ?>/modules/admin/laporan/laporan-sppd.php" class="side-menu__item <?= str_contains($current_uri, '/laporan-sppd') ? 'active' : '' ?>">Cetak SPPD</a></li>
                <li><a href="<?= BASE_URL ?>/modules/admin/laporan/laporan-efektivitas.php" class="side-menu__item <?= str_contains($current_uri, '/laporan-efektivitas') ? 'active' : '' ?>">Efektivitas</a></li>
            </ul>
        </li>

    </ul>
</nav>
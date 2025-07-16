<?php
require_once CONFIG_PATH . '/constants.php';

$current_uri = $_SERVER['REQUEST_URI'];

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

        <!-- KHUSUS ADMIN -->
        <li class="slide__category"><span class="category-name">Manajemen Khusus</span></li>
        <li class="slide <?= str_contains($current_uri, '/user/') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/modules/admin/user/index.php" class="side-menu__item">
                <i class="ti-user side-menu__icon"></i>
                <span class="side-menu__label">Manajemen User</span>
            </a>
        </li>
        <li class="slide <?= str_contains($current_uri, '/pegawai/') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/modules/admin/pegawai/index.php" class="side-menu__item">
                <i class="ti-id-badge side-menu__icon"></i>
                <span class="side-menu__label">Manajemen Pegawai</span>
            </a>
        </li>
        <li class="slide <?= str_contains($current_uri, '/kepala/') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/modules/admin/kepala/index.php" class="side-menu__item">
                <i class="ti-briefcase side-menu__icon"></i>
                <span class="side-menu__label">Data Kepala</span>
            </a>
        </li>

        <!-- DATA PROSES PERJALANAN DINAS -->
        <li class="slide__category"><span class="category-name">Proses Perjalanan Dinas</span></li>
        <?php
        $proses_uri = ['/pengajuan/', '/spt/', '/sppd/', '/dokumen/', '/rincian_biaya/', '/pencairan_dana/', '/evaluasi/'];
        ?>
        <li class="slide has-sub <?= is_uri_match($proses_uri) ? 'open active' : '' ?>">
            <a href="#" class="side-menu__item">
                <i class="ti ti-database side-menu__icon"></i>
                <span class="side-menu__label">Alur Perjalanan Dinas</span>
                <i class="fe fe-chevron-right side-menu__angle"></i>
            </a>
            <ul class="slide-menu child2">
                <li><a href="<?= BASE_URL ?>/modules/shared/pengajuan/index.php" class="side-menu__item <?= str_contains($current_uri, '/pengajuan/') ? 'active' : '' ?>">Pengajuan Perjalanan</a></li>
                <li><a href="<?= BASE_URL ?>/modules/shared/spt/index.php" class="side-menu__item <?= str_contains($current_uri, '/spt/') ? 'active' : '' ?>">Manajemen SPT</a></li>
                <li><a href="<?= BASE_URL ?>/modules/shared/sppd/index.php" class="side-menu__item <?= str_contains($current_uri, '/sppd/') ? 'active' : '' ?>">Proses SPPD</a></li>
                <li><a href="<?= BASE_URL ?>/modules/shared/dokumen/index.php" class="side-menu__item <?= str_contains($current_uri, '/dokumen/') ? 'active' : '' ?>">Upload Dokumen</a></li>
                <li><a href="<?= BASE_URL ?>/modules/shared/rincian_biaya/index.php" class="side-menu__item <?= str_contains($current_uri, '/rincian_biaya/') ? 'active' : '' ?>">Rincian Biaya</a></li>
                <li><a href="<?= BASE_URL ?>/modules/shared/pencairan_dana/index.php" class="side-menu__item <?= str_contains($current_uri, '/pencairan_dana/') ? 'active' : '' ?>">Pencairan Dana</a></li>
                <li><a href="<?= BASE_URL ?>/modules/shared/evaluasi/index.php" class="side-menu__item <?= str_contains($current_uri, '/evaluasi/') ? 'active' : '' ?>">Evaluasi Perjalanan</a></li>
            </ul>
        </li>

        <!-- LAPORAN -->
        <li class="slide__category"><span class="category-name">Laporan</span></li>
        <?php
        // Perbaiki pattern di sini dengan prefix '/laporan/'
        $laporan_uri = ['/laporan/perjalanan_dinas', '/laporan/rincian_biaya', '/laporan/pencairan_dana', '/laporan/evaluasi'];
        ?>
        <li class="slide has-sub <?= is_uri_match($laporan_uri) ? 'open active' : '' ?>">
            <a href="#" class="side-menu__item">
                <i class="ti-bar-chart side-menu__icon"></i>
                <span class="side-menu__label">Laporan</span>
                <i class="fe fe-chevron-right side-menu__angle"></i>
            </a>
            <ul class="slide-menu child2">
                <li><a href="<?= BASE_URL ?>/modules/shared/laporan/perjalanan_dinas.php" class="side-menu__item <?= str_contains($current_uri, '/laporan/perjalanan_dinas') ? 'active' : '' ?>">Data Perjalanan</a></li>
                <li><a href="<?= BASE_URL ?>/modules/shared/laporan/rincian_biaya.php" class="side-menu__item <?= str_contains($current_uri, '/laporan/rincian_biaya') ? 'active' : '' ?>">Biaya Perjalanan</a></li>
                <li><a href="<?= BASE_URL ?>/modules/shared/laporan/pencairan_dana.php" class="side-menu__item <?= str_contains($current_uri, '/laporan/pencairan_dana') ? 'active' : '' ?>">Pencairan Dana</a></li>
                <li><a href="<?= BASE_URL ?>/modules/shared/laporan/evaluasi.php" class="side-menu__item <?= str_contains($current_uri, '/laporan/evaluasi') ? 'active' : '' ?>">Evaluasi</a></li>
            </ul>
        </li>

    </ul>
</nav>
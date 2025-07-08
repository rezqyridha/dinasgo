<?php
$current_uri = $_SERVER['REQUEST_URI'];

function isMenuActive($paths = [])
{
    foreach ($paths as $path) {
        if (str_contains($GLOBALS['current_uri'], $path)) {
            return true;
        }
    }
    return false;
}

function isSubmenuActive($path)
{
    return str_contains($GLOBALS['current_uri'], $path) ? 'active' : '';
}

$submenuPengajuan = isMenuActive(['/modules/shared/pengajuan', '/modules/shared/spt', '/modules/shared/upload_dokumen']);
$submenuEvaluasi = isMenuActive(['/modules/shared/evaluasi']);
?>

<!-- SIDEBAR MENU PEGAWAI -->
<nav class="main-menu-container nav nav-pills flex-column sub-open">
    <ul class="main-menu">

        <!-- DASHBOARD -->
        <li class="slide__category"><span class="category-name">Dashboard</span></li>
        <li class="slide <?= isSubmenuActive('/dashboard.php') ?>">
            <a href="<?= BASE_URL ?>/modules/pegawai/dashboard.php" class="side-menu__item">
                <i class="ti-home side-menu__icon"></i>
                <span class="side-menu__label">Dashboard</span>
            </a>
        </li>

        <!-- PENGAJUAN -->
        <li class="slide__category"><span class="category-name">Perjalanan</span></li>
        <li class="slide has-sub <?= $submenuPengajuan ? 'open active' : '' ?>">
            <a href="#" class="side-menu__item">
                <i class="ti-briefcase side-menu__icon"></i>
                <span class="side-menu__label">Pengajuan</span>
                <i class="fe fe-chevron-right side-menu__angle"></i>
            </a>
            <ul class="slide-menu child2">
                <li><a href="<?= BASE_URL ?>/modules/shared/pengajuan/index.php" class="side-menu__item <?= isSubmenuActive('/pengajuan') ?>">Form Pengajuan</a></li>
                <li><a href="<?= BASE_URL ?>/modules/shared/spt/index.php" class="side-menu__item <?= isSubmenuActive('/spt') ?>">Surat Perintah (SPT)</a></li>
                <li><a href="<?= BASE_URL ?>/modules/shared/sppd/index.php" class="side-menu__item <?= isSubmenuActive('/sppd') ?>">SPPD</a></li>
                <li><a href="<?= BASE_URL ?>/modules/shared/rincian_biaya/index.php" class="side-menu__item <?= isSubmenuActive('/rincian_biaya') ?>">Rincian Biaya</a></li>
                <li><a href="<?= BASE_URL ?>/modules/shared/pencairan_dana/index.php" class="side-menu__item <?= isSubmenuActive('/pencairan_dana') ?>">Pencairan Dana</a></li>
                <li><a href="<?= BASE_URL ?>/modules/shared/dokumen/index.php" class="side-menu__item <?= isSubmenuActive('/upload_dokumen') ?>">Upload Dokumen</a></li>
            </ul>
        </li>

        <!-- EVALUASI -->
        <li class="slide__category"><span class="category-name">Evaluasi</span></li>
        <li class="slide has-sub <?= $submenuEvaluasi ? 'open active' : '' ?>">
            <a href="#" class="side-menu__item">
                <i class="ti-check-box side-menu__icon"></i>
                <span class="side-menu__label">Evaluasi</span>
                <i class="fe fe-chevron-right side-menu__angle"></i>
            </a>
            <ul class="slide-menu child2">
                <li><a href="<?= BASE_URL ?>/modules/shared/evaluasi/index.php" class="side-menu__item <?= isSubmenuActive('/evaluasi') ?>">Evaluasi Perjalanan</a></li>
            </ul>
        </li>

    </ul>
</nav>
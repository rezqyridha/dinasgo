<?php
require_once BASE_PATH . '/config/constants.php';
$current_page = str_replace(BASE_PATH, '', __FILE__);
?>

<!-- Start::nav -->
<nav class="main-menu-container nav nav-pills flex-column sub-open">
    <div class="slide-left" id="slide-left">
        <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
            <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
        </svg>
    </div>

    <ul class="main-menu">
        <!-- Dashboard -->
        <li class="slide__category"><span class="category-name">Dashboard</span></li>
        <li class="slide <?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/modules/admin/dashboard.php" class="side-menu__item">
                <span class="shape1"></span><span class="shape2"></span>
                <i class="ti-home side-menu__icon"></i>
                <span class="side-menu__label">Dashboard</span>
            </a>
        </li>

        <li class="slide__category"><span class="category-name">Data</span></li>
        <li class="slide has-sub <?= in_array($current_page, ['pegawai.php', 'user.php', 'pengajuan.php', 'persetujuan.php', 'sppd.php', 'dokumen.php', 'evaluasi.php']) ? 'active' : '' ?>">
            <a href="javascript:void(0);" class="side-menu__item">
                <span class="shape1"></span><span class="shape2"></span>
                <i class="ti ti-database side-menu__icon"></i>
                <span class="side-menu__label">Manajemen</span>
                <i class="fe fe-chevron-right side-menu__angle"></i>
            </a>
            <ul class="slide-menu child2">
                <li class="slide">
                    <a href="<?= BASE_URL ?>/modules/shared/pegawai/index.php" class="side-menu__item <?= $current_page === 'index.php' ? 'active' : '' ?>">
                        Manajemen Pegawai
                    </a>
                </li>
                <li class="slide">
                    <a href="<?= BASE_URL ?>/modules/admin/user/index.php" class="side-menu__item <?= $current_page === 'index.php' ? 'active' : '' ?>">
                        Manajemen User (Login/Akun)
                    </a>
                </li>
                <li class="slide">
                    <a href="<?= BASE_URL ?>/modules/admin/index.php.php" class="side-menu__item <?= $current_page === 'pengajuan.php' ? 'active' : '' ?>">
                        Form Pengajuan Perjalanan
                    </a>
                </li>
                <li class="slide">
                    <a href="<?= BASE_URL ?>/modules/admin/index.php" class="side-menu__item <?= $current_page === 'persetujuan.php' ? 'active' : '' ?>">
                        Persetujuan Perjalanan
                    </a>
                </li>
                <li class="slide">
                    <a href="<?= BASE_URL ?>/modules/admin/index.php" class="side-menu__item <?= $current_page === 'sppd.php' ? 'active' : '' ?>">
                        Pencairan Biaya / Proses SPPD
                    </a>
                </li>
                <li class="slide">
                    <a href="<?= BASE_URL ?>/modules/admin/dokumen.php" class="side-menu__item <?= $current_page === 'dokumen.php' ? 'active' : '' ?>">
                        Upload Dokumen Perjalanan
                    </a>
                </li>
                <li class="slide">
                    <a href="<?= BASE_URL ?>/modules/admin/evaluasi.php" class="side-menu__item <?= $current_page === 'evaluasi.php' ? 'active' : '' ?>">
                        Evaluasi Perjalanan
                    </a>
                </li>
            </ul>
        </li>


        <!-- Laporan -->
        <!-- Kategori Laporan -->
        <li class="slide__category"><span class="category-name">Laporan</span></li>
        <li class="slide has-sub <?= str_contains($current_page, '/modules/admin/laporan') ? 'active' : '' ?>">
            <a href="javascript:void(0);" class="side-menu__item">
                <span class="shape1"></span><span class="shape2"></span>
                <i class="fa fa-file side-menu__icon"></i>
                <span class="side-menu__label">Laporan</span>
                <i class="fe fe-chevron-right side-menu__angle"></i>
            </a>
            <ul class="slide-menu child2">
                <li class="slide">
                    <a href="<?= BASE_URL ?>/modules/admin/laporan/laporan-data.php"
                        class="side-menu__item <?= str_contains($current_page, '/laporan/laporan-data.php') ? 'active' : '' ?>">
                        Laporan Data Perjalanan Dinas
                    </a>
                </li>
                <li class="slide">
                    <a href="<?= BASE_URL ?>/modules/admin/laporan/laporan-biaya.php"
                        class="side-menu__item <?= str_contains($current_page, '/laporan/laporan-biaya.php') ? 'active' : '' ?>">
                        Laporan Biaya Perjalanan
                    </a>
                </li>
                <li class="slide">
                    <a href="<?= BASE_URL ?>/modules/admin/laporan/laporan-evaluasi.php"
                        class="side-menu__item <?= str_contains($current_page, '/laporan/laporan-evaluasi.php') ? 'active' : '' ?>">
                        Laporan Evaluasi Perjalanan
                    </a>
                </li>
                <li class="slide">
                    <a href="<?= BASE_URL ?>/modules/admin/laporan/laporan-persetujuan.php"
                        class="side-menu__item <?= str_contains($current_page, '/laporan/laporan-persetujuan.php') ? 'active' : '' ?>">
                        Laporan Persetujuan
                    </a>
                </li>
                <li class="slide">
                    <a href="<?= BASE_URL ?>/modules/admin/laporan/laporan-anggaran.php"
                        class="side-menu__item <?= str_contains($current_page, '/laporan/laporan-anggaran.php') ? 'active' : '' ?>">
                        Laporan Monitoring Anggaran
                    </a>
                </li>
                <li class="slide">
                    <a href="<?= BASE_URL ?>/modules/admin/laporan/laporan-sppd.php"
                        class="side-menu__item <?= str_contains($current_page, '/laporan/laporan-sppd.php') ? 'active' : '' ?>">
                        Laporan SPPD (Cetak Format Resmi)
                    </a>
                </li>
                <li class="slide">
                    <a href="<?= BASE_URL ?>/modules/admin/laporan/laporan-efektivitas.php"
                        class="side-menu__item <?= str_contains($current_page, '/laporan/laporan-efektivitas.php') ? 'active' : '' ?>">
                        Laporan Efektivitas Perjalanan
                    </a>
                </li>
            </ul>
        </li>
        <div class="slide-right" id="slide-right">
            <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path>
            </svg>
        </div>
</nav>
<!-- End::nav -->
<?php
$current_page = basename($_SERVER['PHP_SELF']);
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
            <a href="/dinasgo/modules/admin/dashboard.php" class="side-menu__item">
                <span class="shape1"></span><span class="shape2"></span>
                <i class="ti-home side-menu__icon"></i>
                <span class="side-menu__label">Dashboard</span>
            </a>
        </li>

        <!-- Form -->
        <li class="slide__category"><span class="category-name">Form</span></li>
        <li class="slide has-sub <?= in_array($current_page, ['form-pegawai.php', 'form-spt.php', 'form-biperjalanan.php', 'form-dokperjalanan.php', 'form-pelakperjalanan.php', 'form-persetujuan.php', 'form-kepala.php']) ? 'active' : '' ?>">
            <a href="javascript:void(0);" class="side-menu__item">
                <span class="shape1"></span><span class="shape2"></span>
                <i class="ti-write side-menu__icon"></i>
                <span class="side-menu__label">Form</span>
                <i class="fe fe-chevron-right side-menu__angle"></i>
            </a>
            <ul class="slide-menu child1">
                <li class="slide"><a href="/dinasgo/modules/admin/form-pegawai.php" class="side-menu__item <?= $current_page === 'form-pegawai.php' ? 'active' : '' ?>">Pegawai</a></li>
                <li class="slide"><a href="/dinasgo/modules/admin/form-spt.php" class="side-menu__item <?= $current_page === 'form-spt.php' ? 'active' : '' ?>">SPT</a></li>
                <li class="slide"><a href="/dinasgo/modules/admin/form-biperjalanan.php" class="side-menu__item <?= $current_page === 'form-biperjalanan.php' ? 'active' : '' ?>">Biaya Perjalanan</a></li>
                <li class="slide"><a href="/dinasgo/modules/admin/form-dokperjalanan.php" class="side-menu__item <?= $current_page === 'form-dokperjalanan.php' ? 'active' : '' ?>">Dokumen Biaya Perjalanan</a></li>
                <li class="slide"><a href="/dinasgo/modules/admin/form-pelakperjalanan.php" class="side-menu__item <?= $current_page === 'form-pelakperjalanan.php' ? 'active' : '' ?>">Pelaksanaan Perjalanan</a></li>
                <li class="slide"><a href="/dinasgo/modules/admin/form-persetujuan.php" class="side-menu__item <?= $current_page === 'form-persetujuan.php' ? 'active' : '' ?>">Persetujuan SPPD</a></li>
                <li class="slide"><a href="/dinasgo/modules/admin/form-kepala.php" class="side-menu__item <?= $current_page === 'form-kepala.php' ? 'active' : '' ?>">Kepala</a></li>
            </ul>
        </li>

        <!-- Data -->
        <li class="slide__category"><span class="category-name">Data</span></li>
        <li class="slide has-sub <?= in_array($current_page, ['pegawai.php', 'spt.php', 'biperjalanan.php', 'dokperjalanan.php', 'pelakperjalanan.php', 'persetujuan.php', 'kepala.php']) ? 'active' : '' ?>">
            <a href="javascript:void(0);" class="side-menu__item">
                <span class="shape1"></span><span class="shape2"></span>
                <i class="ti ti-database side-menu__icon"></i>
                <span class="side-menu__label">Data</span>
                <i class="fe fe-chevron-right side-menu__angle"></i>
            </a>
            <ul class="slide-menu child2">
                <li class="slide"><a href="/dinasgo/modules/shared/pegawai/index.php" class="side-menu__item">Pegawai</a></li>
                <li class="slide"><a href="/dinasgo/modules/admin/spt.php" class="side-menu__item <?= $current_page === 'spt.php' ? 'active' : '' ?>">SPT</a></li>
                <li class="slide"><a href="/dinasgo/modules/admin/biperjalanan.php" class="side-menu__item <?= $current_page === 'biperjalanan.php' ? 'active' : '' ?>">Biaya Perjalanan</a></li>
                <li class="slide"><a href="/dinasgo/modules/admin/dokperjalanan.php" class="side-menu__item <?= $current_page === 'dokperjalanan.php' ? 'active' : '' ?>">Dokumen Biaya Perjalanan</a></li>
                <li class="slide"><a href="/dinasgo/modules/admin/pelakperjalanan.php" class="side-menu__item <?= $current_page === 'pelakperjalanan.php' ? 'active' : '' ?>">Pelaksanaan Perjalanan</a></li>
                <li class="slide"><a href="/dinasgo/modules/admin/persetujuan.php" class="side-menu__item <?= $current_page === 'persetujuan.php' ? 'active' : '' ?>">Persetujuan SPPD</a></li>
                <li class="slide"><a href="/dinasgo/modules/admin/kepala.php" class="side-menu__item <?= $current_page === 'kepala.php' ? 'active' : '' ?>">Kepala</a></li>
            </ul>
        </li>

        <!-- Laporan -->
        <li class="slide__category"><span class="category-name">Laporan</span></li>
        <li class="slide has-sub <?= in_array($current_page, ['laporan-spt.php', 'laporan-biperjalanan.php', 'laporan-dokperjalanan.php', 'laporan-pelakperjalanan.php', 'laporan-persetujuan.php']) ? 'active' : '' ?>">
            <a href="javascript:void(0);" class="side-menu__item">
                <span class="shape1"></span><span class="shape2"></span>
                <i class="fa fa-file side-menu__icon"></i>
                <span class="side-menu__label">Laporan</span>
                <i class="fe fe-chevron-right side-menu__angle"></i>
            </a>
            <ul class="slide-menu child2">
                <li class="slide"><a href="/dinasgo/modules/admin/laporan-spt.php" class="side-menu__item <?= $current_page === 'laporan-spt.php' ? 'active' : '' ?>">SPT</a></li>
                <li class="slide"><a href="/dinasgo/modules/admin/laporan-biperjalanan.php" class="side-menu__item <?= $current_page === 'laporan-biperjalanan.php' ? 'active' : '' ?>">Biaya Perjalanan Dinas</a></li>
                <li class="slide"><a href="/dinasgo/modules/admin/laporan-dokperjalanan.php" class="side-menu__item <?= $current_page === 'laporan-dokperjalanan.php' ? 'active' : '' ?>">Dokumen Biaya Perjalanan</a></li>
                <li class="slide"><a href="/dinasgo/modules/admin/laporan-pelakperjalanan.php" class="side-menu__item <?= $current_page === 'laporan-pelakperjalanan.php' ? 'active' : '' ?>">Pelaksanaan Perjalanan</a></li>
                <li class="slide"><a href="/dinasgo/modules/admin/laporan-persetujuan.php" class="side-menu__item <?= $current_page === 'laporan-persetujuan.php' ? 'active' : '' ?>">Persetujuan SPPD</a></li>
            </ul>
        </li>
    </ul>

    <div class="slide-right" id="slide-right">
        <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
            <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path>
        </svg>
    </div>
</nav>
<!-- End::nav -->
<?php
$pageTitle = 'Dashboard Admin';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/koneksi.php';

// Query jumlah pegawai
$pegawai_query = "SELECT COUNT(*) as total FROM pegawai";
$pegawai_result = $conn->query($pegawai_query);
$total_pegawai = ($pegawai_result && $pegawai_result->num_rows > 0)
    ? $pegawai_result->fetch_assoc()['total'] : 0;

// Query jumlah sppd
$sppd_query = "SELECT COUNT(*) AS total FROM sppd";
$sppd_result = $conn->query($sppd_query);
$total_sppd = ($sppd_result && $sppd_result->num_rows > 0)
    ? $sppd_result->fetch_assoc()['total'] : 0;

// Data dummy (nanti diganti query asli)
$total_evaluasi = 18;
$total_pencairan = 12;
$pending_pengajuan = 2;
?>

<!DOCTYPE html>
<html lang="id">
<?php include '../../layouts/head.php'; ?>

<body>
    <div class="page">

        <?php include '../../layouts/header.php'; ?>
        <?php include '../../layouts/topbar.php'; ?>
        <?php include '../../layouts/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content app-content">
            <div class="container-fluid">

                <!-- Header -->
                <div class="d-md-flex d-block align-items-center justify-content-between page-header-breadcrumb">
                    <div>
                        <h2 class="main-content-title fs-24 mb-1">Selamat Datang di Aplikasi Perjalanan Dinas</h2>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Halaman Dashboard</li>
                        </ol>
                    </div>
                </div>

                <!-- Notifikasi -->
                <div class="alert alert-danger mt-3">
                    Anda memiliki <strong><?= $pending_pengajuan ?></strong> pengajuan perjalanan dinas yang menunggu persetujuan.
                </div>

                <!-- Cards -->
                <div class="row row-sm mt-4">
                    <?php
                    $cards = [
                        ['Jumlah Pegawai', $total_pegawai],
                        ['Total Perjalanan Dinas', $total_sppd],
                        ['Evaluasi Perjalanan', $total_evaluasi],
                        ['Pencairan Dana', $total_pencairan],
                    ];
                    foreach ($cards as [$label, $count]) {
                        echo "
                <div class='col-lg-3 col-md-6 mb-3'>
                    <div class='card custom-card'>
                        <div class='card-body'>
                            <h5 class='fs-14'>$label</h5>
                            <h4 class='mb-0'>" . htmlspecialchars((string)$count) . "</h4>
                        </div>
                    </div>
                </div>";
                    }
                    ?>
                </div>

                <!-- Grafik Evaluasi -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card custom-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Grafik Evaluasi Perjalanan</h5>
                            </div>
                            <div class="card-body">
                                <div id="chart-evaluasi" style="height: 250px;"></div>
                                <script>
                                    document.addEventListener("DOMContentLoaded", function() {
                                        var options = {
                                            chart: {
                                                type: 'bar',
                                                height: 250
                                            },
                                            series: [{
                                                name: 'Skor Evaluasi',
                                                data: [85, 90, 78, 92, 88]
                                            }],
                                            xaxis: {
                                                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei']
                                            }
                                        };
                                        new ApexCharts(document.querySelector("#chart-evaluasi"), options).render();
                                    });
                                </script>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>



        <?php include_once __DIR__ . '/../../layouts/footer.php'; ?>
        <?php include_once __DIR__ . '/../../layouts/scripts.php'; ?>
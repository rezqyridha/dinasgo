<?php
require_once __DIR__ . '/../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Dashboard Admin';

// Fungsi helper count query
function count_rows($conn, $query)
{
    $result = $conn->query($query);
    return ($result && $result->num_rows > 0) ? (int) $result->fetch_assoc()['total'] : 0;
}

// Jumlah pegawai
$total_pegawai = count_rows($conn, "SELECT COUNT(*) as total FROM pegawai");

// Total pengajuan perjalanan dinas
$total_pengajuan = count_rows($conn, "SELECT COUNT(*) as total FROM pengajuan_perjalanan");

// Evaluasi selesai
$total_evaluasi = count_rows($conn, "SELECT COUNT(*) as total FROM evaluasi_perjalanan WHERE status = 'selesai'");

// Pencairan dana selesai
$total_pencairan = count_rows($conn, "SELECT COUNT(*) as total FROM pencairan_dana WHERE status = 'selesai'");

// Pending pengajuan
$pending_pengajuan = count_rows($conn, "SELECT COUNT(*) as total FROM pengajuan_perjalanan WHERE status = 'diajukan'");

$evaluasi_per_bulan_query = "
    SELECT 
      DATE_FORMAT(p.created_at, '%Y-%m') AS bulan,
      COUNT(e.id) AS total
    FROM evaluasi_perjalanan e
    JOIN pengajuan_perjalanan p ON e.id_pengajuan = p.id
    WHERE e.status = 'selesai'
    GROUP BY bulan
    ORDER BY bulan ASC
";


$evaluasi_per_bulan_result = $conn->query($evaluasi_per_bulan_query);

$bulan = [];
$total = [];

if ($evaluasi_per_bulan_result && $evaluasi_per_bulan_result->num_rows > 0) {
    while ($row = $evaluasi_per_bulan_result->fetch_assoc()) {
        $bulan[] = $row['bulan'];
        $total[] = (int) $row['total'];
    }
} else {
    $bulan = ['-'];
    $total = [0];
}


// Layout
include_once LAYOUTS_PATH . '/head.php';
include_once LAYOUTS_PATH . '/header.php';
include_once LAYOUTS_PATH . '/sidebar.php';
include_once LAYOUTS_PATH . '/topbar.php';
?>

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

        <!-- Notifikasi Pending -->
        <?php if ($pending_pengajuan > 0): ?>
            <div class="alert alert-danger mt-3">
                Anda memiliki <strong><?= htmlspecialchars($pending_pengajuan) ?></strong> pengajuan perjalanan dinas yang menunggu persetujuan.
            </div>
        <?php endif; ?>

        <!-- Cards -->
        <div class="row row-sm mt-4">
            <?php
            $cards = [
                ['Jumlah Pegawai', $total_pegawai],
                ['Total Perjalanan Dinas', $total_pengajuan],
                ['Evaluasi Perjalanan', $total_evaluasi],
                ['Pencairan Dana', $total_pencairan],
            ];
            foreach ($cards as [$label, $count]): ?>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card custom-card">
                        <div class="card-body">
                            <h5 class="fs-14"><?= htmlspecialchars($label) ?></h5>
                            <h4 class="mb-0"><?= htmlspecialchars((string)$count) ?></h4>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Grafik Evaluasi -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Grafik Evaluasi Perjalanan per Bulan</h5>
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
                                        name: 'Jumlah Evaluasi',
                                        data: <?= json_encode($total) ?>
                                    }],
                                    xaxis: {
                                        categories: <?= json_encode($bulan) ?>
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

<?php
include_once LAYOUTS_PATH . '/footer.php';
include_once LAYOUTS_PATH . '/scripts.php';
?>
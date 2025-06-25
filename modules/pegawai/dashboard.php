<?php
require_once __DIR__ . '/../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Dashboard Pegawai';

$id_user = $_SESSION['id_user'] ?? 0;
$stmt = $conn->prepare("SELECT id FROM pegawai WHERE id_user = ?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$stmt->bind_result($id_pegawai);
$stmt->fetch();
$stmt->close();

$id_pegawai = $id_pegawai ?: 0;

// Statistik
$total_pengajuan  = $conn->query("SELECT COUNT(*) AS total FROM pengajuan_perjalanan WHERE id_pegawai = $id_pegawai")->fetch_assoc()['total'] ?? 0;
$total_disetujui  = $conn->query("SELECT COUNT(*) AS total FROM pengajuan_perjalanan WHERE id_pegawai = $id_pegawai AND status = 'disetujui'")->fetch_assoc()['total'] ?? 0;
$total_ditolak    = $conn->query("SELECT COUNT(*) AS total FROM pengajuan_perjalanan WHERE id_pegawai = $id_pegawai AND status = 'ditolak'")->fetch_assoc()['total'] ?? 0;
$total_evaluasi   = $conn->query("SELECT COUNT(*) AS total FROM evaluasi_perjalanan WHERE id_pegawai = $id_pegawai")->fetch_assoc()['total'] ?? 0;
$total_dokumen    = $conn->query("SELECT COUNT(*) AS total FROM dokumen d JOIN pengajuan_perjalanan p ON d.id_pengajuan = p.id WHERE p.id_pegawai = $id_pegawai")->fetch_assoc()['total'] ?? 0;
$total_cair       = $conn->query("SELECT SUM(jumlah_dana) AS total FROM pencairan_dana p JOIN pengajuan_perjalanan pp ON p.id_pengajuan = pp.id WHERE pp.id_pegawai = $id_pegawai")->fetch_assoc()['total'] ?? 0;

// Data untuk Chart: Jumlah Pengajuan 5 Bulan Terakhir
$chart_labels = [];
$chart_data = array_fill(0, 5, 0); // default 0 untuk 5 bulan

$bulan_map = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$now = date('n'); // bulan sekarang

for ($i = 4; $i >= 0; $i--) {
    $bulan_ke = ($now - $i <= 0) ? 12 + ($now - $i) : ($now - $i);
    $chart_labels[] = $bulan_map[$bulan_ke - 1];
    $bulan_index[$bulan_ke] = count($chart_labels) - 1; // mapping bulan ke indeks
}

// Query real ke DB
$sql = "
SELECT 
    MONTH(created_at) AS bulan_angka,
    COUNT(*) AS total
FROM pengajuan_perjalanan
WHERE id_pegawai = $id_pegawai
    AND created_at >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
GROUP BY MONTH(created_at)
ORDER BY MONTH(created_at)
";


$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) {
    $bln = (int)$row['bulan_angka'];
    if (isset($bulan_index[$bln])) {
        $chart_data[$bulan_index[$bln]] = (int)$row['total'];
    }
}



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
                <h2 class="main-content-title fs-24 mb-1">Selamat Datang, <?= htmlspecialchars($_SESSION['nama']) ?></h2>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                    <li class="breadcrumb-item active">Pegawai</li>
                </ol>
            </div>
        </div>

        <!-- Notifikasi -->
        <div class="alert alert-info mt-2 ">
            Pastikan Anda menyelesaikan <strong>pengajuan</strong> sebelum tanggal perjalanan. Jika status pengajuan <strong>ditolak</strong>, silakan periksa ulang data Anda.
        </div>

        <!-- Cards -->
        <div class="row mt-2 g-3">
            <?php
            $cards = [
                ['Total Pengajuan', $total_pengajuan, 'fe-send', 'bg-primary'],
                ['Disetujui', $total_disetujui, 'fe-check-circle', 'bg-success'],
                ['Ditolak', $total_ditolak, 'fe-x-circle', 'bg-danger'],
                ['Evaluasi Dikirim', $total_evaluasi, 'fe-edit-2', 'bg-info'],
                ['Dokumen Diupload', $total_dokumen, 'fe-upload', 'bg-warning'],
                ['Dana Cair (Rp)', number_format($total_cair, 0, ',', '.'), 'fe-dollar-sign', 'bg-pink']
            ];

            foreach ($cards as [$label, $value, $icon, $bg]) {
                echo "
                <div class='col-lg-4 col-md-6'>
                    <div class='card custom-card shadow-sm'>
                        <div class='card-body d-flex align-items-center'>
                            <div class='me-3'>
                                <div class='avatar bg-opacity-75 $bg text-white rounded-circle'>
                                    <i class='fe $icon'></i>
                                </div>
                            </div>
                            <div>
                                <h6 class='fs-13 mb-1 text-muted'>$label</h6>
                                <h4 class='mb-0 fw-bold'>" . htmlspecialchars((string)$value) . "</h4>
                            </div>
                        </div>
                    </div>
                </div>";
            }
            ?>
        </div>

        <!-- Grafik Dummy -->
        <div class="card custom-card mt-4 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Grafik Pengajuan Perjalanan 5 Bulan Terakhir</h5>
            </div>
            <div class="card-body">
                <div id="chart-pegawai" style="height: 260px;"></div>
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        var options = {
                            chart: {
                                type: 'bar',
                                height: 260
                            },
                            series: [{
                                name: 'Jumlah Pengajuan',
                                data: <?= json_encode($chart_data) ?>
                            }],
                            xaxis: {
                                categories: <?= json_encode($chart_labels) ?>
                            },
                            colors: ['#1d8cf8']
                        };
                        new ApexCharts(document.querySelector("#chart-pegawai"), options).render();
                    });
                </script>
            </div>
        </div>


    </div>
</div>

<?php
include_once LAYOUTS_PATH . '/footer.php';
include_once LAYOUTS_PATH . '/scripts.php';
?>
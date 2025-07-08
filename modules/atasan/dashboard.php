<?php
require_once __DIR__ . '/../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Dashboard Atasan';

$id_atasan = $_SESSION['id_user'] ?? 0;

// Total Pengajuan Bawahan (semua status)
$pengajuan_sql = "
    SELECT COUNT(*) as total
    FROM pengajuan_perjalanan p
    JOIN pegawai pg ON p.id_pegawai = pg.id
    WHERE pg.id_atasan = ?
";
$stmt = $conn->prepare($pengajuan_sql);
$stmt->bind_param("i", $id_atasan);
$stmt->execute();
$result = $stmt->get_result();
$total_pengajuan = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['total'] : 0;
$stmt->close();

// Pengajuan Menunggu Verifikasi (status = diajukan)
$pending_sql = "
    SELECT COUNT(*) as total 
    FROM pengajuan_perjalanan p
    JOIN pegawai pg ON p.id_pegawai = pg.id
    WHERE p.status = 'diajukan' AND pg.id_atasan = ?
";
$stmt = $conn->prepare($pending_sql);
$stmt->bind_param("i", $id_atasan);
$stmt->execute();
$result = $stmt->get_result();
$pending_pengajuan = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['total'] : 0;
$stmt->close();

// Total SPPD Bawahan (masih pakai persetujuan)
$sppd_sql = "
    SELECT COUNT(*) as total
    FROM sppd 
    WHERE id_pengajuan IN (SELECT id_pengajuan FROM persetujuan WHERE id_verifikator = ?)
";
$stmt = $conn->prepare($sppd_sql);
$stmt->bind_param("i", $id_atasan);
$stmt->execute();
$result = $stmt->get_result();
$total_sppd = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['total'] : 0;
$stmt->close();

// Total Evaluasi Bawahan (masih pakai persetujuan)
$evaluasi_sql = "
    SELECT COUNT(*) as total
    FROM evaluasi_perjalanan
    WHERE id_pengajuan IN (SELECT id_pengajuan FROM persetujuan WHERE id_verifikator = ?)
";
$stmt = $conn->prepare($evaluasi_sql);
$stmt->bind_param("i", $id_atasan);
$stmt->execute();
$result = $stmt->get_result();
$total_evaluasi = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['total'] : 0;
$stmt->close();

include_once LAYOUTS_PATH . '/head.php';
include_once LAYOUTS_PATH . '/header.php';
include_once LAYOUTS_PATH . '/sidebar.php';
include_once LAYOUTS_PATH . '/topbar.php';
?>

<div class="main-content app-content">
    <div class="container-fluid">

        <!-- Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between page-header-breadcrumb">
            <div>
                <h2 class="main-content-title fs-24 mb-1">Dashboard Atasan</h2>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Halaman Dashboard</li>
                </ol>
            </div>
        </div>

        <!-- Notifikasi -->
        <div class="alert alert-warning mt-3">
            Anda memiliki <strong><?= htmlspecialchars($pending_pengajuan) ?></strong> pengajuan yang menunggu persetujuan.
        </div>

        <!-- Cards -->
        <div class="row row-sm mt-4">
            <?php
            $cards = [
                ['Total Pengajuan Bawahan', $total_pengajuan],
                ['Menunggu Verifikasi', $pending_pengajuan],
                ['Total SPPD Bawahan', $total_sppd],
                ['Evaluasi Bawahan', $total_evaluasi],
            ];
            foreach ($cards as [$label, $count]) {
                echo "
                <div class='col-lg-3 col-md-6 mb-3'>
                    <div class='card custom-card'>
                        <div class='card-body'>
                            <h5 class='fs-14'>" . htmlspecialchars($label) . "</h5>
                            <h4 class='mb-0'>" . htmlspecialchars((string)$count) . "</h4>
                        </div>
                    </div>
                </div>";
            }
            ?>
        </div>

    </div>
</div>

<?php
include_once LAYOUTS_PATH . '/footer.php';
include_once LAYOUTS_PATH . '/scripts.php';
?>
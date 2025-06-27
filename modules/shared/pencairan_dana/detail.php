<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Detail Pencairan Dana';
$role = $_SESSION['role'] ?? '';
$id_user = $_SESSION['id_user'] ?? 0;

// Akses dibatasi untuk: admin, bendahara, pegawai
$allowed_roles = ['admin', 'bendahara', 'pegawai'];
if (!in_array($role, $allowed_roles)) {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: index.php?msg=invalid&obj=pencairan_dana");
    exit;
}

// Ambil data pencairan dan relasi pengajuan
$stmt = $conn->prepare("
    SELECT pd.*, 
           p.tujuan, p.estimasi_biaya, p.tanggal_berangkat,
           peg.nama AS nama_pegawai, peg.jabatan, peg.nip,
           rb.id AS id_rincian, rb.jumlah_total
    FROM pencairan_dana pd
    JOIN pengajuan_perjalanan p ON pd.id_pengajuan = p.id
    JOIN pegawai peg ON p.id_pegawai = peg.id
    LEFT JOIN rincian_biaya rb ON rb.id_pengajuan = p.id AND rb.status = 'disetujui'
    WHERE pd.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    header("Location: index.php?msg=notfound&obj=pencairan_dana");
    exit;
}

// Detail rincian biaya
$stmtDetail = $conn->prepare("SELECT jenis_biaya, jumlah, satuan, harga_satuan FROM rincian_biaya_detail WHERE id_rincian = ?");
$stmtDetail->bind_param("i", $data['id_rincian']);
$stmtDetail->execute();
$detail = $stmtDetail->get_result();

require_once LAYOUTS_PATH . '/head.php';
require_once LAYOUTS_PATH . '/header.php';
require_once LAYOUTS_PATH . '/topbar.php';
require_once LAYOUTS_PATH . '/sidebar.php';
?>

<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mt-3 mb-0"><?= htmlspecialchars($pageTitle) ?></h4>
            <a href="index.php" class="btn btn-secondary btn-sm mt-4">
                <i class="fa fa-arrow-left"></i>
                Kembali
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-3 fw-bold">Nama Pegawai</div>
                    <div class="col-md-9"><?= htmlspecialchars($data['nama_pegawai']) ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3 fw-bold">Tujuan</div>
                    <div class="col-md-9"><?= htmlspecialchars($data['tujuan']) ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3 fw-bold">Tanggal Berangkat</div>
                    <div class="col-md-9"><?= date('d-m-Y', strtotime($data['tanggal_berangkat'])) ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3 fw-bold">Estimasi Biaya</div>
                    <div class="col-md-9">Rp <?= number_format($data['estimasi_biaya'], 0, ',', '.') ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3 fw-bold">Total Rincian Biaya</div>
                    <div class="col-md-9">Rp <?= number_format($data['jumlah_total'], 0, ',', '.') ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 fw-bold">Detail Rincian</div>
                    <div class="col-md-9">
                        <ul class="mb-0">
                            <?php while ($row = $detail->fetch_assoc()): ?>
                                <li><?= htmlspecialchars($row['jenis_biaya']) ?>: <?= $row['jumlah'] ?> <?= htmlspecialchars($row['satuan']) ?> x Rp<?= number_format($row['harga_satuan'], 0, ',', '.') ?> = Rp<?= number_format($row['jumlah'] * $row['harga_satuan'], 0, ',', '.') ?></li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3 fw-bold">Jumlah Dana Dicairkan</div>
                    <div class="col-md-9">Rp <?= htmlspecialchars($data['jumlah_dana']) ?></div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-3 fw-bold">Tanggal Pencairan</div>
                    <div class="col-md-9"><?= date('d-m-Y', strtotime($data['tanggal_pencairan'])) ?></div>
                </div>
                <div class="text-end">
                    <a href="cetak_pencairan.php?id=<?= $id ?>" class="btn btn-primary" target="_blank">
                        <i class="fa fa-print"></i> Cetak
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
require_once LAYOUTS_PATH . '/footer.php';
require_once LAYOUTS_PATH . '/scripts.php';
?>
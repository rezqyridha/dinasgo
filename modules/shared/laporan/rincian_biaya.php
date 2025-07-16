<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

// ✅ RBAC: Hanya admin & bendahara
$role = $_SESSION['role'] ?? '';
if (!in_array($role, ['admin', 'bendahara'])) {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

// ✅ Filter tanggal
$dari = $_GET['dari'] ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');

// Query data header rincian biaya + relasi pegawai & tujuan
$whereClause = "WHERE DATE(rb.tanggal_rincian) >= ? AND DATE(rb.tanggal_rincian) <= ?";
$stmt = $conn->prepare("
    SELECT rb.*, 
           peg.nama AS nama_pegawai, 
           p.tujuan
    FROM rincian_biaya rb
    JOIN pengajuan_perjalanan p ON rb.id_pengajuan = p.id
    JOIN pegawai peg ON p.id_pegawai = peg.id
    $whereClause
    ORDER BY rb.tanggal_rincian ASC
");
$stmt->bind_param("ss", $dari, $sampai);
$stmt->execute();
$result = $stmt->get_result();

require_once LAYOUTS_PATH . '/head.php';
require_once LAYOUTS_PATH . '/header.php';
require_once LAYOUTS_PATH . '/topbar.php';
require_once LAYOUTS_PATH . '/sidebar.php';
?>

<div class="main-content app-content">
    <div class="container-fluid">
        <h4 class="mt-4 mb-3">📑 Laporan Rincian Biaya Perjalanan Dinas</h4>

        <form method="GET" class="row g-3 align-items-end mb-4">
            <div class="col-md-3">
                <label class="form-label">Dari</label>
                <input type="date" name="dari" class="form-control" value="<?= htmlspecialchars($dari) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Sampai</label>
                <input type="date" name="sampai" class="form-control" value="<?= htmlspecialchars($sampai) ?>">
            </div>
            <div class="col-md-6 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-50">
                    <i class="fa fa-search me-1"></i> Tampilkan
                </button>
                <button type="submit" formaction="cetak_rincian_biaya.php" formtarget="_blank" class="btn btn-danger w-50">
                    <i class="fa fa-print me-1"></i> Cetak PDF
                </button>
            </div>
        </form>

        <div class="card shadow-sm">
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-primary">
                        <tr>
                            <th>No</th>
                            <th>Nomor Rincian</th>
                            <th>Nama Pegawai</th>
                            <th>Tujuan</th>
                            <th>Tanggal</th>
                            <th>Total Biaya</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php $no = 1;
                            while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($row['nomor_rincian']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_pegawai']) ?></td>
                                    <td><?= htmlspecialchars($row['tujuan']) ?></td>
                                    <td><?= date('d-m-Y', strtotime($row['tanggal_rincian'])) ?></td>
                                    <td>Rp <?= number_format($row['jumlah_total'], 0, ',', '.') ?></td>
                                    <td>
                                        <span class="badge bg-<?=
                                                                $row['status'] === 'draft' ? 'secondary' : ($row['status'] === 'diajukan' ? 'warning' : ($row['status'] === 'disetujui' ? 'success' : ($row['status'] === 'ditolak' ? 'danger' : 'info')))
                                                                ?>">
                                            <?= ucfirst($row['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">Data tidak ditemukan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
require_once LAYOUTS_PATH . '/footer.php';
require_once LAYOUTS_PATH . '/scripts.php';
?>
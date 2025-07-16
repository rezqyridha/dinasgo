<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

// ✅ RBAC: hanya admin & bendahara
$role = $_SESSION['role'] ?? '';
if (!in_array($role, ['admin', 'bendahara'])) {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

// ✅ Filter tanggal & status
$dari = $_GET['dari'] ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');
$status = $_GET['status'] ?? '';

// ✅ Query data pencairan dana + relasi pegawai, rincian biaya
$whereClause = "WHERE DATE(pd.tanggal_pencairan) >= ? AND DATE(pd.tanggal_pencairan) <= ?";
$params = [$dari, $sampai];
$types = "ss";

if ($status && $status !== 'semua') {
    $whereClause .= " AND pd.status = ?";
    $params[] = $status;
    $types .= "s";
}

$stmt = $conn->prepare("
    SELECT pd.*, rb.nomor_rincian, peg.nama AS nama_pegawai
    FROM pencairan_dana pd
    JOIN rincian_biaya rb ON pd.id_rincian_biaya = rb.id
    JOIN pengajuan_perjalanan pp ON pd.id_pengajuan = pp.id
    JOIN pegawai peg ON pp.id_pegawai = peg.id
    $whereClause
    ORDER BY pd.tanggal_pencairan ASC
");
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

require_once LAYOUTS_PATH . '/head.php';
require_once LAYOUTS_PATH . '/header.php';
require_once LAYOUTS_PATH . '/topbar.php';
require_once LAYOUTS_PATH . '/sidebar.php';
?>

<div class="main-content app-content">
    <div class="container-fluid">
        <h4 class="mt-4 mb-3">💰 Laporan Pencairan Dana</h4>

        <form method="GET" class="row g-3 align-items-end mb-4">
            <div class="col-md-3">
                <label class="form-label">Dari</label>
                <input type="date" name="dari" class="form-control" value="<?= htmlspecialchars($dari) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Sampai</label>
                <input type="date" name="sampai" class="form-control" value="<?= htmlspecialchars($sampai) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="semua" <?= $status === 'semua' ? 'selected' : '' ?>>Semua</option>
                    <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="dicairkan" <?= $status === 'dicairkan' ? 'selected' : '' ?>>Dicairkan</option>
                    <option value="selesai" <?= $status === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-50">
                    <i class="fa fa-search me-1"></i> Tampilkan
                </button>
                <button type="submit" formaction="cetak_pencairan_dana.php" formtarget="_blank" class="btn btn-danger w-50">
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
                            <th>Nomor Pencairan</th>
                            <th>Tanggal Cair</th>
                            <th>Nomor Rincian</th>
                            <th>Nama Pegawai</th>
                            <th>Jumlah Dana</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php $no = 1;
                            while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($row['id']) ?>/<?= date('Y', strtotime($row['tanggal_pencairan'])) ?></td>
                                    <td><?= date('d-m-Y', strtotime($row['tanggal_pencairan'])) ?></td>
                                    <td><?= htmlspecialchars($row['nomor_rincian']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_pegawai']) ?></td>
                                    <td>Rp <?= htmlspecialchars($row['jumlah_dana']) ?></td>
                                    <td>
                                        <span class="badge bg-<?=
                                                                $row['status'] === 'draft' ? 'secondary' : ($row['status'] === 'dicairkan' ? 'info' : 'success') ?>">
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
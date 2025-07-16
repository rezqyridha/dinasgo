<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/dinasgo/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$allowed_roles = ['admin', 'atasan'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: ../../unauthorized.php");
    exit;
}

// --- Filter default
$dari = $_GET['dari'] ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');
$status = $_GET['status'] ?? '';

$where = [];

// Filter tanggal
if (!empty($dari) && !empty($sampai)) {
    $where[] = "pp.tanggal_berangkat >= '$dari' AND pp.tanggal_berangkat <= '$sampai'";
}

// Filter status
if ($status !== '' && $status !== 'Semua') {
    $where[] = "pp.status = '$status'";
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$query = "
    SELECT pp.*, peg.nama AS nama_pegawai, peg.nip, peg.jabatan, spt.nomor_spt, sppd.nomor_sppd, rb.jumlah_total
    FROM pengajuan_perjalanan pp
    JOIN pegawai peg ON pp.id_pegawai = peg.id
    LEFT JOIN spt ON spt.id_pengajuan = pp.id
    LEFT JOIN sppd ON sppd.id_pengajuan = pp.id
    LEFT JOIN rincian_biaya rb ON rb.id_pengajuan = pp.id
    $whereClause
    ORDER BY pp.tanggal_berangkat DESC
";

$result = $conn->query($query);

require_once LAYOUTS_PATH . '/head.php';
require_once LAYOUTS_PATH . '/header.php';
require_once LAYOUTS_PATH . '/topbar.php';
require_once LAYOUTS_PATH . '/sidebar.php';
?>

<div class="main-content app-content">
    <div class="container-fluid">
        <h3 class="mt-4 mb-3">📋 Laporan Perjalanan Dinas</h3>

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
                    <option value="Semua">Semua</option>
                    <option value="disetujui" <?= $status === 'disetujui' ? 'selected' : '' ?>>Disetujui</option>
                    <option value="selesai" <?= $status === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-50">
                    <i class="fa fa-search me-1"></i> Tampilkan
                </button>
                <button type="submit" class="btn btn-danger w-50"
                    formaction="cetak_perjalanan_dinas.php" formtarget="_blank">
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
                            <th>Nama Pegawai</th>
                            <th>NIP</th>
                            <th>Jabatan</th>
                            <th>Tujuan</th>
                            <th>Tgl Berangkat</th>
                            <th>Tgl Kembali</th>
                            <th>Nomor SPT</th>
                            <th>Nomor SPPD</th>
                            <th>Total Biaya</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php $no = 1;
                            while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($row['nama_pegawai']) ?></td>
                                    <td><?= htmlspecialchars($row['nip']) ?></td>
                                    <td><?= htmlspecialchars($row['jabatan']) ?></td>
                                    <td><?= htmlspecialchars($row['tujuan']) ?></td>
                                    <td><?= date('d-m-Y', strtotime($row['tanggal_berangkat'])) ?></td>
                                    <td><?= date('d-m-Y', strtotime($row['tanggal_kembali'])) ?></td>
                                    <td><?= htmlspecialchars($row['nomor_spt'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['nomor_sppd'] ?? '-') ?></td>
                                    <td>
                                        Rp <?= number_format(floatval($row['jumlah_total']), 0, ',', '.') ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $row['status'] === 'disetujui' ? 'success' : ($row['status'] === 'selesai' ? 'primary' : 'secondary') ?>">
                                            <?= ucfirst($row['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" class="text-center text-muted">Data tidak ditemukan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<?php require_once LAYOUTS_PATH . '/footer.php'; ?>
<?php require_once LAYOUTS_PATH . '/scripts.php'; ?>
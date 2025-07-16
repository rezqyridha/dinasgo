<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

//  RBAC: Hanya admin & atasan
$role = $_SESSION['role'] ?? '';
if (!in_array($role, ['admin', 'atasan'])) {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

//  Filter status
$status = $_GET['status'] ?? 'semua';

$whereClause = "";
$params = [];
$types = "";

if ($status && $status !== 'semua') {
    $whereClause = "WHERE ev.status = ?";
    $params[] = $status;
    $types .= "s";
}

$sql = "
    SELECT ev.*, peg.nama AS nama_pegawai
    FROM evaluasi_perjalanan ev
    JOIN pegawai peg ON ev.id_pegawai = peg.id
    $whereClause
    ORDER BY ev.id ASC
";

$stmt = $conn->prepare($sql);

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

//  Layout
require_once LAYOUTS_PATH . '/head.php';
require_once LAYOUTS_PATH . '/header.php';
require_once LAYOUTS_PATH . '/topbar.php';
require_once LAYOUTS_PATH . '/sidebar.php';
?>

<div class="main-content app-content">
    <div class="container-fluid">
        <h4 class="mt-4 mb-3">📑 Laporan Evaluasi Perjalanan Dinas</h4>

        <form method="GET" class="row g-3 align-items-end mb-4">
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="semua" <?= $status === 'semua' ? 'selected' : '' ?>>Semua</option>
                    <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="diajukan" <?= $status === 'diajukan' ? 'selected' : '' ?>>Diajukan</option>
                    <option value="disetujui" <?= $status === 'disetujui' ? 'selected' : '' ?>>Disetujui</option>
                    <option value="ditolak" <?= $status === 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                    <option value="selesai" <?= $status === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                </select>
            </div>
            <div class="col-md-8 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-50">
                    <i class="fa fa-search me-1"></i> Tampilkan
                </button>
                <a href="<?= BASE_URL ?>/modules/shared/laporan/cetak_evaluasi.php?status=<?= $status ?>"
                    target="_blank"
                    class="btn btn-danger w-50">
                    <i class="fa fa-print me-1"></i> Cetak PDF
                </a>
            </div>
        </form>

        <div class="card shadow-sm">
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-primary">
                        <tr>
                            <th>No</th>
                            <th>Nama Pegawai</th>
                            <th>Hasil</th>
                            <th>Kendala</th>
                            <th>Saran</th>
                            <th>Lampiran</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php $no = 1;
                            while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($row['nama_pegawai']) ?></td>
                                    <td><?= nl2br(htmlspecialchars($row['hasil'])) ?></td>
                                    <td><?= nl2br(htmlspecialchars($row['kendala'])) ?></td>
                                    <td><?= nl2br(htmlspecialchars($row['saran'])) ?></td>
                                    <td>
                                        <?php if ($row['lampiran']): ?>
                                            <a href="<?= BASE_URL ?>/uploads/evaluasi/<?= htmlspecialchars($row['lampiran']) ?>" target="_blank">Lihat File</a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
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
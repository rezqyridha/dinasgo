<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Evaluasi Perjalanan Dinas';
$role = $_SESSION['role'] ?? '';
$id_user = $_SESSION['id_user'] ?? 0;

// Proteksi role
$canRead = in_array($role, ['pegawai', 'admin', 'atasan']);
if (!$canRead) {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

// Query data
$query = "
    SELECT ep.*, pp.tujuan, pp.tanggal_berangkat, u.nama AS nama_pegawai 
    FROM evaluasi_perjalanan ep
    JOIN pengajuan_perjalanan pp ON ep.id_pengajuan = pp.id
    JOIN user u ON ep.id_pegawai = u.id
";

if ($role === 'pegawai') {
    $query .= " WHERE ep.id_pegawai = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $query .= " ORDER BY ep.id DESC";
    $result = $conn->query($query);
}

require_once LAYOUTS_PATH . '/head.php';
require_once LAYOUTS_PATH . '/header.php';
require_once LAYOUTS_PATH . '/topbar.php';
require_once LAYOUTS_PATH . '/sidebar.php';
?>

<div class="main-content app-content">
    <div class="container-fluid">
        <div class="card custom-card mt-4 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><?= htmlspecialchars($pageTitle) ?></h5>
                <?php if ($role === 'pegawai'): ?>
                    <a href="<?= BASE_URL ?>/modules/pegawai/evaluasi/add.php" class="btn btn-sm btn-primary">
                        <i class="fe fe-plus"></i> Buat Evaluasi
                    </a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Pegawai</th>
                                <th>Tujuan</th>
                                <th>Tanggal Berangkat</th>
                                <th>Kendala</th>
                                <th>Hasil</th>
                                <th>Saran</th>
                                <th>Status</th>
                                <th>Lampiran</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php $no = 1;
                                while ($row = $result->fetch_assoc()): ?>
                                    <?php
                                    $badge = match ($row['status']) {
                                        'draft' => 'secondary',
                                        'diajukan' => 'warning',
                                        'disetujui' => 'success',
                                        'ditolak' => 'danger',
                                        'selesai' => 'primary',
                                        default => 'light'
                                    };
                                    ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['nama_pegawai']) ?></td>
                                        <td><?= htmlspecialchars($row['tujuan']) ?></td>
                                        <td><?= date('d-m-Y', strtotime($row['tanggal_berangkat'])) ?></td>
                                        <td><?= htmlspecialchars($row['kendala']) ?></td>
                                        <td><?= htmlspecialchars($row['hasil']) ?></td>
                                        <td><?= htmlspecialchars($row['saran']) ?></td>
                                        <td><span class="badge bg-<?= $badge ?>"><?= ucfirst($row['status']) ?></span></td>
                                        <td>
                                            <?php if ($row['lampiran']): ?>
                                                <a href="<?= BASE_URL ?>/uploads/evaluasi/<?= htmlspecialchars($row['lampiran']) ?>" target="_blank">Unduh</a>
                                            <?php else: ?>
                                                <i class="text-muted">-</i>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-list d-flex justify-content-center">
                                                <?php if ($role === 'pegawai' && $row['status'] === 'draft'): ?>
                                                    <a href="<?= BASE_URL ?>/modules/pegawai/evaluasi/edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning me-1">
                                                        <i class="fe fe-edit"></i>
                                                    </a>
                                                    <button onclick="confirmDelete('<?= BASE_URL ?>/modules/pegawai/evaluasi/delete.php?id=<?= $row['id'] ?>')" class="btn btn-sm btn-danger">
                                                        <i class="fe fe-trash-2"></i>
                                                    </button>
                                                <?php endif; ?>

                                                <?php if (in_array($role, ['admin', 'atasan'])): ?>
                                                    <a href="<?= BASE_URL ?>/modules/admin/evaluasi/edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning me-1">
                                                        <i class="fe fe-edit"></i>
                                                    </a>
                                                    <button onclick="confirmDelete('<?= BASE_URL ?>/modules/admin/evaluasi/delete.php?id=<?= $row['id'] ?>')" class="btn btn-sm btn-danger">
                                                        <i class="fe fe-trash-2"></i>
                                                    </button>
                                                <?php endif; ?>

                                                <?php if (in_array($row['status'], ['disetujui', 'selesai'])): ?>
                                                    <a href="<?= BASE_URL ?>/modules/shared/evaluasi/cetak.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-sm btn-info">
                                                        <i class="fe fe-printer"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted">Belum ada data evaluasi.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once LAYOUTS_PATH . '/footer.php';
require_once LAYOUTS_PATH . '/scripts.php';
?>

</script>
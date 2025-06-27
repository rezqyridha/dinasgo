<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Daftar Rincian Biaya Perjalanan';
$role = $_SESSION['role'];
$id_user = $_SESSION['id_user'];

$canRead = in_array($role, ['admin', 'pegawai', 'bendahara', 'atasan']);
if (!$canRead) {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

// Ambil data rincian berdasarkan role
$query = "
    SELECT rb.*, u.nama AS pembuat, p.tujuan
    FROM rincian_biaya rb
    JOIN pengajuan_perjalanan p ON rb.id_pengajuan = p.id
    JOIN user u ON rb.dibuat_oleh = u.id
";

if ($role === 'pegawai') {
    $query .= " WHERE rb.dibuat_oleh = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $query .= " ORDER BY rb.tanggal_rincian DESC";
    $result = $conn->query($query);
}

require_once LAYOUTS_PATH . '/head.php';
require_once LAYOUTS_PATH . '/header.php';
require_once LAYOUTS_PATH . '/topbar.php';
require_once LAYOUTS_PATH . '/sidebar.php';
?>

<div class="main-content app-content">
    <div class="container-fluid">
        <div class="card custom-card mt-5 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title mb-0"><?= htmlspecialchars($pageTitle) ?></div>
                <?php if ($role === 'admin'): ?>
                    <a href="<?= BASE_URL ?>/modules/admin/rincian_biaya/add.php" class="btn btn-sm btn-primary">
                        <i class="fe fe-plus me-1"></i> Tambah Rincian
                    </a>
                <?php endif; ?>
            </div>

            <div class="card-body">
                <div class="mb-3 d-flex justify-content-end">
                    <input type="text" id="searchBox" class="form-control w-25" placeholder="Cari Rincian...">
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle" id="tabel-rincian">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nomor</th>
                                <th>Tanggal</th>
                                <th>Tujuan</th>
                                <th>Pembuat</th>
                                <th>Jumlah Total</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php $no = 1;
                                while ($row = $result->fetch_assoc()): ?>
                                    <?php
                                    $badge = match ($row['status']) {
                                        'draft' => 'bg-secondary',
                                        'diajukan' => 'bg-warning ',
                                        'disetujui' => 'bg-success',
                                        'ditolak' => 'bg-danger',
                                        'selesai' => 'bg-primary',
                                        default => 'bg-light'
                                    };
                                    ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['nomor_rincian']) ?></td>
                                        <td><?= date('d-m-Y', strtotime($row['tanggal_rincian'])) ?></td>
                                        <td><?= htmlspecialchars($row['tujuan']) ?></td>
                                        <td><?= htmlspecialchars($row['pembuat']) ?></td>
                                        <td>Rp <?= number_format($row['jumlah_total'], 0, ',', '.') ?></td>
                                        <td><span class="badge <?= $badge ?>"><?= ucfirst($row['status']) ?></span></td>
                                        <td class="text-center">
                                            <div class="btn-list d-flex justify-content-center">
                                                <a href="<?= BASE_URL ?>/modules/shared/rincian_biaya/detail.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info me-1" title="Lihat Detail">
                                                    <i class="fe fe-eye"></i>
                                                </a>

                                                <?php if ($role === 'admin' && $row['status'] === 'draft'): ?>
                                                    <a href="<?= BASE_URL ?>/modules/admin/rincian_biaya/edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning me-1" title="Edit Rincian">
                                                        <i class="fe fe-edit"></i>
                                                    </a>
                                                    <a href="<?= BASE_URL ?>/modules/admin/rincian_biaya/ajukan.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-success me-1" title="Ajukan Rincian">
                                                        <i class="fe fe-send"></i>
                                                    </a>
                                                    <button onclick="confirmDelete('<?= BASE_URL ?>/modules/admin/rincian_biaya/delete.php?id=<?= $row['id'] ?>')" class="btn btn-sm btn-danger" title="Hapus Rincian">
                                                        <i class="fe fe-trash-2"></i>
                                                    </button>
                                                <?php endif; ?>

                                                <?php if ($role === 'bendahara' && $row['status'] === 'diajukan'): ?>
                                                    <a href="<?= BASE_URL ?>/modules/bendahara/rincian_biaya/verifikasi.php?id=<?= $row['id'] ?>&action=setujui" class="btn btn-sm btn-success me-1" title="Setujui">
                                                        <i class="fe fe-check"></i>
                                                    </a>
                                                    <a href="<?= BASE_URL ?>/modules/bendahara/rincian_biaya/verifikasi.php?id=<?= $row['id'] ?>&action=tolak" class="btn btn-sm btn-danger" title="Tolak">
                                                        <i class="fe fe-x"></i>
                                                    </a>
                                                <?php endif; ?>

                                                <?php if (in_array($role, ['admin', 'bendahara']) && in_array($row['status'], ['disetujui', 'selesai'])): ?>
                                                    <a href="<?= BASE_URL ?>/modules/shared/rincian_biaya/cetak_rincian.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-sm btn-dark me-1" title="Cetak PDF">
                                                        <i class="fe fe-printer"></i>
                                                    </a>
                                                <?php endif; ?>


                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Belum ada data rincian biaya.</td>
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

<script>
    document.getElementById("searchBox").addEventListener("keyup", function() {
        const filter = this.value.toLowerCase();
        document.querySelectorAll("#tabel-rincian tbody tr").forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
        });
    });
</script>
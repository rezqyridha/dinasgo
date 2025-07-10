<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Daftar Pencairan Dana';
$role = $_SESSION['role'];
$id_user = $_SESSION['id_user'];

// Proteksi role
$canRead = in_array($role, ['admin', 'pegawai', 'bendahara']);
if (!$canRead) {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

// Ambil data sesuai role
if ($role === 'pegawai') {
    $stmt = $conn->prepare("SELECT id FROM pegawai WHERE id_user = ?");
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $stmt->bind_result($id_pegawai);
    $stmt->fetch();
    $stmt->close();
    $id_pegawai = $id_pegawai ?? 0;

    $query = "
        SELECT pd.*, peg.nama AS nama_pegawai, u.nama AS nama_bendahara, admin.nama AS nama_admin_finalisasi
        FROM pencairan_dana pd
        JOIN pengajuan_perjalanan pp ON pd.id_pengajuan = pp.id
        JOIN pegawai peg ON pp.id_pegawai = peg.id
        LEFT JOIN user u ON pd.id_bendahara = u.id
        LEFT JOIN user admin ON pd.id_admin_finalisasi = admin.id
        WHERE pp.id_pegawai = ?
        ORDER BY pd.tanggal_pencairan DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_pegawai);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $query = "
        SELECT pd.*, peg.nama AS nama_pegawai, u.nama AS nama_bendahara, admin.nama AS nama_admin_finalisasi
        FROM pencairan_dana pd
        JOIN pengajuan_perjalanan pp ON pd.id_pengajuan = pp.id
        JOIN pegawai peg ON pp.id_pegawai = peg.id
        LEFT JOIN user u ON pd.id_bendahara = u.id
        LEFT JOIN user admin ON pd.id_admin_finalisasi = admin.id
        ORDER BY pd.tanggal_pencairan DESC";
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
                <?php if ($role === 'bendahara'): ?>
                    <a href="<?= BASE_URL ?>/modules/bendahara/pencairan_dana/add.php" class="btn btn-sm btn-primary">
                        <i class="fe fe-plus me-1"></i> Tambah Pencairan
                    </a>
                <?php endif; ?>
            </div>

            <div class="card-body">
                <div class="mb-3 d-flex justify-content-end">
                    <input type="text" id="searchBox" class="form-control w-25" placeholder="Cari pencairan...">
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle mb-0" id="tabel-pencairan">
                        <thead class="table-primary">
                            <tr>
                                <th>No</th>
                                <th>Nama Pegawai</th>
                                <th>Jumlah Dana</th>
                                <th>Tgl Cair</th>
                                <th>Status</th>
                                <th>Admin Finalisasi</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php $no = 1;
                                while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['nama_pegawai']) ?></td>
                                        <td>Rp <?= htmlspecialchars($row['jumlah_dana']) ?></td>
                                        <td><?= date('d-m-Y', strtotime($row['tanggal_pencairan'])) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $row['status'] === 'draft' ? 'warning' : ($row['status'] === 'dicairkan' ? 'info' : 'success') ?>">
                                                <?= ucfirst($row['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($row['nama_admin_finalisasi'] ?? '-') ?></td>
                                        <td class="text-center">
                                            <div class="btn-list d-flex justify-content-center">
                                                <a href="detail.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info me-1" title="Lihat Detail">
                                                    <i class="fe fe-eye"></i>
                                                </a>

                                                <?php if (in_array($role, ['admin', 'bendahara']) && $row['status'] === 'selesai'): ?>
                                                    <a href="cetak_pencairan.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-sm btn-primary me-1" title="Cetak Bukti">
                                                        <i class="fe fe-printer"></i>
                                                    </a>
                                                <?php endif; ?>

                                                <?php if ($row['status'] === 'draft' && $role === 'bendahara'): ?>
                                                    <button class="btn btn-sm btn-success me-1" data-bs-toggle="modal" data-bs-target="#modalCairkan<?= $row['id'] ?>" title="Verifikasi Cairkan">
                                                        <i class="fe fe-check"></i>
                                                    </button>
                                                <?php elseif ($row['status'] === 'dicairkan' && $role === 'admin'): ?>
                                                    <button class="btn btn-sm btn-primary me-1" data-bs-toggle="modal" data-bs-target="#modalFinal<?= $row['id'] ?>" title="Finalisasi">
                                                        <i class="fe fe-flag"></i>
                                                    </button>
                                                <?php endif; ?>

                                                <?php if ($role === 'bendahara' && $row['status'] === 'draft'): ?>
                                                    <a href="<?= BASE_URL ?>/modules/bendahara/pencairan_dana/edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning me-1" title="Edit">
                                                        <i class="fe fe-edit"></i>
                                                    </a>
                                                    <button onclick="confirmDelete('<?= BASE_URL ?>/modules/bendahara/pencairan_dana/delete.php?id=<?= $row['id'] ?>')" class="btn btn-sm btn-danger" title="Hapus">
                                                        <i class="fe fe-trash-2"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Modal Cairkan Dana untuk Bendahara -->
                                    <?php if ($row['status'] === 'draft' && $role === 'bendahara'): ?>
                                        <div class="modal fade" id="modalCairkan<?= $row['id'] ?>" tabindex="-1" aria-labelledby="modalCairkanLabel<?= $row['id'] ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-md modal-dialog-top">
                                                <div class="modal-content">
                                                    <form action="verifikasi.php" method="POST">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Verifikasi Pencairan Dana</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                            <div class="mb-3">
                                                                <label class="form-label">Tanggal Cair Real</label>
                                                                <input type="date" name="tanggal_pencairan" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" name="aksi" value="cairkan" class="btn btn-success">Cairkan Dana</button>
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                    <?php endif; ?>

                                    <!-- Modal Finalisasi untuk Admin -->
                                    <?php if ($row['status'] === 'dicairkan' && $role === 'admin'): ?>
                                        <div class="modal fade" id="modalFinal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="modalFinalLabel<?= $row['id'] ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-md modal-dialog-top">
                                                <div class="modal-content">
                                                    <form action="verifikasi.php" method="POST">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Finalisasi Pencairan Dana</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                            <div class="mb-3">
                                                                <label class="form-label">Tanggal Finalisasi</label>
                                                                <input type="date" name="tanggal_finalisasi" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" name="aksi" value="finalisasi" class="btn btn-primary">
                                                                Finalisasi
                                                            </button>
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Belum ada data pencairan dana.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once LAYOUTS_PATH . '/footer.php'; ?>
<?php require_once LAYOUTS_PATH . '/scripts.php'; ?>

<script>
    document.getElementById("searchBox").addEventListener("keyup", function() {
        const filter = this.value.toLowerCase();
        document.querySelectorAll("#tabel-pencairan tbody tr").forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
        });
    });
</script>
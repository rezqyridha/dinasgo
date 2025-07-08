<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Daftar Surat Perintah Perjalanan Dinas (SPPD)';
$role = $_SESSION['role'];
$id_user = $_SESSION['id_user'];

// Proteksi RBAC
if (!in_array($role, ['admin', 'atasan', 'pegawai'])) {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

// Ambil data sesuai role
if ($role === 'pegawai') {
    $stmt = $conn->prepare("
        SELECT sppd.*, peg.nama AS nama_pegawai, pp.tujuan, pp.tanggal_berangkat
        FROM sppd
        JOIN pengajuan_perjalanan pp ON sppd.id_pengajuan = pp.id
        JOIN pegawai peg ON pp.id_pegawai = peg.id
        WHERE peg.id_user = ?
        ORDER BY sppd.tanggal_terbit DESC
    ");
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $query = "
        SELECT sppd.*, peg.nama AS nama_pegawai, pp.tujuan, pp.tanggal_berangkat
        FROM sppd
        JOIN pengajuan_perjalanan pp ON sppd.id_pengajuan = pp.id
        JOIN pegawai peg ON pp.id_pegawai = peg.id
        ORDER BY sppd.tanggal_terbit DESC
    ";
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
                    <a href="<?= BASE_URL ?>/modules/admin/sppd/add.php" class="btn btn-sm btn-primary">
                        <i class="fe fe-plus me-1"></i> Tambah SPPD
                    </a>
                <?php endif; ?>
            </div>

            <div class="card-body">
                <div class="mb-3 d-flex justify-content-end">
                    <input type="text" id="searchBox" class="form-control w-25" placeholder="Cari SPPD...">
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped align-middle mb-0" id="tabel-sppd">
                        <thead class="table-primary">
                            <tr>
                                <th>No</th>
                                <th>Nomor SPPD</th>
                                <th>Nama Pegawai</th>
                                <th>Tujuan</th>
                                <th>Tanggal Berangkat</th>
                                <th>Tanggal Terbit</th>
                                <th>Catatan</th>
                                <?php if ($role === 'admin'): ?>
                                    <th class="text-center">Aksi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php $no = 1;
                                while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['nomor_sppd']) ?></td>
                                        <td><?= htmlspecialchars($row['nama_pegawai']) ?></td>
                                        <td><?= htmlspecialchars($row['tujuan']) ?></td>
                                        <td><?= date('d-m-Y', strtotime($row['tanggal_berangkat'])) ?></td>
                                        <td><?= date('d-m-Y', strtotime($row['tanggal_terbit'])) ?></td>
                                        <td><?= htmlspecialchars($row['catatan']) ?></td>

                                        <?php if ($role === 'admin'): ?>
                                            <td class="text-center">
                                                <div class="btn-list d-flex justify-content-center">
                                                    <a href="<?= BASE_URL ?>/modules/admin/sppd/detail.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info me-1" title="Detail">
                                                        <i class="fe fe-eye"></i>
                                                    </a>
                                                    <a href="<?= BASE_URL ?>/modules/admin/sppd/cetak_sppd.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary me-1" title="Cetak" target="_blank">
                                                        <i class="fe fe-printer"></i>
                                                    </a>
                                                    <a href="<?= BASE_URL ?>/modules/admin/sppd/edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning me-1" title="Edit">
                                                        <i class="fe fe-edit"></i>
                                                    </a>
                                                    <button onclick="confirmDelete('<?= BASE_URL ?>/modules/admin/sppd/delete.php?id=<?= $row['id'] ?>')" class="btn btn-sm btn-danger" title="Hapus">
                                                        <i class="fe fe-trash-2"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="<?= $role === 'admin' ? '8' : '7' ?>" class="text-center text-muted">Belum ada data SPPD.</td>
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
        document.querySelectorAll("#tabel-sppd tbody tr").forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
        });
    });
</script>
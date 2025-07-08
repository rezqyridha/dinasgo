<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Daftar Surat Perintah Tugas (SPT)';
$role = $_SESSION['role'];
$id_user = $_SESSION['id_user'];

// Proteksi role
$canEditDelete = in_array($role, ['admin']);
if (!$canEditDelete && !in_array($role, ['pegawai', 'atasan'])) {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}
$canRead = in_array($role, ['admin', 'pegawai', 'atasan']);
if (!$canRead) {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

// Ambil data berdasarkan role
if ($role === 'pegawai') {
    $stmt = $conn->prepare("SELECT id FROM pegawai WHERE id_user = ?");
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $stmt->bind_result($id_pegawai);
    $stmt->fetch();
    $stmt->close();

    $id_pegawai = $id_pegawai ?? 0;

    $query = "
        SELECT spt.*, peg.nama AS nama_pegawai, k.nama AS penandatangan
        FROM spt
        JOIN pengajuan_perjalanan pp ON spt.id_pengajuan = pp.id
        JOIN pegawai peg ON pp.id_pegawai = peg.id
        LEFT JOIN kepala k ON spt.ditandatangani_oleh = k.id
        WHERE pp.id_pegawai = ?
        ORDER BY spt.tanggal_spt DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_pegawai);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $query = "
        SELECT spt.*, peg.nama AS nama_pegawai, k.nama AS penandatangan
        FROM spt
        JOIN pengajuan_perjalanan pp ON spt.id_pengajuan = pp.id
        JOIN pegawai peg ON pp.id_pegawai = peg.id
        LEFT JOIN kepala k ON spt.ditandatangani_oleh = k.id
        ORDER BY spt.tanggal_spt DESC";
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
                    <a href="<?= BASE_URL ?>/modules/admin/spt/add.php" class="btn btn-sm btn-primary">
                        <i class="fe fe-plus me-1"></i> Tambah SPT
                    </a>
                <?php endif; ?>
            </div>

            <div class="card-body">
                <div class="mb-3 d-flex justify-content-end">
                    <input type="text" id="searchBox" class="form-control w-25" placeholder="Cari SPT...">
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped align-middle mb-0" id="tabel-spt">
                        <thead class="table-primary">
                            <tr>
                                <th>No</th>
                                <th>Nomor SPT</th>
                                <th>Nama Pegawai</th>
                                <th>Tanggal SPT</th>
                                <th>Maksud Perjalanan</th>
                                <th>Lama</th>
                                <th>Transportasi</th>
                                <th>Status</th>
                                <th>Ditandatangani Oleh</th>
                                <?php if ($role !== 'pegawai'): ?>
                                    <th class="text-center">Aksi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php $no = 1;
                                while ($row = $result->fetch_assoc()):
                                    $status = $row['status'];
                                    $badgeClass = match ($status) {
                                        'draft' => 'bg-secondary',
                                        'ditandatangani' => 'bg-success',
                                        'dibatalkan' => 'bg-danger',
                                        default => 'bg-light'
                                    };
                                ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['nomor_spt']) ?></td>
                                        <td><?= htmlspecialchars($row['nama_pegawai']) ?></td>
                                        <td><?= date('d-m-Y', strtotime($row['tanggal_spt'])) ?></td>
                                        <td><?= htmlspecialchars($row['maksud_perjalanan']) ?></td>
                                        <td><?= htmlspecialchars($row['lama_perjalanan']) ?></td>
                                        <td><?= htmlspecialchars($row['transportasi']) ?></td>
                                        <td><span class="badge <?= $badgeClass ?>"><?= ucfirst($status) ?></span></td>
                                        <td><?= $row['penandatangan'] ?? '<i class="text-muted">-</i>' ?></td>
                                        <?php if ($role !== 'pegawai'): ?>
                                            <td class="text-center">
                                                <div class="btn-list d-flex justify-content-center">
                                                    <?php if ($status !== 'dibatalkan'): ?>
                                                        <a href="<?= BASE_URL ?>/modules/admin/spt/cetak.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info me-1" title="Cetak" target="_blank">
                                                            <i class="fe fe-printer"></i>
                                                        </a>
                                                    <?php endif; ?>

                                                    <?php if ($canEditDelete && $status === 'draft'): ?>
                                                        <a href="<?= BASE_URL ?>/modules/admin/spt/edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning me-1" title="Edit">
                                                            <i class="fe fe-edit"></i>
                                                        </a>
                                                        <button onclick="confirmDelete('<?= BASE_URL ?>/modules/admin/spt/delete.php?id=<?= $row['id'] ?>')" class="btn btn-sm btn-danger" title="Hapus">
                                                            <i class="fe fe-trash-2"></i>
                                                        </button>
                                                    <?php endif; ?>

                                                    <?php if ($status === 'dibatalkan'): ?>
                                                        <!-- <span class="badge bg-secondary">Final</span>-->
                                                        <!-- Atau jika mau tanda strip saja: -->
                                                        <span>-</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>

                                        <?php endif; ?>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="<?= $role === 'pegawai' ? '9' : '10' ?>" class="text-center text-muted">Belum ada data SPT.</td>
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
        document.querySelectorAll("#tabel-spt tbody tr").forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
        });
    });
</script>
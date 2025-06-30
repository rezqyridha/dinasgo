<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Pengajuan Perjalanan Dinas';
$role = $_SESSION['role'];
$id_user = $_SESSION['id_user'];

// Validasi role
$canRead = in_array($role, ['admin', 'pegawai', 'atasan']);
if (!$canRead) {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

// Ambil data pengajuan
if ($role === 'pegawai') {
    $stmt = $conn->prepare("SELECT id FROM pegawai WHERE id_user = ?");
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $stmt->bind_result($id_pegawai);
    $stmt->fetch();
    $stmt->close();

    $id_pegawai = $id_pegawai ?? 0;

    $query = "
        SELECT p.*, peg.nama AS nama_pegawai
        FROM pengajuan_perjalanan p
        JOIN pegawai peg ON p.id_pegawai = peg.id
        WHERE p.id_pegawai = $id_pegawai
        ORDER BY p.created_at DESC";
} else {
    $query = "
    SELECT p.*, peg.nama AS nama_pegawai
    FROM pengajuan_perjalanan p
    JOIN pegawai peg ON p.id_pegawai = peg.id
    ORDER BY p.created_at DESC
";
}
$result = $conn->query($query);

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
                <?php if ($role === 'pegawai'): ?>
                    <a href="add.php" class="btn btn-sm btn-primary"><i class="fe fe-plus me-1"></i> Tambah Pengajuan</a>
                <?php endif; ?>
            </div>

            <div class="card-body">
                <div class="mb-3 d-flex justify-content-end">
                    <input type="text" id="searchBox" class="form-control w-25" placeholder="Cari pengajuan...">
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped align-middle mb-0" id="tabel-pengajuan">
                        <thead class="table-primary">
                            <tr>
                                <th>No</th>
                                <th>Nama Pegawai</th>
                                <th>Tujuan</th>
                                <th>Keperluan</th>
                                <th>Berangkat</th>
                                <th>Kembali</th>
                                <th>Tgl Pengajuan</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php $no = 1;
                                while ($row = $result->fetch_assoc()):
                                    $status = $row['status'];
                                    $badgeClass = match ($status) {
                                        'diajukan' => 'bg-warning',
                                        'disetujui' => 'bg-success',
                                        'ditolak' => 'bg-danger',
                                        'selesai' => 'bg-secondary',
                                        default => 'bg-light'
                                    };
                                ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['nama_pegawai']) ?></td>
                                        <td><?= htmlspecialchars($row['tujuan']) ?></td>
                                        <td><?= htmlspecialchars($row['keperluan']) ?></td>
                                        <td><?= date('d-m-Y', strtotime($row['tanggal_berangkat'])) ?></td>
                                        <td><?= date('d-m-Y', strtotime($row['tanggal_kembali'])) ?></td>
                                        <td><?= date('d-m-Y', strtotime($row['created_at'])) ?></td>
                                        <td><span class="badge <?= $badgeClass ?>"><?= ucfirst($status) ?></span></td>
                                        <td class="text-center">
                                            <?php if ($role === 'pegawai' && $status === 'diajukan'): ?>
                                                <div class="btn-list d-flex justify-content-center">
                                                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning me-1" title="Edit">
                                                        <i class="fe fe-edit"></i>
                                                    </a>
                                                    <button onclick="confirmDelete('delete.php?id=<?= $row['id'] ?>')" class="btn btn-sm btn-danger" title="Hapus">
                                                        <i class="fe fe-trash-2"></i>
                                                    </button>
                                                </div>

                                            <?php elseif ($role === 'admin' && $status === 'diajukan'): ?>
                                                <div class="btn-list d-flex justify-content-center">
                                                    <a href="verifikasi.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-success me-1" title="Verifikasi">
                                                        <i class="fe fe-check-circle"></i>
                                                    </a>
                                                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning me-1" title="Edit">
                                                        <i class="fe fe-edit"></i>
                                                    </a>
                                                    <button onclick="confirmDelete('delete.php?id=<?= $row['id'] ?>')" class="btn btn-sm btn-danger" title="Hapus">
                                                        <i class="fe fe-trash-2"></i>
                                                    </button>
                                                </div>

                                            <?php elseif ($role === 'atasan' && $status === 'diajukan'): ?>
                                                <div class="btn-list d-flex justify-content-center">
                                                    <a href="verifikasi.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-success" title="Verifikasi">
                                                        <i class="fe fe-check-circle me-1"></i> Verifikasi
                                                    </a>
                                                </div>

                                            <?php else: ?>
                                                <span class="badge bg-light text-dark border">
                                                    <i class="fe fe-lock me-1"></i> Final
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">Belum ada data pengajuan.</td>
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
        document.querySelectorAll("#tabel-pengajuan tbody tr").forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
        });
    });
</script>
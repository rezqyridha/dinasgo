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
    // Dapatkan id_pegawai dari user login
    $stmt = $conn->prepare("SELECT id FROM pegawai WHERE id_user = ?");
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $stmt->bind_result($id_pegawai);
    $stmt->fetch();
    $stmt->close();

    $id_pegawai = $id_pegawai ?? 0;

    $query = "
        SELECT pd.*, peg.nama AS nama_pegawai, u.nama AS nama_bendahara
        FROM pencairan_dana pd
        JOIN pengajuan_perjalanan pp ON pd.id_pengajuan = pp.id
        JOIN pegawai peg ON pp.id_pegawai = peg.id
        LEFT JOIN user u ON pd.id_bendahara = u.id
        WHERE pp.id_pegawai = ?
        ORDER BY pd.tanggal_pencairan DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_pegawai);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Admin dan bendahara melihat semua data
    $query = "
        SELECT pd.*, peg.nama AS nama_pegawai, u.nama AS nama_bendahara
        FROM pencairan_dana pd
        JOIN pengajuan_perjalanan pp ON pd.id_pengajuan = pp.id
        JOIN pegawai peg ON pp.id_pegawai = peg.id
        LEFT JOIN user u ON pd.id_bendahara = u.id
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
                                <th>Tanggal Pencairan</th>
                                <th>Bendahara</th>
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
                                        <td><?= htmlspecialchars($row['nama_bendahara'] ?? '-') ?></td>
                                        <td class="text-center">
                                            <div class="btn-list d-flex justify-content-center">
                                                <a href="detail.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info me-1" title="Lihat Detail">
                                                    <i class="fe fe-eye"></i>
                                                </a>
                                                <?php if (in_array($role, ['admin', 'bendahara'])): ?>
                                                    <a href="cetak_pencairan.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-sm btn-primary me-1" title="Cetak Bukti">
                                                        <i class="fe fe-printer"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ($role === 'bendahara'): ?>
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
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Belum ada data pencairan dana.</td>
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
        document.querySelectorAll("#tabel-pencairan tbody tr").forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
        });
    });
</script>
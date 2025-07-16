<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Manajemen Kepala';
$role = $_SESSION['role'] ?? '';

// Hanya admin yang boleh akses
if ($role !== 'admin') {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

// Query semua data kepala
$query = "SELECT * FROM kepala ORDER BY tahun DESC, nama ASC";
$result = $conn->query($query);

// Jika gagal ambil data
if (!$result) {
    die("Gagal mengambil data kepala: " . $conn->error);
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
                <a href="add.php" class="btn btn-sm btn-primary">
                    <i class="fe fe-user-plus me-1"></i> Tambah Kepala
                </a>
            </div>

            <div class="card-body">
                <div class="mb-3 d-flex justify-content-end">
                    <input type="text" id="searchBox" class="form-control w-25" placeholder="Cari kepala...">
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle table-striped" id="tabel-kepala">
                        <thead class="table-primary">
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>NIP</th>
                                <th>Jabatan</th>
                                <th>Tahun</th>
                                <th>Dibuat</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php $no = 1;
                                while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['nama']) ?></td>
                                        <td><?= htmlspecialchars($row['nip']) ?></td>
                                        <td><?= htmlspecialchars($row['jabatan']) ?></td>
                                        <td><?= htmlspecialchars($row['tahun'] ?? '-') ?></td>
                                        <td><?= date('d-m-Y', strtotime($row['created_at'])) ?></td>
                                        <td class="text-center">
                                            <div class="btn-list d-flex justify-content-center">
                                                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning me-1" title="Edit">
                                                    <i class="fe fe-edit"></i>
                                                </a>
                                                <button onclick="confirmDelete('delete.php?id=<?= $row['id'] ?>')" class="btn btn-sm btn-danger" title="Hapus">
                                                    <i class="fe fe-trash-2"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Belum ada data kepala.</td>
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
    // Live Search
    document.getElementById("searchBox").addEventListener("keyup", function() {
        const filter = this.value.toLowerCase();
        document.querySelectorAll("#tabel-kepala tbody tr").forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
        });
    });
</script>
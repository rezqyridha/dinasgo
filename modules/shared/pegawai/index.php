<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once BASE_PATH . '/auth/session.php';
require_once BASE_PATH . '/config/koneksi.php';

$pageTitle = 'Data Pegawai';
$isAdmin = ($_SESSION['role'] === 'admin');

// Query data pegawai
$query = "SELECT * FROM pegawai ORDER BY nama ASC";
$result = $conn->query($query);

// Jika query gagal, tampilkan error
if (!$result) {
    die("Gagal mengambil data pegawai: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="id">
<?php include_once BASE_PATH . '/layouts/head.php'; ?>

<body>
    <div class="page">
        <?php
        include_once BASE_PATH . '/layouts/header.php';
        include_once BASE_PATH . '/layouts/topbar.php';
        include_once BASE_PATH . '/layouts/sidebar.php';
        ?>

        <div class="main-content app-content">
            <div class="container-fluid">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0"><?= htmlspecialchars($pageTitle) ?></h2>
                    <?php if ($isAdmin): ?>
                        <a href="add.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tambah Pegawai
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Tabel Data Pegawai -->
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover text-nowrap">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>NIP</th>
                                        <th>Jabatan</th>
                                        <th>No. HP</th>
                                        <th>Email</th>
                                        <th>Alamat</th>
                                        <?php if ($isAdmin): ?>
                                            <th>Aksi</th>
                                        <?php endif; ?>
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
                                                <td><?= htmlspecialchars($row['no_hp'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($row['email'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($row['alamat'] ?? '-') ?></td>
                                                <?php if ($isAdmin): ?>
                                                    <td>
                                                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                                        <a href="#" onclick="confirmDelete('delete.php?id=<?= $row['id'] ?>')" class="btn btn-danger btn-sm">Hapus</a>
                                                    </td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="<?= $isAdmin ? 8 : 7 ?>" class="text-center">Data pegawai tidak tersedia.</td>
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
        include_once BASE_PATH . '/layouts/footer.php';
        include_once BASE_PATH . '/layouts/scripts.php';
        ?>
    </div>
</body>

</html>
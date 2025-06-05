<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once BASE_PATH . '/auth/session.php';
require_once BASE_PATH . '/config/koneksi.php';

$pageTitle = 'Manajemen User';
$current_page = str_replace(BASE_PATH, '', __FILE__);
$isAdmin = ($_SESSION['role'] === 'admin');

// Cek hak akses
if (!$isAdmin) {
    header("Location: " . BASE_URL . "/modules/{$_SESSION['role']}/dashboard.php?msg=unauthorized");
    exit;
}

// Ambil semua user
$result = $conn->query("SELECT * FROM user ORDER BY role, nama");

function getRoleBadgeClass($role)
{
    switch (strtolower($role)) {
        case 'admin':
            return 'bg-danger';
        case 'pegawai':
            return 'bg-primary';
        case 'atasan':
            return 'bg-info';
        case 'bendahara':
            return 'bg-success';
        default:
            return 'bg-secondary';
    }
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
                    <h2 class="mt-3 mb-0"><?= htmlspecialchars($pageTitle) ?></h2>
                    <a href="add.php" class="btn btn-primary mt-3">
                        <i class="fas fa-user-plus"></i> Tambah User
                    </a>
                </div>

                <!-- Table -->
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover text-nowrap">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result && $result->num_rows > 0): ?>
                                        <?php $no = 1;
                                        while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><?= htmlspecialchars($row['nama']) ?></td>
                                                <td><?= htmlspecialchars($row['username']) ?></td>
                                                <td>
                                                    <span class="badge <?= getRoleBadgeClass($row['role']) ?>">
                                                        <?= htmlspecialchars(ucfirst($row['role'])) ?>
                                                </td>
                                                <td>
                                                    <?php if ($row['status'] === 'aktif'): ?>
                                                        <span class="badge bg-success">Aktif</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Nonaktif</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                                    <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                                        <a href="javascript:void(0);" onclick="confirmDelete('delete.php?id=<?= $row['id'] ?>')" class="btn btn-danger btn-sm">Hapus</a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">Data user tidak tersedia.</td>
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
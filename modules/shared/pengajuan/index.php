<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once BASE_PATH . '/auth/session.php';
require_once BASE_PATH . '/config/koneksi.php';

$pageTitle = 'Data Pengajuan Perjalanan Dinas';
$current_page = basename(__FILE__);
$role = $_SESSION['role'];
$canRead = in_array($role, ['admin', 'atasan', 'bendahara', 'pegawai']);

if (!$canRead) {
    header("Location: " . BASE_URL . "/modules/$role/index.php?msg=unauthorized");
    exit;
}

// Query data pengajuan + nama pegawai
$query = "
    SELECT p.*, u.nama AS nama_pegawai 
    FROM pengajuan_perjalanan p
    JOIN user u ON p.id_pegawai = u.id
    ORDER BY p.created_at DESC
";
$result = $conn->query($query);
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
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0"><?= htmlspecialchars($pageTitle) ?></h2>
                    <?php if ($role === 'pegawai'): ?>
                        <a href="add.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tambah Pengajuan
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Tabel -->
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover text-nowrap">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Pegawai</th>
                                        <th>Tujuan</th>
                                        <th>Keperluan</th>
                                        <th>Berangkat</th>
                                        <th>Kembali</th>
                                        <th>Tgl Pengajuan</th>
                                        <th>Status</th>
                                        <?php if ($role === 'pegawai' || in_array($role, ['admin', 'atasan', 'bendahara'])): ?>
                                            <th>Aksi</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result && $result->num_rows > 0): ?>
                                        <?php $no = 1;
                                        while ($row = $result->fetch_assoc()):
                                            $status = $row['status'];
                                            $badgeClass = match ($status) {
                                                'diajukan' => 'warning',
                                                'disetujui' => 'success',
                                                'ditolak' => 'danger',
                                                'selesai' => 'secondary',
                                                default => 'light'
                                            };
                                        ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><?= htmlspecialchars($row['nama_pegawai']) ?></td>
                                                <td><?= htmlspecialchars($row['tujuan']) ?></td>
                                                <td><?= htmlspecialchars($row['keperluan']) ?></td>
                                                <td><?= htmlspecialchars($row['tgl_berangkat']) ?></td>
                                                <td><?= htmlspecialchars($row['tgl_kembali']) ?></td>
                                                <td><?= htmlspecialchars($row['tanggal_pengajuan']) ?></td>
                                                <td><span class="badge bg-<?= $badgeClass ?>"><?= ucfirst($status) ?></span></td>
                                                <?php if ($role === 'pegawai' && $status === 'diajukan'): ?>
                                                    <td>
                                                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                                        <button onclick="confirmDelete('delete.php?id=<?= $row['id'] ?>')" class="btn btn-sm btn-danger">Hapus</button>
                                                    </td>
                                                <?php elseif (in_array($role, ['admin', 'atasan', 'bendahara'])): ?>
                                                    <td>-</td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center">Data pengajuan tidak tersedia.</td>
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
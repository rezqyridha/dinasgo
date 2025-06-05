<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once BASE_PATH . '/auth/session.php';
require_once BASE_PATH . '/config/koneksi.php';

// echo "Role: " . $_SESSION['role'] . "<br>";
//echo "Folder: $currentFolder | Modul: $currentModule";
//exit;

// Proteksi login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$pageTitle = 'Data Surat Perintah Tugas (SPT)';
$id_user = $_SESSION['user_id'];
$role = $_SESSION['role'];
$isAdmin = ($role === 'admin');

// Query data SPT berdasarkan role
if ($role === 'pegawai') {
    $query = "
        SELECT s.*, p.id_pegawai, u.nama AS pegawai, a.nama AS penandatangan
        FROM spt s
        JOIN pengajuan_perjalanan p ON s.id_pengajuan = p.id
        JOIN user u ON p.id_pegawai = u.id
        LEFT JOIN user a ON s.ditandatangani_oleh = a.id
        WHERE p.id_pegawai = $id_user
        ORDER BY s.tanggal_spt DESC
    ";
} else {
    $query = "
        SELECT s.*, p.id_pegawai, u.nama AS pegawai, a.nama AS penandatangan
        FROM spt s
        JOIN pengajuan_perjalanan p ON s.id_pengajuan = p.id
        JOIN user u ON p.id_pegawai = u.id
        LEFT JOIN user a ON s.ditandatangani_oleh = a.id
        ORDER BY s.tanggal_spt DESC
    ";
}

$result = $conn->query($query);
if (!$result) {
    die("Gagal mengambil data SPT: " . $conn->error);
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
                    <?php if ($isAdmin): ?>
                        <a href="add.php" class="btn btn-primary mt-3">
                            <i class="fas fa-plus"></i> Tambah SPT
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Tabel Data SPT -->
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover text-nowrap">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Nomor SPT</th>
                                        <th>Nama Pegawai</th>
                                        <th>Tanggal SPT</th>
                                        <th>Maksud Perjalanan</th>
                                        <th>Status</th>
                                        <th>Penandatangan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php $no = 1;
                                        while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><?= htmlspecialchars($row['nomor_spt']) ?></td>
                                                <td>
                                                    <?= htmlspecialchars($row['pegawai']) ?>
                                                    <?= ($row['id_pegawai'] == $id_user) ? ' <span class="text-muted">(Milik Anda)</span>' : '' ?>
                                                </td>
                                                <td><?= date('d-m-Y', strtotime($row['tanggal_spt'])) ?></td>
                                                <td><?= htmlspecialchars($row['maksud_perjalanan']) ?></td>
                                                <td>
                                                    <?php
                                                    $badge = match ($row['status']) {
                                                        'draft' => 'secondary',
                                                        'ditandatangani' => 'success',
                                                        'dibatalkan' => 'danger',
                                                        default => 'dark',
                                                    };
                                                    echo "<span class='badge bg-$badge'>" . htmlspecialchars($row['status']) . "</span>";
                                                    ?>
                                                </td>
                                                <td><?= htmlspecialchars($row['penandatangan'] ?? '-') ?></td>
                                                <td>
                                                    <a href="view.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm">Detail</a>
                                                    <?php if ($isAdmin && $row['status'] === 'draft'): ?>
                                                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                                        <a href="#" onclick="confirmDelete('delete.php?id=<?= $row['id'] ?>')" class="btn btn-danger btn-sm">Hapus</a>
                                                    <?php endif; ?>
                                                    <?php if (in_array($role, ['admin', 'pegawai', 'bendahara'])): ?>
                                                        <a href="cetak.php?id=<?= $row['id'] ?>" class="btn btn-success btn-sm" target="_blank">Cetak</a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">Data SPT tidak tersedia.</td>
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
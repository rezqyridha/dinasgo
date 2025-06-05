<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once BASE_PATH . '/auth/session.php';
require_once BASE_PATH . '/config/koneksi.php';

$pageTitle = 'Detail Surat Perintah Tugas (SPT)';
$role = $_SESSION['role'];
$userId = $_SESSION['user_id'] ?? 0;

// Validasi ID
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    echo "<script>alert('ID tidak valid.'); window.location.href='" . BASE_URL . "/modules/shared/spt/index.php';</script>";
    exit;
}

// Ambil data
$query = "
    SELECT s.*, 
           u.nama AS pegawai, 
           u.id AS id_pegawai,
           a.nama AS penandatangan, 
           p.tujuan, 
           p.tanggal_berangkat, 
           p.tanggal_kembali
    FROM spt s
    JOIN pengajuan_perjalanan p ON s.id_pengajuan = p.id
    JOIN user u ON p.id_pegawai = u.id
    LEFT JOIN user a ON s.ditandatangani_oleh = a.id
    WHERE s.id = $id
";
$result = $conn->query($query);
$data = $result->fetch_assoc();

// Cek data ditemukan
if (!$data) {
    echo "<script>alert('Data tidak ditemukan'); window.location.href='" . BASE_URL . "/modules/shared/spt/index.php';</script>";
    exit;
}

// Cek hak akses
$allowedRoles = ['admin', 'pegawai', 'bendahara', 'atasan'];
if (!in_array($role, $allowedRoles)) {
    echo "<script>alert('Akses ditolak.'); window.location.href='" . BASE_URL . "/modules/$role/dashboard.php';</script>";
    exit;
}

// Jika pegawai, hanya boleh melihat miliknya
if ($role === 'pegawai' && $data['id_pegawai'] != $userId) {
    echo "<script>alert('Anda tidak memiliki akses ke data ini.'); window.location.href='" . BASE_URL . "/modules/shared/spt/index.php';</script>";
    exit;
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mt-3 mb-0"><?= htmlspecialchars($pageTitle) ?></h2>
                    <a href="index.php" class="btn btn-sm btn-outline-secondary mt-3">
                        <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                    </a>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title mb-4 text-primary fw-bold">Informasi SPT</h5>
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th width="30%">Nomor SPT</th>
                                    <td><?= htmlspecialchars($data['nomor_spt']) ?></td>
                                </tr>
                                <tr>
                                    <th>Nama Pegawai</th>
                                    <td><?= htmlspecialchars($data['pegawai']) ?><?= ($data['id_pegawai'] == $userId) ? ' <span class="text-muted">(Milik Anda)</span>' : '' ?></td>
                                </tr>
                                <tr>
                                    <th>Tujuan</th>
                                    <td><?= htmlspecialchars($data['tujuan']) ?></td>
                                </tr>
                                <tr>
                                    <th>Tanggal Berangkat</th>
                                    <td><?= date('d-m-Y', strtotime($data['tanggal_berangkat'])) ?></td>
                                </tr>
                                <tr>
                                    <th>Tanggal Kembali</th>
                                    <td><?= date('d-m-Y', strtotime($data['tanggal_kembali'])) ?></td>
                                </tr>
                                <tr>
                                    <th>Tanggal SPT</th>
                                    <td><?= date('d-m-Y', strtotime($data['tanggal_spt'])) ?></td>
                                </tr>
                                <tr>
                                    <th>Lama Perjalanan</th>
                                    <td><?= htmlspecialchars($data['lama_perjalanan']) ?></td>
                                </tr>
                                <tr>
                                    <th>Maksud Perjalanan</th>
                                    <td><?= nl2br(htmlspecialchars($data['maksud_perjalanan'])) ?></td>
                                </tr>
                                <tr>
                                    <th>Transportasi</th>
                                    <td><?= htmlspecialchars($data['transportasi']) ?></td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td><span class="badge bg-<?= match ($data['status']) {
                                                                    'draft' => 'secondary',
                                                                    'ditandatangani' => 'success',
                                                                    'dibatalkan' => 'danger',
                                                                    default => 'dark'
                                                                } ?>"><?= ucfirst($data['status']) ?></span></td>
                                </tr>
                                <tr>
                                    <th>Ditandatangani Oleh</th>
                                    <td><?= $data['penandatangan'] ?? '-' ?></td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="mt-4 d-flex gap-2 flex-wrap">
                            <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>

                            <?php if ($role === 'admin'): ?>
                                <a href="edit.php?id=<?= $data['id'] ?>" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="delete.php?id=<?= $data['id'] ?>" class="btn btn-danger"
                                    onclick="return confirm('Yakin ingin menghapus SPT ini?')">
                                    <i class="fas fa-trash-alt"></i> Hapus
                                </a>
                            <?php endif; ?>

                            <?php if (in_array($role, ['pegawai', 'bendahara', 'admin'])): ?>
                                <a href="cetak.php?id=<?= $data['id'] ?>" class="btn btn-success" target="_blank">
                                    <i class="fas fa-print"></i> Cetak
                                </a>
                            <?php endif; ?>

                            <?php if ($role === 'atasan' && $data['status'] === 'draft'): ?>
                                <a href="tandatangan.php?id=<?= $data['id'] ?>" class="btn btn-primary">
                                    <i class="fas fa-pen-nib"></i> Tanda Tangani
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include_once BASE_PATH . '/layouts/footer.php'; ?>
        <?php include_once BASE_PATH . '/layouts/scripts.php'; ?>
    </div>
</body>

</html>
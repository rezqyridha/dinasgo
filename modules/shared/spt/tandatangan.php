<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once BASE_PATH . '/auth/session.php';
require_once BASE_PATH . '/config/koneksi.php';

$pageTitle = 'Tanda Tangan SPT';
$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];

// Hanya role atasan yang boleh menandatangani
if ($role !== 'atasan') {
    header("Location: " . BASE_URL . "/modules/$role/dashboard.php?msg=unauthorized");
    exit;
}

// Validasi ID SPT
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    echo "<script>alert('ID tidak valid.'); location.href='index.php';</script>";
    exit;
}

// Ambil data SPT
$query = "
    SELECT s.*, u.nama AS pegawai, p.tujuan
    FROM spt s
    JOIN pengajuan_perjalanan p ON s.id_pengajuan = p.id
    JOIN user u ON p.id_pegawai = u.id
    WHERE s.id = $id
";
$result = $conn->query($query);
$data = $result->fetch_assoc();

if (!$data) {
    echo "<script>alert('Data SPT tidak ditemukan.'); location.href='index.php';</script>";
    exit;
}

if ($data['status'] !== 'draft') {
    echo "<script>alert('SPT ini sudah ditandatangani atau dibatalkan.'); location.href='index.php';</script>";
    exit;
}

// Proses jika disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $update = $conn->prepare("UPDATE spt SET status = 'ditandatangani', ditandatangani_oleh = ? WHERE id = ?");
    $update->bind_param("ii", $userId, $id);

    if ($update->execute()) {
        echo "<script>alert('SPT berhasil ditandatangani.'); location.href='index.php';</script>";
        exit;
    } else {
        $error = "Gagal menyimpan tanda tangan: " . $conn->error;
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mt-3 mb-0"><?= htmlspecialchars($pageTitle) ?></h2>
                    <a href="index.php" class="btn btn-secondary mt-3"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="card custom-card">
                    <div class="card-body">
                        <p>Anda akan menandatangani SPT atas nama:</p>
                        <ul>
                            <li><strong>Nama Pegawai:</strong> <?= htmlspecialchars($data['pegawai']) ?></li>
                            <li><strong>Tujuan:</strong> <?= htmlspecialchars($data['tujuan']) ?></li>
                            <li><strong>Nomor SPT:</strong> <?= htmlspecialchars($data['nomor_spt']) ?></li>
                        </ul>

                        <form method="POST">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Tanda Tangani</button>
                            <a href="index.php" class="btn btn-secondary">Batal</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php include_once BASE_PATH . '/layouts/footer.php'; ?>
        <?php include_once BASE_PATH . '/layouts/scripts.php'; ?>
    </div>
</body>

</html>
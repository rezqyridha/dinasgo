<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once BASE_PATH . '/auth/session.php';
require_once BASE_PATH . '/config/koneksi.php';

$pageTitle = 'Edit Pengajuan Perjalanan';
$id = $_GET['id'] ?? null;
$role = $_SESSION['role'];
$isPegawai = $role === 'pegawai';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php?msg=invalid");
    exit;
}


$query = $conn->prepare("SELECT * FROM pengajuan_perjalanan WHERE id = ?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    header("Location: index.php?msg=notfound");
    exit;
}

// Cegah pegawai mengedit jika status bukan diajukan
if ($isPegawai && $data['status'] !== 'diajukan') {
    header("Location: index.php?msg=forbidden");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($isPegawai) {
        $tanggal_berangkat = $_POST['tanggal_berangkat'] ?? '';
        $tanggal_kembali = $_POST['tanggal_kembali'] ?? '';
        $tujuan = trim($_POST['tujuan'] ?? '');
        $keperluan = trim($_POST['keperluan'] ?? '');

        $stmt = $conn->prepare("UPDATE pengajuan_perjalanan SET tanggal_berangkat = ?, tanggal_kembali = ?, tujuan = ?, keperluan = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $tanggal_berangkat, $tanggal_kembali, $tujuan, $keperluan, $id);
        $stmt->execute();
        header("Location: index.php?msg=updated");
        exit;
    } else {
        $status = $_POST['status'] ?? '';
        if (!in_array($status, ['diajukan', 'disetujui', 'ditolak', 'selesai'])) {
            header("Location: index.php?msg=invalid");
            exit;
        }

        $stmt = $conn->prepare("UPDATE pengajuan_perjalanan SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
        header("Location: index.php?msg=updated");
        exit;
    }
}
?>

<!-- Gunakan layout modular untuk form -->
<!DOCTYPE html>
<html lang="id">
<?php include_once BASE_PATH . '/layouts/head.php'; ?>

<body>
    <div class="page">
        <?php include_once BASE_PATH . '/layouts/header.php'; ?>
        <?php include_once BASE_PATH . '/layouts/topbar.php'; ?>
        <?php include_once BASE_PATH . '/layouts/sidebar.php'; ?>

        <div class="main-content app-content">
            <div class="container-fluid">
                <h4>Edit Pengajuan</h4>

                <form method="POST">
                    <?php if ($isPegawai): ?>
                        <div class="mb-3">
                            <label>Tanggal Berangkat</label>
                            <input type="date" class="form-control" name="tanggal_berangkat" value="<?= $data['tanggal_berangkat'] ?>" required>
                        </div>
                        <div class="mb-3">
                            <label>Tanggal Kembali</label>
                            <input type="date" class="form-control" name="tanggal_kembali" value="<?= $data['tanggal_kembali'] ?>" required>
                        </div>
                        <div class="mb-3">
                            <label>Tujuan</label>
                            <input type="text" class="form-control" name="tujuan" value="<?= htmlspecialchars($data['tujuan']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label>Keperluan</label>
                            <textarea name="keperluan" class="form-control" required><?= htmlspecialchars($data['keperluan']) ?></textarea>
                        </div>
                    <?php else: ?>
                        <div class="mb-3">
                            <label>Status</label>
                            <select name="status" class="form-control" required>
                                <?php foreach (['diajukan', 'disetujui', 'ditolak', 'selesai'] as $s): ?>
                                    <option value="<?= $s ?>" <?= $data['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>

        <?php include_once BASE_PATH . '/layouts/footer.php'; ?>
        <?php include_once BASE_PATH . '/layouts/scripts.php'; ?>
    </div>
</body>

</html>
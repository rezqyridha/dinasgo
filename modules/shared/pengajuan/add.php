<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once BASE_PATH . '/auth/session.php';
require_once BASE_PATH . '/config/koneksi.php';

$pageTitle = 'Tambah Pengajuan Perjalanan';

if ($_SESSION['role'] !== 'pegawai') {
    header("Location: index.php?msg=unauthorized");
    exit;
}

$id_pegawai = $_SESSION['user_id'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tujuan = trim($_POST['tujuan']);
    $keperluan = trim($_POST['keperluan']);
    $tgl_berangkat = $_POST['tgl_berangkat'] ?? '';
    $tgl_kembali = $_POST['tgl_kembali'] ?? '';

    if ($tujuan === '' || $keperluan === '' || !$tgl_berangkat || !$tgl_kembali) {
        header("Location: add.php?msg=kosong");
        exit;
    }

    // Cek duplikat pengajuan pada tanggal berangkat yang sama
    $cek = $conn->prepare("SELECT id FROM pengajuan_perjalanan WHERE id_pegawai = ? AND tgl_berangkat = ? AND tujuan = ?");
    $cek->bind_param("iss", $id_pegawai, $tgl_berangkat, $tujuan);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows > 0) {
        header("Location: add.php?msg=duplicate");
        exit;
    }
    $cek->close();

    // Simpan pengajuan
    $stmt = $conn->prepare("INSERT INTO pengajuan_perjalanan (id_pegawai, tujuan, keperluan, tgl_berangkat, tgl_kembali, tanggal_pengajuan, status) VALUES (?, ?, ?, ?, ?, NOW(), 'diajukan')");
    $stmt->bind_param("issss", $id_pegawai, $tujuan, $keperluan, $tgl_berangkat, $tgl_kembali);

    if ($stmt->execute()) {
        header("Location: index.php?msg=added");
        exit;
    } else {
        header("Location: add.php?msg=failed");
        exit;
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
                    <h2 class="mb-0"><?= htmlspecialchars($pageTitle) ?></h2>
                </div>

                <div class="card custom-card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Tujuan</label>
                                <input type="text" name="tujuan" class="form-control" placeholder="Contoh: Jakarta" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Keperluan</label>
                                <textarea name="keperluan" class="form-control" rows="3" placeholder="Contoh: Rapat koordinasi" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tanggal Berangkat</label>
                                <input type="date" name="tgl_berangkat" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tanggal Kembali</label>
                                <input type="date" name="tgl_kembali" class="form-control" required>
                            </div>

                            <button type="submit" class="btn btn-primary">Ajukan</button>
                            <a href="index.php" class="btn btn-secondary">Batal</a>
                        </form>
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
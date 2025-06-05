<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once BASE_PATH . '/auth/session.php';
require_once BASE_PATH . '/config/koneksi.php';

$pageTitle = 'Edit Surat Perintah Tugas (SPT)';

// Role proteksi
if ($_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/modules/" . $_SESSION['role'] . "/dashboard.php?msg=unauthorized");
    exit;
}

// Validasi ID SPT
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$data = $conn->query("SELECT * FROM spt WHERE id = $id")->fetch_assoc();

if (!$data) {
    echo "<script>alert('Data tidak ditemukan'); location.href='" . BASE_URL . "/modules/shared/spt/index.php';</script>";
    exit;
}

if ($data['status'] !== 'draft') {
    echo "<script>alert('SPT tidak dapat diedit karena bukan berstatus draft.'); location.href='" . BASE_URL . "/modules/shared/spt/index.php';</script>";
    exit;
}


$error = '';

// Ambil daftar pengajuan yang valid untuk dipilih (disetujui + pengajuan milik data ini)
$pengajuan = $conn->query("
    SELECT p.id, u.nama, p.tujuan, p.tanggal_berangkat
    FROM pengajuan_perjalanan p
    JOIN user u ON p.id_pegawai = u.id
    WHERE p.status = 'disetujui'
    OR p.id = {$data['id_pengajuan']}
");

// Proses jika disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pengajuan      = $_POST['id_pengajuan'];
    $nomor_spt         = $conn->real_escape_string(trim($_POST['nomor_spt']));
    $tanggal_spt       = $_POST['tanggal_spt'];
    $maksud_perjalanan = $conn->real_escape_string(trim($_POST['maksud_perjalanan']));
    $lama_perjalanan   = $_POST['lama_perjalanan'];
    $transportasi      = $_POST['transportasi'];

    // Validasi: pengajuan harus status disetujui
    $cek = $conn->query("SELECT status FROM pengajuan_perjalanan WHERE id = $id_pengajuan")->fetch_assoc();
    if (!$cek || $cek['status'] !== 'disetujui') {
        $error = "Pengajuan tidak valid atau belum disetujui.";
    }

    // Jika valid
    if (!$error) {
        $stmt = $conn->prepare("UPDATE spt SET 
            id_pengajuan = ?, 
            nomor_spt = ?, 
            tanggal_spt = ?, 
            maksud_perjalanan = ?, 
            lama_perjalanan = ?, 
            transportasi = ?
            WHERE id = ?");
        $stmt->bind_param("isssssi", $id_pengajuan, $nomor_spt, $tanggal_spt, $maksud_perjalanan, $lama_perjalanan, $transportasi, $id);

        if ($stmt->execute()) {
            header("Location: " . BASE_URL . "/modules/shared/spt/index.php?msg=updated");
            exit;
        } else {
            $error = "Gagal menyimpan perubahan: " . $stmt->error;
        }
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
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="card custom-card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="id_pengajuan" class="form-label">Pengajuan</label>
                                <select name="id_pengajuan" id="id_pengajuan" class="form-select" required>
                                    <option value="">-- Pilih Pengajuan --</option>
                                    <?php while ($row = $pengajuan->fetch_assoc()): ?>
                                        <option value="<?= $row['id'] ?>" <?= $row['id'] == $data['id_pengajuan'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($row['nama']) ?> - <?= htmlspecialchars($row['tujuan']) ?>
                                            (<?= date('d-m-Y', strtotime($row['tanggal_berangkat'])) ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="nomor_spt" class="form-label">Nomor SPT</label>
                                <input type="text" name="nomor_spt" id="nomor_spt" class="form-control"
                                    value="<?= htmlspecialchars($data['nomor_spt']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="tanggal_spt" class="form-label">Tanggal SPT</label>
                                <input type="date" name="tanggal_spt" id="tanggal_spt" class="form-control"
                                    value="<?= htmlspecialchars($data['tanggal_spt']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="maksud_perjalanan" class="form-label">Maksud Perjalanan</label>
                                <textarea name="maksud_perjalanan" id="maksud_perjalanan" class="form-control" required
                                    rows="3"><?= htmlspecialchars($data['maksud_perjalanan']) ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="lama_perjalanan" class="form-label">Lama Perjalanan</label>
                                <input type="text" name="lama_perjalanan" id="lama_perjalanan" class="form-control"
                                    value="<?= htmlspecialchars($data['lama_perjalanan']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="transportasi" class="form-label">Transportasi</label>
                                <input type="text" name="transportasi" id="transportasi" class="form-control"
                                    value="<?= htmlspecialchars($data['transportasi']) ?>" required>
                            </div>

                            <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                            <a href="<?= BASE_URL ?>/modules/shared/spt/index.php" class="btn btn-secondary">Batal</a>
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
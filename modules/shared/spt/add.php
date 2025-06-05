<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once BASE_PATH . '/auth/session.php';
require_once BASE_PATH . '/config/koneksi.php';

$pageTitle = 'Tambah Surat Perintah Tugas (SPT)';

// Hanya admin yang boleh akses
if ($_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/modules/" . $_SESSION['role'] . "/dashboard.php?msg=unauthorized");
    exit;
}

$error = '';
$input = [
    'id_pengajuan' => '',
    'nomor_spt' => '',
    'tanggal_spt' => '',
    'maksud_perjalanan' => '',
    'lama_perjalanan' => '',
    'transportasi' => ''
];

// Ambil daftar pengajuan yang sudah disetujui & belum punya SPT
$pengajuan = $conn->query("
    SELECT p.id, u.nama, p.tujuan, p.tanggal_berangkat
    FROM pengajuan_perjalanan p
    JOIN user u ON p.id_pegawai = u.id
    WHERE p.status = 'disetujui'
    AND p.id NOT IN (SELECT id_pengajuan FROM spt)
    ORDER BY p.tanggal_berangkat DESC
");

// Jika disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil & simpan input
    foreach ($input as $key => $_) {
        $input[$key] = isset($_POST[$key]) ? trim($_POST[$key]) : '';
    }

    // Validasi dasar
    if (in_array('', $input)) {
        $error = "Semua field wajib diisi.";
    } else {
        // Simpan ke DB
        $stmt = $conn->prepare("
            INSERT INTO spt (id_pengajuan, nomor_spt, tanggal_spt, maksud_perjalanan, lama_perjalanan, transportasi)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "isssss",
            $input['id_pengajuan'],
            $input['nomor_spt'],
            $input['tanggal_spt'],
            $input['maksud_perjalanan'],
            $input['lama_perjalanan'],
            $input['transportasi']
        );

        if ($stmt->execute()) {
            header("Location: " . BASE_URL . "/modules/shared/spt/index.php?msg=sukses");
            exit;
        } else {
            $error = "Gagal menyimpan data: " . $stmt->error;
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
                                <label for="id_pengajuan" class="form-label">Pengajuan Disetujui</label>
                                <select name="id_pengajuan" id="id_pengajuan" class="form-select" required>
                                    <option value="">-- Pilih Pengajuan --</option>
                                    <?php if ($pengajuan && $pengajuan->num_rows > 0): ?>
                                        <?php while ($row = $pengajuan->fetch_assoc()): ?>
                                            <option value="<?= $row['id'] ?>" <?= $input['id_pengajuan'] == $row['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($row['nama']) ?> - <?= htmlspecialchars($row['tujuan']) ?>
                                                (<?= date('d-m-Y', strtotime($row['tanggal_berangkat'])) ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="nomor_spt" class="form-label">Nomor SPT</label>
                                <input type="text" name="nomor_spt" id="nomor_spt" class="form-control"
                                    value="<?= htmlspecialchars($input['nomor_spt']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="tanggal_spt" class="form-label">Tanggal SPT</label>
                                <input type="date" name="tanggal_spt" id="tanggal_spt" class="form-control"
                                    value="<?= htmlspecialchars($input['tanggal_spt']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="maksud_perjalanan" class="form-label">Maksud Perjalanan</label>
                                <textarea name="maksud_perjalanan" id="maksud_perjalanan" class="form-control"
                                    rows="3" required><?= htmlspecialchars($input['maksud_perjalanan']) ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="lama_perjalanan" class="form-label">Lama Perjalanan</label>
                                <input type="text" name="lama_perjalanan" id="lama_perjalanan" class="form-control"
                                    value="<?= htmlspecialchars($input['lama_perjalanan']) ?>" placeholder="Misal: 3 hari" required>
                            </div>

                            <div class="mb-3">
                                <label for="transportasi" class="form-label">Transportasi</label>
                                <input type="text" name="transportasi" id="transportasi" class="form-control"
                                    value="<?= htmlspecialchars($input['transportasi']) ?>" required>
                            </div>

                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <a href="<?= BASE_URL ?>/modules/shared/spt/index.php" class="btn btn-secondary">Batal</a>
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
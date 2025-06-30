<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Ubah SPPD';
$role = $_SESSION['role'] ?? '';
if ($role !== 'admin') {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

$id = $_GET['id'] ?? 0;
if (!$id) {
    header("Location: " . BASE_URL . "/modules/shared/sppd/index.php?msg=invalid&obj=sppd");
    exit;
}

// Ambil data SPPD
$stmt = $conn->prepare("
    SELECT sppd.*, peg.nama, pp.tujuan, pp.tanggal_berangkat
    FROM sppd
    JOIN pengajuan_perjalanan pp ON sppd.id_pengajuan = pp.id
    JOIN pegawai peg ON pp.id_pegawai = peg.id
    WHERE sppd.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    header("Location: " . BASE_URL . "/modules/shared/sppd/index.php?msg=notfound&obj=sppd");
    exit;
}

$input = [
    'tanggal_terbit' => $data['tanggal_terbit'],
    'catatan' => $data['catatan'],
];
$error = '';

// Jika disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input['tanggal_terbit'] = $_POST['tanggal_terbit'] ?? '';
    $input['catatan'] = trim($_POST['catatan'] ?? '');

    if ($input['tanggal_terbit'] === '') {
        $error = 'Tanggal terbit wajib diisi.';
    } else {
        $stmtUpdate = $conn->prepare("UPDATE sppd SET tanggal_terbit = ?, catatan = ? WHERE id = ?");
        $stmtUpdate->bind_param("ssi", $input['tanggal_terbit'], $input['catatan'], $id);

        if ($stmtUpdate->execute()) {
            header("Location: " . BASE_URL . "/modules/shared/sppd/index.php?msg=updated&obj=sppd");
            exit;
        } else {
            header("Location: " . BASE_URL . "/modules/shared/sppd/index.php?msg=failed&obj=sppd");
            exit;
        }
    }
}

require_once LAYOUTS_PATH . '/head.php';
require_once LAYOUTS_PATH . '/header.php';
require_once LAYOUTS_PATH . '/topbar.php';
require_once LAYOUTS_PATH . '/sidebar.php';
?>

<div class="main-content app-content">
    <div class="container-fluid">
        <h4 class="mb-4"><?= htmlspecialchars($pageTitle) ?></h4>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST">
                    <!-- Pengajuan (readonly) -->
                    <div class="mb-3">
                        <label class="form-label">Pengajuan</label>
                        <input type="text" class="form-control" readonly
                            value="<?= htmlspecialchars($data['nama']) ?> - <?= htmlspecialchars($data['tujuan']) ?> (<?= date('d-m-Y', strtotime($data['tanggal_berangkat'])) ?>)">
                    </div>

                    <!-- Tanggal Terbit -->
                    <div class="mb-3">
                        <label class="form-label">Tanggal Terbit</label>
                        <input type="date" name="tanggal_terbit" class="form-control" required
                            value="<?= htmlspecialchars($input['tanggal_terbit']) ?>">
                    </div>

                    <!-- Nomor SPPD (readonly) -->
                    <div class="mb-3">
                        <label class="form-label">Nomor SPPD</label>
                        <input type="text" class="form-control" readonly value="<?= htmlspecialchars($data['nomor_sppd']) ?>">
                    </div>

                    <!-- Catatan -->
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="catatan" rows="3" class="form-control"><?= htmlspecialchars($input['catatan']) ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-warning">
                            <i class="fe fe-save me-1"></i> Simpan Perubahan
                        </button>
                        <a href="<?= BASE_URL ?>/modules/shared/sppd/index.php" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once LAYOUTS_PATH . '/footer.php';
require_once LAYOUTS_PATH . '/scripts.php';
?>
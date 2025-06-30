<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Edit Dokumen';
$role = $_SESSION['role'] ?? '';

if ($role !== 'admin') {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: " . BASE_URL . "/modules/shared/dokumen/index.php?msg=invalid&obj=dokumen");
    exit;
}

// ✅ Ambil data lama
$stmt = $conn->prepare("
  SELECT d.*, pp.tujuan, pp.tanggal_berangkat, peg.nama AS nama_pegawai
  FROM dokumen d
  JOIN pengajuan_perjalanan pp ON d.id_pengajuan = pp.id
  JOIN pegawai peg ON pp.id_pegawai = peg.id
  WHERE d.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    header("Location: " . BASE_URL . "/modules/shared/dokumen/index.php?msg=notfound&obj=dokumen");
    exit;
}

$error = '';
$input = [
    'jenis' => $data['jenis'],
];

// === Proses update ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input['jenis'] = $_POST['jenis'] ?? '';

    if ($input['jenis'] === '') {
        $error = "Jenis dokumen wajib dipilih.";
    } else {
        $newFileUploaded = !empty($_FILES['file']['name']);
        $fileName = $data['nama_file']; // default: file lama

        if ($newFileUploaded) {
            $upload_dir = dirname(__DIR__, 3) . "/uploads/dokumen/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $tmpName = $_FILES['file']['tmp_name'];
            $newName = time() . '_' . basename($_FILES['file']['name']);
            $targetPath = $upload_dir . $newName;

            if (move_uploaded_file($tmpName, $targetPath)) {
                // Hapus file lama
                $oldPath = $upload_dir . $data['nama_file'];
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
                $fileName = $newName;
            } else {
                $error = "Gagal upload file baru.";
            }
        }

        if (!$error) {
            $stmtUpdate = $conn->prepare("UPDATE dokumen SET jenis = ?, nama_file = ? WHERE id = ?");
            $stmtUpdate->bind_param("ssi", $input['jenis'], $fileName, $id);

            if ($stmtUpdate->execute()) {
                header("Location: " . BASE_URL . "/modules/shared/dokumen/index.php?msg=updated&obj=dokumen");
                exit;
            } else {
                $error = "Gagal menyimpan perubahan.";
            }
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
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Pengajuan</label>
                        <input type="text" class="form-control"
                            value="<?= htmlspecialchars($data['nama_pegawai']) . ' - ' . htmlspecialchars($data['tujuan']) . ' (' . date('d-m-Y', strtotime($data['tanggal_berangkat'])) . ')' ?>"
                            readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jenis Dokumen</label>
                        <select name="jenis" class="form-select" required>
                            <option value="">-- Pilih Jenis --</option>
                            <option value="surat_tugas" <?= $input['jenis'] === 'surat_tugas' ? 'selected' : '' ?>>Surat Tugas</option>
                            <option value="undangan" <?= $input['jenis'] === 'undangan' ? 'selected' : '' ?>>Undangan</option>
                            <option value="revisi" <?= $input['jenis'] === 'revisi' ? 'selected' : '' ?>>Revisi</option>
                            <option value="lainnya" <?= $input['jenis'] === 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">File Lama</label><br>
                        <a href="<?= BASE_URL ?>/uploads/dokumen/<?= htmlspecialchars($data['nama_file']) ?>" target="_blank">
                            <?= htmlspecialchars($data['nama_file']) ?>
                        </a>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ganti File (Opsional)</label>
                        <input type="file" name="file" class="form-control">
                        <small class="text-muted">Kosongkan jika tidak ingin ganti file.</small>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">
                            <i class="fe fe-save me-1"></i> Simpan Perubahan
                        </button>
                        <a href="<?= BASE_URL ?>/modules/shared/dokumen/index.php" class="btn btn-secondary">Batal</a>
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
<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Edit Evaluasi Perjalanan';
$role = $_SESSION['role'] ?? '';
$id_user = $_SESSION['id_user'] ?? 0;

// Hanya pegawai yang bisa edit
if ($role !== 'pegawai') {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

// Validasi ID
$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: " . BASE_URL . "/modules/shared/evaluasi/index.php?msg=invalid&obj=evaluasi");
    exit;
}

// Ambil ID Pegawai aktif
$stmt = $conn->prepare("SELECT id FROM pegawai WHERE id_user = ?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$stmt->bind_result($id_pegawai);
$stmt->fetch();
$stmt->close();

// Ambil data evaluasi miliknya & masih draft
$stmt = $conn->prepare("
    SELECT * FROM evaluasi_perjalanan 
    WHERE id = ? AND id_pegawai = ? AND status = 'draft'
");
$stmt->bind_param("ii", $id, $id_pegawai);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    header("Location: " . BASE_URL . "/modules/shared/evaluasi/index.php?msg=notfound&obj=evaluasi");
    exit;
}

// Ambil pengajuan untuk validasi dropdown (hanya 1)
$stmt = $conn->prepare("
    SELECT id, tujuan, tanggal_berangkat
    FROM pengajuan_perjalanan
    WHERE id = ?
");
$stmt->bind_param("i", $data['id_pengajuan']);
$stmt->execute();
$pengajuan = $stmt->get_result();
$stmt->close();

// Default input
$input = [
    'kendala' => $data['kendala'] ?? '',
    'hasil'   => $data['hasil'] ?? '',
    'saran'   => $data['saran'] ?? ''
];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input['kendala'] = trim($_POST['kendala'] ?? '');
    $input['hasil'] = trim($_POST['hasil'] ?? '');
    $input['saran'] = trim($_POST['saran'] ?? '');
    $lampiran = $_FILES['lampiran'] ?? null;

    if (
        empty($input['kendala']) ||
        empty($input['hasil']) ||
        empty($input['saran'])
    ) {
        $error = "Semua field wajib diisi.";
    } else {
        $fileName = $data['lampiran']; // lampiran lama by default

        // Jika user upload lampiran baru
        if (!empty($lampiran['name'])) {
            $allowed_ext = ['pdf', 'doc', 'docx'];
            $ext = strtolower(pathinfo($lampiran['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed_ext)) {
                $error = "Lampiran hanya boleh PDF atau DOCX.";
            } else {
                $upload_dir = dirname(__DIR__, 3) . "/uploads/evaluasi/";
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $fileName = time() . '_' . basename($lampiran['name']);
                $targetPath = $upload_dir . $fileName;

                if (move_uploaded_file($lampiran['tmp_name'], $targetPath)) {
                    // Hapus file lama kalau ada
                    $oldFile = $upload_dir . $data['lampiran'];
                    if (is_file($oldFile) && file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                } else {
                    $error = "Gagal mengunggah lampiran baru.";
                }
            }
        }

        if (!$error) {
            $stmt = $conn->prepare("
                UPDATE evaluasi_perjalanan 
                SET kendala = ?, hasil = ?, saran = ?, lampiran = ?
                WHERE id = ? AND id_pegawai = ?
            ");
            $stmt->bind_param(
                "ssssii",
                $input['kendala'],
                $input['hasil'],
                $input['saran'],
                $fileName,
                $id,
                $id_pegawai
            );

            if ($stmt->execute()) {
                header("Location: " . BASE_URL . "/modules/shared/evaluasi/index.php?msg=updated&obj=evaluasi");
                exit;
            } else {
                $error = "Gagal memperbarui evaluasi.";
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
                        <select name="id_pengajuan" class="form-select" disabled>
                            <?php while ($row = $pengajuan->fetch_assoc()): ?>
                                <option value="<?= $row['id'] ?>" selected>
                                    <?= htmlspecialchars($row['tujuan']) ?> (<?= date('d-m-Y', strtotime($row['tanggal_berangkat'])) ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <small class="text-muted">Pengajuan tidak dapat diubah.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kendala</label>
                        <textarea name="kendala" rows="2" class="form-control" required><?= htmlspecialchars($input['kendala']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Hasil</label>
                        <textarea name="hasil" rows="2" class="form-control" required><?= htmlspecialchars($input['hasil']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Saran</label>
                        <textarea name="saran" rows="2" class="form-control" required><?= htmlspecialchars($input['saran']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Lampiran Baru (Opsional)</label>
                        <input type="file" name="lampiran" class="form-control">
                        <small class="text-muted">Kosongkan jika tidak ingin mengganti. Format PDF/DOCX.</small><br>
                        <small>Lampiran saat ini: <?= htmlspecialchars($data['lampiran']) ?></small>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">
                            <i class="fe fe-save me-1"></i> Simpan Perubahan
                        </button>
                        <a href="<?= BASE_URL ?>/modules/shared/evaluasi/index.php" class="btn btn-secondary">Batal</a>
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
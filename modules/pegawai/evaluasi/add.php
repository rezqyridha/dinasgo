<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Tambah Evaluasi Perjalanan';
$role = $_SESSION['role'] ?? '';
$id_user = $_SESSION['id_user'] ?? 0;

// Hanya pegawai yang bisa tambah
if ($role !== 'pegawai') {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

// Ambil ID Pegawai
$stmt = $conn->prepare("SELECT id FROM pegawai WHERE id_user = ?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$stmt->bind_result($id_pegawai);
$stmt->fetch();
$stmt->close();

// Query pengajuan valid
$stmt = $conn->prepare("
    SELECT pp.id, pp.tujuan, pp.tanggal_berangkat
    FROM pengajuan_perjalanan pp
    WHERE pp.id_pegawai = ?
      AND EXISTS (SELECT 1 FROM spt WHERE id_pengajuan = pp.id AND status = 'ditandatangani')
      AND EXISTS (SELECT 1 FROM sppd WHERE id_pengajuan = pp.id)
      AND EXISTS (SELECT 1 FROM rincian_biaya WHERE id_pengajuan = pp.id AND status = 'disetujui')
      AND EXISTS (SELECT 1 FROM pencairan_dana WHERE id_pengajuan = pp.id)
      AND NOT EXISTS (SELECT 1 FROM evaluasi_perjalanan WHERE id_pengajuan = pp.id)
    ORDER BY pp.tanggal_berangkat DESC
");
$stmt->bind_param("i", $id_pegawai);
$stmt->execute();
$pengajuan = $stmt->get_result();

$error = '';
$input = [
    'id_pengajuan' => '',
    'kendala' => '',
    'hasil' => '',
    'saran' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input['id_pengajuan'] = $_POST['id_pengajuan'] ?? '';
    $input['kendala'] = trim($_POST['kendala'] ?? '');
    $input['hasil'] = trim($_POST['hasil'] ?? '');
    $input['saran'] = trim($_POST['saran'] ?? '');
    $lampiran = $_FILES['lampiran'] ?? null;

    if (
        $input['id_pengajuan'] === '' ||
        $input['kendala'] === '' ||
        $input['hasil'] === '' ||
        $input['saran'] === '' ||
        empty($lampiran['name'])
    ) {
        $error = "Semua field wajib diisi dan lampiran harus dipilih.";
    } else {
        // Simpan lampiran
        $upload_dir = dirname(__DIR__, 3) . "/uploads/evaluasi/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $fileName = time() . '_' . basename($lampiran['name']);
        $targetPath = $upload_dir . $fileName;

        if (move_uploaded_file($lampiran['tmp_name'], $targetPath)) {
            $stmt = $conn->prepare("
                INSERT INTO evaluasi_perjalanan (id_pengajuan, id_pegawai, kendala, hasil, saran, status, lampiran)
                VALUES (?, ?, ?, ?, ?, 'draft', ?)
            ");
            $stmt->bind_param(
                "iissss",
                $input['id_pengajuan'],
                $id_pegawai,
                $input['kendala'],
                $input['hasil'],
                $input['saran'],
                $fileName
            );

            if ($stmt->execute()) {
                header("Location: " . BASE_URL . "/modules/shared/evaluasi_perjalanan/index.php?msg=added&obj=evaluasi");
                exit;
            } else {
                $error = "Gagal menyimpan evaluasi.";
            }
        } else {
            $error = "Gagal mengunggah lampiran.";
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
                        <label class="form-label">Pilih Pengajuan</label>
                        <select name="id_pengajuan" class="form-select" required>
                            <option value="">-- Pilih Pengajuan --</option>
                            <?php while ($row = $pengajuan->fetch_assoc()): ?>
                                <option value="<?= $row['id'] ?>" <?= $input['id_pengajuan'] == $row['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['tujuan']) ?> (<?= date('d-m-Y', strtotime($row['tanggal_berangkat'])) ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
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
                        <label class="form-label">Lampiran (PDF/DOCX)</label>
                        <input type="file" name="lampiran" class="form-control" required>
                        <small class="text-muted">File harus PDF/DOCX. Simpan di folder uploads/evaluasi/.</small>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">
                            <i class="fe fe-save me-1"></i> Simpan
                        </button>
                        <a href="<?= BASE_URL ?>/modules/shared/evaluasi_perjalanan/index.php" class="btn btn-secondary">Batal</a>
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
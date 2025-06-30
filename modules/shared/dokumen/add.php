<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Upload Dokumen';
$role = $_SESSION['role'] ?? '';
$id_user = $_SESSION['id_user'] ?? 0;

if (!in_array($role, ['admin', 'pegawai'])) {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

$error = '';
$input = [
    'id_pengajuan' => '',
    'jenis' => ''
];

//  Ambil pengajuan yang sudah punya SPT & belum punya dokumen
if ($role === 'pegawai') {
    // Pegawai: hanya pengajuan milik sendiri
    $query = $conn->prepare("
        SELECT pp.id, pp.tujuan, pp.tanggal_berangkat
        FROM pengajuan_perjalanan pp
        WHERE pp.id_pegawai = (SELECT id FROM pegawai WHERE id_user = ?)
          AND EXISTS (SELECT 1 FROM spt WHERE spt.id_pengajuan = pp.id)
          AND NOT EXISTS (SELECT 1 FROM dokumen WHERE dokumen.id_pengajuan = pp.id)
        ORDER BY pp.tanggal_berangkat DESC
    ");
    $query->bind_param("i", $id_user);
    $query->execute();
    $pengajuan = $query->get_result();
} else {
    // Admin: semua pengajuan
    $pengajuan = $conn->query("
        SELECT pp.id, pp.tujuan, pp.tanggal_berangkat, peg.nama
        FROM pengajuan_perjalanan pp
        JOIN pegawai peg ON pp.id_pegawai = peg.id
        WHERE EXISTS (SELECT 1 FROM spt WHERE spt.id_pengajuan = pp.id)
          AND NOT EXISTS (SELECT 1 FROM dokumen WHERE dokumen.id_pengajuan = pp.id)
        ORDER BY pp.tanggal_berangkat DESC
    ");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input['id_pengajuan'] = $_POST['id_pengajuan'] ?? '';
    $input['jenis'] = $_POST['jenis'] ?? '';

    if ($input['id_pengajuan'] === '' || $input['jenis'] === '' || empty($_FILES['files']['name'][0])) {
        $error = "Semua field wajib diisi dan file harus dipilih.";
    } else {
        $upload_dir = dirname(__DIR__, 3) . "/uploads/dokumen/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $success = true;

        foreach ($_FILES['files']['name'] as $index => $name) {
            $tmpName = $_FILES['files']['tmp_name'][$index];
            $fileName = time() . '_' . basename($name);
            $targetPath = $upload_dir . $fileName;

            if (move_uploaded_file($tmpName, $targetPath)) {
                $stmt = $conn->prepare("INSERT INTO dokumen (id_pengajuan, id_user, nama_file, jenis) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiss", $input['id_pengajuan'], $id_user, $fileName, $input['jenis']);
                if (!$stmt->execute()) {
                    $success = false;
                }
            } else {
                $success = false;
            }
        }

        if ($success) {
            header("Location: " . BASE_URL . "/modules/shared/dokumen/index.php?msg=added&obj=dokumen");
            exit;
        } else {
            header("Location: " . BASE_URL . "/modules/shared/dokumen/add.php?msg=error&obj=dokumen");
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
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Pilih Pengajuan</label>
                        <select name="id_pengajuan" class="form-select" required>
                            <option value="">-- Pilih Pengajuan --</option>
                            <?php while ($row = $pengajuan->fetch_assoc()): ?>
                                <option value="<?= $row['id'] ?>" <?= $input['id_pengajuan'] == $row['id'] ? 'selected' : '' ?>>
                                    <?= ($role === 'admin' ? htmlspecialchars($row['nama']) . ' - ' : '') ?>
                                    <?= htmlspecialchars($row['tujuan']) ?> (<?= date('d-m-Y', strtotime($row['tanggal_berangkat'])) ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
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
                        <label class="form-label">Pilih File (Bisa lebih dari 1)</label>
                        <input type="file" name="files[]" class="form-control" multiple required>
                        <small class="text-muted">Hanya PDF/DOCX maksimal beberapa MB sesuai aturan.</small>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">
                            <i class="fe fe-upload me-1"></i> Upload
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
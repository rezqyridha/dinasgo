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

$allowed_jenis = ['surat_tugas', 'bukti_pengeluaran', 'sppd', 'lainnya'];

// Dummy jenis untuk filter query
$dummy_jenis = $_GET['jenis'] ?? 'surat_tugas';
if (!in_array($dummy_jenis, $allowed_jenis)) $dummy_jenis = 'surat_tugas';

// Ambil pengajuan valid
if ($role === 'pegawai') {
    $query = $conn->prepare("
        SELECT pp.id, pp.tujuan, pp.tanggal_berangkat
        FROM pengajuan_perjalanan pp
        WHERE pp.id_pegawai = (SELECT id FROM pegawai WHERE id_user = ?)
          AND EXISTS (SELECT 1 FROM spt WHERE spt.id_pengajuan = pp.id AND spt.status = 'ditandatangani')
          AND EXISTS (SELECT 1 FROM sppd WHERE sppd.id_pengajuan = pp.id)
          AND NOT EXISTS (
              SELECT 1 FROM dokumen WHERE dokumen.id_pengajuan = pp.id AND dokumen.jenis = ?
          )
        ORDER BY pp.tanggal_berangkat DESC
    ");
    $query->bind_param("is", $id_user, $dummy_jenis);
} else {
    $query = $conn->prepare("
        SELECT pp.id, pp.tujuan, pp.tanggal_berangkat, peg.nama
        FROM pengajuan_perjalanan pp
        JOIN pegawai peg ON pp.id_pegawai = peg.id
        WHERE EXISTS (SELECT 1 FROM spt WHERE spt.id_pengajuan = pp.id AND spt.status = 'ditandatangani')
          AND EXISTS (SELECT 1 FROM sppd WHERE sppd.id_pengajuan = pp.id)
          AND NOT EXISTS (
              SELECT 1 FROM dokumen WHERE dokumen.id_pengajuan = pp.id AND dokumen.jenis = ?
          )
        ORDER BY pp.tanggal_berangkat DESC
    ");
    $query->bind_param("s", $dummy_jenis);
}
$query->execute();
$pengajuan = $query->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input['id_pengajuan'] = $_POST['id_pengajuan'] ?? '';
    $input['jenis'] = $_POST['jenis'] ?? '';

    if (!in_array($input['jenis'], $allowed_jenis)) {
        $error = "Jenis dokumen tidak valid.";
    } elseif ($input['id_pengajuan'] === '' || empty($_FILES['files']['name'][0])) {
        $error = "Semua field wajib diisi dan file harus dipilih.";
    } else {
        // ✅ Tambahkan pengecekan apakah sudah ada dokumen dengan id_pengajuan dan jenis yang sama
        $cek = $conn->prepare("SELECT COUNT(*) AS total FROM dokumen WHERE id_pengajuan = ? AND jenis = ?");
        $cek->bind_param("is", $input['id_pengajuan'], $input['jenis']);
        $cek->execute();
        $resultCek = $cek->get_result()->fetch_assoc();

        if ($resultCek['total'] > 0) {
            $error = "Dokumen untuk pengajuan dan jenis ini sudah ada.";
        } else {
            $upload_dir = dirname(__DIR__, 3) . "/uploads/dokumen/{$input['id_pengajuan']}/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $allowed_ext = ['pdf', 'docx'];
            $max_size = 5 * 1024 * 1024;

            $success = true;

            foreach ($_FILES['files']['name'] as $index => $name) {
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                if (!in_array($ext, $allowed_ext)) {
                    $error = "Format file tidak diizinkan.";
                    $success = false;
                    break;
                }

                if ($_FILES['files']['size'][$index] > $max_size) {
                    $error = "Ukuran file melebihi batas 5MB.";
                    $success = false;
                    break;
                }

                $safeName = preg_replace("/[^A-Za-z0-9_\-\.]/", "_", basename($name));
                $fileName = time() . '_' . $safeName;
                $targetPath = $upload_dir . $fileName;

                if (move_uploaded_file($_FILES['files']['tmp_name'][$index], $targetPath)) {
                    $stmt = $conn->prepare("INSERT INTO dokumen (id_pengajuan, id_user, nama_file, jenis) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("iiss", $input['id_pengajuan'], $id_user, $fileName, $input['jenis']);
                    if (!$stmt->execute()) {
                        $error = "Gagal simpan ke database.";
                        $success = false;
                        break;
                    }
                } else {
                    $error = "Gagal upload file.";
                    $success = false;
                    break;
                }
            }

            if ($success) {
                header("Location: " . BASE_URL . "/modules/shared/dokumen/index.php?msg=added&obj=dokumen");
                exit;
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
        <h4 class="mb-4 mt-4"><?= htmlspecialchars($pageTitle) ?></h4>

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
                            <?php foreach ($allowed_jenis as $jenis): ?>
                                <option value="<?= $jenis ?>" <?= $input['jenis'] === $jenis ? 'selected' : '' ?>>
                                    <?= ucwords(str_replace('_', ' ', $jenis)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="alert alert-info small">
                        <strong>Catatan:</strong> Jika mengunggah <code>Bukti Pengeluaran</code>,
                        pastikan semua nota/bukti digabung ke <strong>satu file PDF</strong>.
                        Gunakan <a href="https://www.ilovepdf.com/merge_pdf" target="_blank">https://www.ilovepdf.com/merge_pdf</a>.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pilih File</label>
                        <input type="file" name="files[]" class="form-control" multiple required>
                        <small class="text-muted">Hanya PDF/DOCX. Maksimal 5MB per file.</small>
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
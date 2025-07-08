<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Detail Dokumen';
$role = $_SESSION['role'] ?? '';
$id_user = $_SESSION['id_user'] ?? 0;

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: " . BASE_URL . "/modules/shared/dokumen/index.php?msg=invalid&obj=dokumen");
    exit;
}

// ✅ Ambil data detail
$stmt = $conn->prepare("
    SELECT d.*, pp.tujuan, pp.tanggal_berangkat, peg.nama AS nama_pegawai, u.nama AS uploader
    FROM dokumen d
    JOIN pengajuan_perjalanan pp ON d.id_pengajuan = pp.id
    JOIN pegawai peg ON pp.id_pegawai = peg.id
    JOIN user u ON d.id_user = u.id
    WHERE d.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    header("Location: " . BASE_URL . "/modules/shared/dokumen/index.php?msg=notfound&obj=dokumen");
    exit;
}

// ✅ Proteksi RBAC untuk pegawai (hanya lihat milik sendiri)
if ($role === 'pegawai') {
    $stmtCheck = $conn->prepare("SELECT id FROM pegawai WHERE id_user = ?");
    $stmtCheck->bind_param("i", $id_user);
    $stmtCheck->execute();
    $stmtCheck->bind_result($id_pegawai);
    $stmtCheck->fetch();
    $stmtCheck->close();

    if ($data['id_user'] != $id_user) {
        header("Location: " . BASE_URL . "/unauthorized.php");
        exit;
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

        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="250">Nama File</th>
                        <td>:
                            <a href="<?= BASE_URL ?>/uploads/dokumen/<?= htmlspecialchars($data['nama_file']) ?>" target="_blank">
                                <?= htmlspecialchars($data['nama_file']) ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <th>Jenis Dokumen</th>
                        <td>: <?= htmlspecialchars(ucfirst($data['jenis'])) ?></td>
                    </tr>
                    <tr>
                        <th>Pengajuan</th>
                        <td>: <?= htmlspecialchars($data['tujuan']) ?> (<?= date('d-m-Y', strtotime($data['tanggal_berangkat'])) ?>)</td>
                    </tr>
                    <tr>
                        <th>Nama Pegawai</th>
                        <td>: <?= htmlspecialchars($data['nama_pegawai']) ?></td>
                    </tr>
                    <tr>
                        <th>Uploaded By</th>
                        <td>: <?= htmlspecialchars($data['uploader']) ?></td>
                    </tr>
                    <tr>
                        <th>Uploaded At</th>
                        <td>: <?= date('d-m-Y H:i', strtotime($data['uploaded_at'])) ?></td>
                    </tr>
                </table>

                <?php if (in_array($role, ['pegawai', 'admin', 'atasan'])): ?>
                    <div class="mt-4">
                        <a href="<?= BASE_URL ?>/modules/shared/dokumen/index.php" class="btn btn-secondary">
                            <i class="fe fe-arrow-left me-1"></i> Kembali
                        </a>
                        <?php if ($role === 'pegawai' || $role === 'admin'): ?>
                            <button type="button"
                                class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadTambahanModal">
                                <i class="fe fe-plus me-1"></i> Upload Dokumen Tambahan
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="uploadTambahanModal" tabindex="-1" aria-labelledby="uploadTambahanLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="<?= BASE_URL ?>/modules/shared/dokumen/upload_tambahan.php" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadTambahanLabel">Upload Dokumen Tambahan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_pengajuan" value="<?= $data['id_pengajuan'] ?>">
                <div class="mb-3">
                    <label class="form-label">Pilih File (Bisa lebih dari 1)</label>
                    <input type="file" name="files[]" class="form-control" multiple required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Jenis Dokumen</label>
                    <select name="jenis" class="form-select" required>
                        <option value="">-- Pilih Jenis --</option>
                        <option value="surat_tugas">Surat Tugas</option>
                        <option value="undangan">Undangan</option>
                        <option value="revisi">Revisi</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Upload</button>
            </div>
        </form>
    </div>
</div>

<?php
require_once LAYOUTS_PATH . '/footer.php';
require_once LAYOUTS_PATH . '/scripts.php';
?>
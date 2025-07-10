<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Detail Pengajuan';
$role = $_SESSION['role'] ?? '';
$id_user = $_SESSION['id_user'] ?? 0;

// ID Pengajuan
$id_pengajuan = (int) ($_GET['id_pengajuan'] ?? 0);
if ($id_pengajuan <= 0) {
    header("Location: " . BASE_URL . "/modules/shared/dokumen/index.php?msg=invalid&obj=pengajuan");
    exit;
}

// Ambil info pokok
$stmt = $conn->prepare("
    SELECT pp.*, peg.nama AS nama_pegawai
    FROM pengajuan_perjalanan pp
    JOIN pegawai peg ON pp.id_pegawai = peg.id
    WHERE pp.id = ?
");
$stmt->bind_param("i", $id_pengajuan);
$stmt->execute();
$info = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$info) {
    header("Location: " . BASE_URL . "/modules/shared/dokumen/index.php?msg=notfound&obj=pengajuan");
    exit;
}

// Proteksi RBAC pegawai: hanya lihat milik sendiri
if ($role === 'pegawai') {
    $stmtCheck = $conn->prepare("SELECT id FROM pegawai WHERE id_user = ?");
    $stmtCheck->bind_param("i", $id_user);
    $stmtCheck->execute();
    $stmtCheck->bind_result($id_pegawai);
    $stmtCheck->fetch();
    $stmtCheck->close();

    if ($info['id_pegawai'] != $id_pegawai) {
        header("Location: " . BASE_URL . "/unauthorized.php");
        exit;
    }
}

// Ambil semua dokumen
$stmt = $conn->prepare("
    SELECT * FROM dokumen WHERE id_pengajuan = ? ORDER BY uploaded_at DESC
");
$stmt->bind_param("i", $id_pengajuan);
$stmt->execute();
$listDokumen = $stmt->get_result();
$stmt->close();

// Filter jenis yang belum ada
$allowedJenis = ['surat_tugas', 'bukti_pengeluaran', 'sppd', 'lainnya'];
$usedJenis = [];

foreach ($listDokumen as $row) {
    $usedJenis[] = $row['jenis'];
}
$availableJenis = array_diff($allowedJenis, $usedJenis);

require_once LAYOUTS_PATH . '/head.php';
require_once LAYOUTS_PATH . '/header.php';
require_once LAYOUTS_PATH . '/topbar.php';
require_once LAYOUTS_PATH . '/sidebar.php';
?>

<div class="main-content app-content">
    <div class="container-fluid">
        <h4 class="mb-4 mt-4"><?= htmlspecialchars($pageTitle) ?></h4>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">Tujuan</th>
                        <td>: <?= htmlspecialchars($info['tujuan']) ?></td>
                    </tr>
                    <tr>
                        <th>Tanggal Berangkat</th>
                        <td>: <?= date('d-m-Y', strtotime($info['tanggal_berangkat'])) ?></td>
                    </tr>
                    <tr>
                        <th>Nama Pegawai</th>
                        <td>: <?= htmlspecialchars($info['nama_pegawai']) ?></td>
                    </tr>
                </table>
                <div class="mt-3">
                    <a href="<?= BASE_URL ?>/modules/shared/dokumen/index.php" class="btn btn-secondary">
                        <i class="fe fe-arrow-left me-1"></i> Kembali
                    </a>
                    <?php if (in_array($role, ['pegawai', 'admin']) && count($availableJenis) > 0): ?>
                        <button type="button"
                            class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="fe fe-plus me-1"></i> Upload Dokumen Tambahan
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header"><strong>Daftar Dokumen</strong></div>
            <div class="card-body">
                <table class="table table-bordered table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>No</th>
                            <th>Nama File</th>
                            <th>Jenis</th>
                            <th>Uploaded At</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($listDokumen->num_rows > 0): ?>
                            <?php $no = 1;
                            foreach ($listDokumen as $row): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/uploads/dokumen/<?= $id_pengajuan ?>/<?= htmlspecialchars($row['nama_file']) ?>" target="_blank">
                                            <?= htmlspecialchars($row['nama_file']) ?>
                                        </a>
                                    </td>
                                    <td><?= ucwords(str_replace('_', ' ', $row['jenis'])) ?></td>
                                    <td><?= date('d-m-Y H:i', strtotime($row['uploaded_at'])) ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/modules/shared/dokumen/detail.php?id=<?= $row['id'] ?>&from=detail_pengajuan.php&id_pengajuan=<?= (int)$id_pengajuan ?>"
                                            class="btn btn-sm btn-info">
                                            <i class="fe fe-eye"></i> Lihat
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada dokumen diupload.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ✅ Modal Upload Dokumen Tambahan -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST"
            action="<?= BASE_URL ?>/modules/shared/dokumen/upload_tambahan.php"
            enctype="multipart/form-data"
            class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">Upload Dokumen Tambahan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="id_pengajuan" value="<?= (int)$id_pengajuan ?>">
                <input type="hidden" name="redirect" value="detail_pengajuan.php?id_pengajuan=<?= (int)$id_pengajuan ?>">

                <div class="mb-3">
                    <label class="form-label">Jenis Dokumen</label>
                    <select name="jenis" class="form-select" required>
                        <option value="">-- Pilih Jenis --</option>
                        <?php foreach ($availableJenis as $jenis): ?>
                            <option value="<?= $jenis ?>">
                                <?= ucwords(str_replace('_', ' ', $jenis)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- ✅ Alert merge khusus bukti pengeluaran -->
                <div class="alert alert-info small">
                    <strong>Catatan:</strong> Untuk <code>Bukti Pengeluaran</code>, gabungkan semua nota/bukti ke <strong>satu file PDF</strong>.
                    Gunakan <a href="https://www.ilovepdf.com/merge_pdf" target="_blank">https://www.ilovepdf.com/merge_pdf</a>.
                </div>

                <div class="mb-3">
                    <label class="form-label">Pilih File</label>
                    <input type="file" name="file" class="form-control" required>
                    <small class="text-muted">Hanya PDF/DOCX. Maksimal 5MB.</small>
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fe fe-upload me-1"></i> Upload
                </button>
            </div>
        </form>
    </div>
</div>



<?php
require_once LAYOUTS_PATH . '/footer.php';
require_once LAYOUTS_PATH . '/scripts.php';
?>
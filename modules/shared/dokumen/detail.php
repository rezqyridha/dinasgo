<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Detail Dokumen';
$role = $_SESSION['role'] ?? '';
$id_user = $_SESSION['id_user'] ?? 0;

// Ambil ID Dokumen
$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: " . BASE_URL . "/modules/shared/dokumen/index.php?msg=invalid&obj=dokumen");
    exit;
}

// Ambil detail dokumen
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
$stmt->close();

if (!$data) {
    header("Location: " . BASE_URL . "/modules/shared/dokumen/index.php?msg=notfound&obj=dokumen");
    exit;
}

// Proteksi RBAC
if ($role === 'pegawai' && $data['id_user'] != $id_user) {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

// Info file & path
$ext = strtolower(pathinfo($data['nama_file'], PATHINFO_EXTENSION));
$fileUrl = BASE_URL . "/uploads/dokumen/" . (int)$data['id_pengajuan'] . "/" . htmlspecialchars($data['nama_file']);

// Handle smart back
$from = $_GET['from'] ?? '';
$id_pengajuan = (int) ($_GET['id_pengajuan'] ?? 0);
$backUrl = BASE_URL . "/modules/shared/dokumen/index.php";

if ($from === 'detail_pengajuan.php' && $id_pengajuan > 0) {
    $backUrl = BASE_URL . "/modules/shared/dokumen/detail_pengajuan.php?id_pengajuan=" . $id_pengajuan;
}

require_once LAYOUTS_PATH . '/head.php';
require_once LAYOUTS_PATH . '/header.php';
require_once LAYOUTS_PATH . '/topbar.php';
require_once LAYOUTS_PATH . '/sidebar.php';
?>

<div class="main-content app-content">
    <div class="container-fluid">
        <h4 class="mb-4 mt-4"><?= htmlspecialchars($pageTitle) ?></h4>

        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="250">Nama File</th>
                        <td>:
                            <a href="<?= $fileUrl ?>" target="_blank">
                                <?= htmlspecialchars($data['nama_file']) ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <th>Jenis Dokumen</th>
                        <td>: <?= htmlspecialchars(ucwords(str_replace('_', ' ', $data['jenis']))) ?></td>
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

                <div class="mt-4">
                    <h5>Preview Dokumen:</h5>

                    <?php if ($ext === 'pdf'): ?>
                        <embed src="<?= $fileUrl ?>" type="application/pdf" width="100%" height="600px">
                    <?php elseif ($ext === 'docx'): ?>
                        <p>File DOCX tidak dapat dipreview di browser. Silakan unduh.</p>
                        <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-primary">
                            <i class="fe fe-download me-1"></i> Download Dokumen
                        </a>
                    <?php else: ?>
                        <p>Format file tidak dikenali untuk preview.</p>
                    <?php endif; ?>

                    <a href="<?= $backUrl ?>" class="btn btn-secondary mt-3">
                        <i class="fe fe-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once LAYOUTS_PATH . '/footer.php';
require_once LAYOUTS_PATH . '/scripts.php';
?>
<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Verifikasi oleh Atasan';

if ($_SESSION['role'] !== 'atasan') {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$id_pengajuan = (int)($_GET['id'] ?? 0);

// Ambil data pengajuan
$stmt = $conn->prepare("SELECT p.*, peg.nama 
    FROM pengajuan_perjalanan p 
    JOIN pegawai peg ON p.id_pegawai = peg.id 
    WHERE p.id = ?");
$stmt->bind_param("i", $id_pengajuan);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

if (!$data) {
    header("Location: index.php?msg=notfound");
    exit;
}

if ($data['status'] !== 'diajukan') {
    header("Location: index.php?msg=invalidstatus");
    exit;
}

// Proses simpan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'] ?? '';
    $catatan = trim($_POST['catatan'] ?? '');

    if (!in_array($status, ['disetujui', 'ditolak']) || $catatan === '') {
        header("Location: verifikasi_atasan.php?id=$id_pengajuan&msg=invalid");
        exit;
    }

    // Simpan ke tabel persetujuan
    $stmt = $conn->prepare("INSERT INTO persetujuan 
        (id_pengajuan, id_verifikator, catatan, status, tanggal_persetujuan) 
        VALUES (?, ?, ?, ?, CURDATE())");
    $stmt->bind_param("iiss", $id_pengajuan, $id_user, $catatan, $status);

    if ($stmt->execute()) {
        // Simpan juga ke kolom diverifikasi_oleh di pengajuan_perjalanan
        $conn->query("UPDATE pengajuan_perjalanan SET diverifikasi_oleh = $id_user WHERE id = $id_pengajuan");

        header("Location: index.php?msg=verifikasi_berhasil");
    } else {
        header("Location: verifikasi_atasan.php?id=$id_pengajuan&msg=gagal_simpan");
    }
    exit;
}

require_once LAYOUTS_PATH . '/head.php';
require_once LAYOUTS_PATH . '/header.php';
require_once LAYOUTS_PATH . '/topbar.php';
require_once LAYOUTS_PATH . '/sidebar.php';
?>

<div class="main-content app-content">
    <div class="container-fluid">
        <div class="card custom-card mt-5">
            <div class="card-header">
                <h5 class="card-title mb-0"><?= $pageTitle ?></h5>
            </div>
            <form method="POST">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Pegawai</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($data['nama']) ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tujuan</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($data['tujuan']) ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status Verifikasi</label>
                        <select name="status" class="form-select" required>
                            <option value="" hidden>-- Pilih Status --</option>
                            <option value="disetujui">Disetujui</option>
                            <option value="ditolak">Ditolak</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Catatan</label>
                        <textarea name="catatan" class="form-control" rows="3" required placeholder="Tulis catatan verifikasi..."></textarea>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="fe fe-check-circle me-1"></i> Simpan Verifikasi
                    </button>
                    <a href="index.php" class="btn btn-danger">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
require_once LAYOUTS_PATH . '/footer.php';
require_once LAYOUTS_PATH . '/scripts.php';
?>
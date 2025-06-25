<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Verifikasi Pengajuan Perjalanan';

// Cek role yang diperbolehkan
$role = $_SESSION['role'];
$id_user = $_SESSION['id_user'];

if (!in_array($role, ['admin', 'atasan'])) {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

// Ambil ID pengajuan
$id_pengajuan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_pengajuan <= 0) {
    header("Location: index.php?msg=invalid&obj=pengajuan");
    exit;
}

// Ambil data pengajuan
$stmt = $conn->prepare("SELECT p.*, peg.nama AS nama_pegawai FROM pengajuan_perjalanan p JOIN pegawai peg ON p.id_pegawai = peg.id WHERE p.id = ?");
$stmt->bind_param("i", $id_pengajuan);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

if (!$data) {
    header("Location: index.php?msg=invalid&obj=pengajuan");
    exit;
}

// Handle submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status   = $_POST['status'] ?? '';
    $catatan  = trim($_POST['catatan'] ?? '');

    if (!in_array($status, ['disetujui', 'ditolak'])) {
        header("Location: verifikasi.php?id=$id_pengajuan&msg=invalid&obj=pengajuan");
        exit;
    }

    // Update tabel pengajuan_perjalanan
    $stmt1 = $conn->prepare("UPDATE pengajuan_perjalanan 
        SET status = ?, diverifikasi_oleh = ?, catatan_verifikasi = ? 
        WHERE id = ?");
    $stmt1->bind_param("sisi", $status, $id_user, $catatan, $id_pengajuan);
    $stmt1->execute();
    $stmt1->close();

    // Insert ke tabel persetujuan
    $stmt2 = $conn->prepare("INSERT INTO persetujuan 
    (id_pengajuan, id_verifikator, catatan, status, tanggal_persetujuan) 
    VALUES (?, ?, ?, ?, CURDATE())");
    $stmt2->bind_param("iiss", $id_pengajuan, $id_user, $catatan, $status);
    $stmt2->execute();
    $stmt2->close();

    header("Location: index.php?msg=updated&obj=pengajuan");
    exit;
}

require_once LAYOUTS_PATH . '/head.php';
require_once LAYOUTS_PATH . '/header.php';
require_once LAYOUTS_PATH . '/topbar.php';
require_once LAYOUTS_PATH . '/sidebar.php';
?>

<div class="main-content app-content">
    <div class="container-fluid">

        <div class="row justify-content-center mt-5">
            <div class="col-md-8">
                <div class="card shadow-sm custom-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Form Verifikasi Pengajuan</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Pegawai</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($data['nama_pegawai']) ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tujuan Perjalanan</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($data['tujuan']) ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status Verifikasi</label>
                                <select name="status" class="form-select" required>
                                    <option value="">-- Pilih Status --</option>
                                    <option value="disetujui">Disetujui</option>
                                    <option value="ditolak">Ditolak</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Catatan</label>
                                <textarea name="catatan" class="form-control" rows="3" placeholder="Tulis catatan verifikasi..."></textarea>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-secondary"><i class="fe fe-arrow-left me-1"></i> Kembali</a>
                                <button type="submit" class="btn btn-primary"><i class="fe fe-check-circle me-1"></i> Simpan Verifikasi</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php
require_once LAYOUTS_PATH . '/footer.php';
require_once LAYOUTS_PATH . '/scripts.php';
?>
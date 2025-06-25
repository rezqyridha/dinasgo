<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Edit Pengajuan';
$role = $_SESSION['role'];
$id_user = $_SESSION['id_user'];

if (!in_array($role, ['admin', 'pegawai'])) {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: index.php?msg=invalid&obj=pengajuan");
    exit;
}

// Ambil ID Pegawai jika login sebagai pegawai
if ($role === 'pegawai') {
    $stmt = $conn->prepare("SELECT id FROM pegawai WHERE id_user = ?");
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $stmt->bind_result($id_pegawai);
    $stmt->fetch();
    $stmt->close();
} else {
    $id_pegawai = null; // admin bisa akses semua
}

// Ambil data lama
$sql = "
    SELECT p.*, pg.nama AS nama_pegawai 
    FROM pengajuan_perjalanan p 
    JOIN pegawai pg ON p.id_pegawai = pg.id
    WHERE p.id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    header("Location: index.php?msg=invalid&obj=pengajuan");
    exit;
}

// Validasi: Pegawai hanya bisa edit miliknya
if ($role === 'pegawai' && $data['id_pegawai'] != $id_pegawai) {
    header("Location: index.php?msg=unauthorized&obj=pengajuan");
    exit;
}

// Tidak boleh edit jika bukan status diajukan (khusus pegawai)
if ($role === 'pegawai' && $data['status'] !== 'diajukan') {
    header("Location: index.php?msg=locked&obj=pengajuan");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tujuan           = trim($_POST['tujuan']);
    $keperluan        = trim($_POST['keperluan']);
    $tanggal_berangkat = $_POST['tanggal_berangkat'] ?? '';
    $tanggal_kembali   = $_POST['tanggal_kembali'] ?? '';
    $estimasi_biaya   = str_replace(['Rp', '.', ','], '', $_POST['estimasi_biaya'] ?? '0');

    if ($tujuan === '' || $keperluan === '' || !$tanggal_berangkat || !$tanggal_kembali) {
        header("Location: edit.php?id=$id&msg=kosong&obj=pengajuan");
        exit;
    }

    $stmt = $conn->prepare("
        UPDATE pengajuan_perjalanan
        SET tujuan=?, keperluan=?, tanggal_berangkat=?, tanggal_kembali=?, estimasi_biaya=?
        WHERE id=?
    ");
    $stmt->bind_param("ssssdi", $tujuan, $keperluan, $tanggal_berangkat, $tanggal_kembali, $estimasi_biaya, $id);

    if ($stmt->execute()) {
        header("Location: index.php?msg=updated&obj=pengajuan");
    } else {
        header("Location: edit.php?id=$id&msg=failed&obj=pengajuan");
    }
    exit;
}

include_once LAYOUTS_PATH . '/head.php';
include_once LAYOUTS_PATH . '/header.php';
include_once LAYOUTS_PATH . '/sidebar.php';
include_once LAYOUTS_PATH . '/topbar.php';
?>

<div class="main-content app-content">
    <div class="container-fluid">
        <div class="card custom-card mt-5 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title mb-0">Edit Pengajuan Perjalanan</div>
                <a href="index.php" class="btn btn-sm btn-dark"><i class="fe fe-arrow-left me-1"></i> Kembali</a>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="tujuan" class="form-label">Tujuan Perjalanan</label>
                        <input type="text" name="tujuan" id="tujuan" class="form-control" value="<?= htmlspecialchars($data['tujuan']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="keperluan" class="form-label">Keperluan</label>
                        <textarea name="keperluan" id="keperluan" rows="3" class="form-control" required><?= htmlspecialchars($data['keperluan']) ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_berangkat" class="form-label">Tanggal Berangkat</label>
                            <input type="date" name="tanggal_berangkat" id="tanggal_berangkat" class="form-control" value="<?= $data['tanggal_berangkat'] ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_kembali" class="form-label">Tanggal Kembali</label>
                            <input type="date" name="tanggal_kembali" id="tanggal_kembali" class="form-control" value="<?= $data['tanggal_kembali'] ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="estimasi_biaya" class="form-label">Estimasi Biaya (Rp)</label>
                        <input type="text" name="estimasi_biaya" id="estimasi_biaya" class="form-control" value="<?= number_format($data['estimasi_biaya'], 0, ',', '.') ?>">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fe fe-save me-1"></i> Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Format input estimasi biaya saat diketik
    const estimasiInput = document.getElementById('estimasi_biaya');
    estimasiInput.addEventListener('input', function() {
        let angka = this.value.replace(/\D/g, '');
        this.value = new Intl.NumberFormat('id-ID').format(angka);
    });
</script>

<?php
include_once LAYOUTS_PATH . '/footer.php';
include_once LAYOUTS_PATH . '/scripts.php';
?>
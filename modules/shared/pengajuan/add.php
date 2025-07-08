<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once AUTH_PATH . '/session.php';
require_once CONFIG_PATH . '/koneksi.php';

$pageTitle = 'Tambah Pengajuan Perjalanan Dinas';

//  Hanya role pegawai
if ($_SESSION['role'] !== 'pegawai') {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

$id_user = $_SESSION['id_user'];

//  Ambil ID Pegawai dari user login
$stmt = $conn->prepare("SELECT id FROM pegawai WHERE id_user = ?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$stmt->bind_result($id_pegawai);
$stmt->fetch();
$stmt->close();

// Cek validitas
if (!$id_pegawai) {
    header("Location: index.php?msg=invalid&obj=pengajuan");
    exit;
}

// Proses submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tujuan    = trim($_POST['tujuan'] ?? '');
    $keperluan = trim($_POST['keperluan'] ?? '');
    $berangkat = $_POST['tanggal_berangkat'] ?? '';
    $kembali   = $_POST['tanggal_kembali'] ?? '';
    $biaya     = floatval($_POST['estimasi_biaya'] ?? 0);

    // Validasi
    if ($tujuan === '' || $keperluan === '' || !$berangkat || !$kembali || $biaya <= 0) {
        header("Location: add.php?msg=kosong&obj=pengajuan");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO pengajuan_perjalanan (id_pegawai, tujuan, tanggal_berangkat, tanggal_kembali, keperluan, estimasi_biaya) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssd", $id_pegawai, $tujuan, $berangkat, $kembali, $keperluan, $biaya);

    if ($stmt->execute()) {
        header("Location: index.php?msg=added&obj=pengajuan");
    } else {
        header("Location: index.php?msg=failed&obj=pengajuan");
    }
    exit;
}

require_once LAYOUTS_PATH . '/head.php';
require_once LAYOUTS_PATH . '/header.php';
require_once LAYOUTS_PATH . '/sidebar.php';
require_once LAYOUTS_PATH . '/topbar.php';
?>

<div class="main-content app-content">
    <div class="container-fluid">
        <div class="card custom-card mt-5 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title mb-0">Form Tambah Pengajuan</div>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="tujuan" class="form-label">Tujuan</label>
                        <input type="text" name="tujuan" id="tujuan" class="form-control" required placeholder="Contoh: Jakarta, Bandung">
                    </div>
                    <div class="mb-3">
                        <label for="keperluan" class="form-label">Keperluan</label>
                        <textarea name="keperluan" id="keperluan" class="form-control" rows="3" required placeholder="Jelaskan maksud perjalanan"></textarea>
                    </div>
                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label for="tanggal_berangkat" class="form-label">Tanggal Berangkat</label>
                            <input type="date" name="tanggal_berangkat" id="tanggal_berangkat" class="form-control" required>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="tanggal_kembali" class="form-label">Tanggal Kembali</label>
                            <input type="date" name="tanggal_kembali" id="tanggal_kembali" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="estimasi_biaya_display" class="form-label">Estimasi Biaya (Rp)</label>
                        <input type="text" id="estimasi_biaya_display" class="form-control" placeholder="Contoh: Rp 1.000.000" required>
                        <input type="hidden" name="estimasi_biaya" id="estimasi_biaya">
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary"><i class="fe fe-save me-1"></i> Simpan</button>
                        <a href="index.php" class="btn btn-secondary">Batal</a>
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

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const displayInput = document.getElementById("estimasi_biaya_display");
        const hiddenInput = document.getElementById("estimasi_biaya");

        function formatRupiah(angka) {
            return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        function cleanInput(val) {
            return val.replace(/[^0-9]/g, '');
        }

        displayInput.addEventListener("input", function() {
            let raw = cleanInput(this.value);
            hiddenInput.value = raw;
            this.value = raw ? "Rp " + formatRupiah(raw) : "";
        });
    });
</script>
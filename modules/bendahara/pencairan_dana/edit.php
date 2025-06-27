<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Edit Pencairan Dana';
$role = $_SESSION['role'] ?? '';
$id_user = $_SESSION['id_user'] ?? 0;

if ($role !== 'bendahara') {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: " . BASE_URL . "/modules/shared/pencairan_dana/index.php?msg=invalid_id&obj=pencairan");
    exit;
}

$error = '';
$input = [
    'jumlah_dana' => '',
    'tanggal_pencairan' => ''
];

// Ambil data lama
$stmt = $conn->prepare("
    SELECT pd.*, 
           p.tujuan, p.estimasi_biaya,
           peg.nama AS nama_pegawai, p.id AS id_pengajuan, rb.id AS id_rincian, rb.jumlah_total
    FROM pencairan_dana pd
    JOIN pengajuan_perjalanan p ON pd.id_pengajuan = p.id
    JOIN pegawai peg ON p.id_pegawai = peg.id
    LEFT JOIN rincian_biaya rb ON rb.id_pengajuan = p.id AND rb.status = 'disetujui'
    WHERE pd.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    header("Location: " . BASE_URL . "/modules/shared/pencairan_dana/index.php?msg=not_found&obj=pencairan");
    exit;
}

$input['jumlah_dana'] = $data['jumlah_dana'];
$input['tanggal_pencairan'] = $data['tanggal_pencairan'];

// Ambil rincian detail
$stmtDetail = $conn->prepare("SELECT jenis_biaya, jumlah, satuan, harga_satuan FROM rincian_biaya_detail WHERE id_rincian = ?");
$stmtDetail->bind_param("i", $data['id_rincian']);
$stmtDetail->execute();
$detail = $stmtDetail->get_result();

// Proses update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = str_replace(['Rp', ' ', ',00'], '', $_POST['jumlah_dana']);
    $formatted = number_format((int) str_replace('.', '', $raw), 0, ',', '.');
    $input['jumlah_dana'] = $formatted;
    $input['tanggal_pencairan'] = trim($_POST['tanggal_pencairan']);

    if (in_array('', $input)) {
        $_SESSION['error'] = "Semua field wajib diisi.";
    } else {
        $stmtUpdate = $conn->prepare("UPDATE pencairan_dana SET jumlah_dana = ?, tanggal_pencairan = ? WHERE id = ?");
        $stmtUpdate->bind_param("ssi", $input['jumlah_dana'], $input['tanggal_pencairan'], $id);

        if ($stmtUpdate->execute()) {
            header("Location: " . BASE_URL . "/modules/shared/pencairan_dana/index.php?msg=updated&obj=pencairan");
            exit;
        } else {
            header("Location: " . BASE_URL . "/modules/shared/pencairan_dana/index.php?msg=error&obj=pencairan");
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mt-3 mb-0"><?= htmlspecialchars($pageTitle) ?></h4>
        </div>

        <div class="card custom-card shadow-sm">
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nama Pegawai</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($data['nama_pegawai']) ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tujuan</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($data['tujuan']) ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estimasi Biaya</label>
                        <input type="text" class="form-control" value="Rp <?= number_format($data['estimasi_biaya'], 0, ',', '.') ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Total Rincian Biaya</label>
                        <input type="text" class="form-control" value="Rp <?= number_format($data['jumlah_total'], 0, ',', '.') ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Detail Rincian</label>
                        <div class="border p-2 bg-light rounded small">
                            <ul class="mb-0">
                                <?php while ($row = $detail->fetch_assoc()): ?>
                                    <li><?= htmlspecialchars($row['jenis_biaya']) ?>: <?= $row['jumlah'] ?> <?= htmlspecialchars($row['satuan']) ?> x Rp<?= number_format($row['harga_satuan'], 0, ',', '.') ?> = Rp<?= number_format($row['jumlah'] * $row['harga_satuan'], 0, ',', '.') ?></li>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="jumlah_dana" class="form-label">Jumlah Dana Dicairkan</label>
                        <input type="text" name="jumlah_dana" id="jumlah_dana" class="form-control" value="Rp <?= htmlspecialchars($input['jumlah_dana']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="tanggal_pencairan" class="form-label">Tanggal Pencairan</label>
                        <input type="date" name="tanggal_pencairan" id="tanggal_pencairan" class="form-control" value="<?= htmlspecialchars($input['tanggal_pencairan']) ?>" required>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary"><i class="fe fe-save me-1"></i> Perbarui</button>
                        <a href="<?= BASE_URL ?>/modules/shared/pencairan_dana/index.php" class="btn btn-secondary">Batal</a>
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
    function formatRupiah(angka, prefix = 'Rp ') {
        let number_string = angka.replace(/[^,\d]/g, '').toString(),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] !== undefined ? rupiah + ',' + split[1].substring(0, 2) : rupiah;
        return prefix + rupiah;
    }

    document.getElementById('jumlah_dana').addEventListener('input', function(e) {
        let input = e.target;
        let angka = input.value.replace(/[^,\d]/g, '');
        input.value = formatRupiah(angka);
    });
</script>
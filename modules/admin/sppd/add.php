<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Tambah SPPD';
$role = $_SESSION['role'] ?? '';
if ($role !== 'admin') {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

$input = [
    'id_pengajuan' => '',
    'tanggal_terbit' => '',
    'catatan' => ''
];
$nomor_sppd = '';
$error = '';

//  Ambil daftar pengajuan yang sudah punya SPT tapi belum ada SPPD
$pengajuan = $conn->query("
    SELECT 
      pp.id, 
      peg.nama, 
      pp.tujuan, 
      pp.tanggal_berangkat,
      spt.nomor_spt
    FROM pengajuan_perjalanan pp
    JOIN pegawai peg ON pp.id_pegawai = peg.id
    JOIN spt ON spt.id_pengajuan = pp.id AND spt.status = 'ditandatangani'
    WHERE NOT EXISTS (
      SELECT 1 FROM sppd WHERE sppd.id_pengajuan = pp.id
    )
    ORDER BY pp.tanggal_berangkat DESC
");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input['id_pengajuan'] = $_POST['id_pengajuan'] ?? '';
    $input['tanggal_terbit'] = $_POST['tanggal_terbit'] ?? '';
    $input['catatan'] = trim($_POST['catatan'] ?? '');

    if (in_array('', $input)) {
        $error = 'Semua field wajib diisi.';
    } else {
        $tahun = date('Y', strtotime($input['tanggal_terbit']));
        $stmt = $conn->prepare("SELECT COUNT(*) FROM sppd WHERE YEAR(tanggal_terbit) = ?");
        $stmt->bind_param("s", $tahun);
        $stmt->execute();
        $stmt->bind_result($jumlah);
        $stmt->fetch();
        $stmt->close();

        $urutan = str_pad($jumlah + 1, 3, '0', STR_PAD_LEFT);
        $nomor_sppd = "SPPD/{$urutan}/{$tahun}";

        $stmtInsert = $conn->prepare("INSERT INTO sppd (id_pengajuan, nomor_sppd, tanggal_terbit, catatan) VALUES (?, ?, ?, ?)");
        $stmtInsert->bind_param("isss", $input['id_pengajuan'], $nomor_sppd, $input['tanggal_terbit'], $input['catatan']);

        if ($stmtInsert->execute()) {
            header("Location: " . BASE_URL . "/modules/shared/sppd/index.php?msg=added&obj=sppd");
            exit;
        } else {
            header("Location: " . BASE_URL . "/modules/shared/sppd/add.php?msg=failed&obj=sppd");
            exit;
        }
    }
}

// === Hitung Nomor SPPD jika tanggal_terbit sudah dipilih (untuk tampilan awal)
if ($input['tanggal_terbit']) {
    $tahun = date('Y', strtotime($input['tanggal_terbit']));
    $stmt = $conn->prepare("SELECT COUNT(*) FROM sppd WHERE YEAR(tanggal_terbit) = ?");
    $stmt->bind_param("s", $tahun);
    $stmt->execute();
    $stmt->bind_result($jumlah);
    $stmt->fetch();
    $stmt->close();
    $urutan = str_pad($jumlah + 1, 3, '0', STR_PAD_LEFT);
    $nomor_sppd = "SPPD/{$urutan}/{$tahun}";
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
                <form method="POST">
                    <div class="mb-3">
                        <label for="id_pengajuan" class="form-label">Pilih Pengajuan</label>
                        <select name="id_pengajuan" id="id_pengajuan" class="form-select" required>
                            <option value="">-- Pilih Pengajuan --</option>
                            <?php while ($row = $pengajuan->fetch_assoc()): ?>
                                <option value="<?= $row['id'] ?>" <?= $input['id_pengajuan'] == $row['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['nama']) ?>
                                    - <?= htmlspecialchars($row['tujuan']) ?>
                                    (<?= date('d-m-Y', strtotime($row['tanggal_berangkat'])) ?>)
                                    - SPT: <?= htmlspecialchars($row['nomor_spt']) ?>
                                </option>

                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Terbit</label>
                        <input type="date" name="tanggal_terbit" class="form-control" required value="<?= htmlspecialchars($input['tanggal_terbit']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nomor SPPD (Otomatis)</label>
                        <input type="text" class="form-control" value="<?= $nomor_sppd ?: 'Akan dibuat otomatis saat disimpan' ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="catatan" rows="3" class="form-control"
                            placeholder="Contoh: Laporan hasil kunjungan diserahkan paling lambat 3 hari setelah kembali."><?= htmlspecialchars($input['catatan'] ?? '') ?></textarea>

                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">
                            <i class="fe fe-save me-1"></i> Simpan
                        </button>
                        <a href="<?= BASE_URL ?>/modules/shared/sppd/index.php" class="btn btn-secondary">Batal</a>
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
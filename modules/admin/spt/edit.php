<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Edit Surat Perintah Tugas (SPT)';
$role = $_SESSION['role'] ?? '';
$id_user = $_SESSION['id_user'] ?? 0;

// Hanya admin
if ($role !== 'admin') {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

// Validasi ID
$id = (int) ($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM spt WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data || $data['status'] !== 'draft') {
    header("Location: " . BASE_URL . "/modules/shared/spt/index.php?msg=invalid&obj=spt");
    exit;
}

$error = '';
$id_pengajuan = $data['id_pengajuan'];

// Info pengajuan
$stmtP = $conn->prepare("
    SELECT peg.nama, p.tujuan, p.tanggal_berangkat
    FROM pengajuan_perjalanan p
    JOIN pegawai peg ON p.id_pegawai = peg.id
    WHERE p.id = ?
");
$stmtP->bind_param("i", $id_pengajuan);
$stmtP->execute();
$peng = $stmtP->get_result()->fetch_assoc();

// Daftar kepala
$kepala = $conn->query("SELECT id, nama, jabatan FROM kepala");

// Jika disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor_spt = trim($_POST['nomor_spt']);
    $tanggal_spt = $_POST['tanggal_spt'];
    $transportasi = trim($_POST['transportasi']);
    $id_kepala = $_POST['ditandatangani_oleh'] ?? '';

    // Penandatangan NULL-safe
    $penandatangan_id = $id_kepala !== '' ? (int)$id_kepala : NULL;

    // Status otomatis
    $status_spt = $penandatangan_id ? 'ditandatangani' : 'draft';

    // Ambil data
    $stmtData = $conn->prepare("SELECT keperluan, tanggal_berangkat, tanggal_kembali FROM pengajuan_perjalanan WHERE id = ?");
    $stmtData->bind_param("i", $id_pengajuan);
    $stmtData->execute();
    $pengajuanData = $stmtData->get_result()->fetch_assoc();

    if (!$pengajuanData) {
        $error = "Pengajuan tidak ditemukan.";
    } else {
        $maksud_perjalanan = $pengajuanData['keperluan'];
        $lama = (new DateTime($pengajuanData['tanggal_berangkat']))->diff(new DateTime($pengajuanData['tanggal_kembali']))->days + 1;
        $lama_perjalanan = $lama . ' hari';

        $stmt = $conn->prepare("UPDATE spt SET 
            nomor_spt = ?, tanggal_spt = ?, maksud_perjalanan = ?, 
            lama_perjalanan = ?, transportasi = ?, ditandatangani_oleh = ?, status = ?
            WHERE id = ?");
        $stmt->bind_param(
            "sssssssi",
            $nomor_spt,
            $tanggal_spt,
            $maksud_perjalanan,
            $lama_perjalanan,
            $transportasi,
            $penandatangan_id,
            $status_spt,
            $id
        );

        if ($stmt->execute()) {
            header("Location: " . BASE_URL . "/modules/shared/spt/index.php?msg=updated&obj=spt");
            exit;
        } else {
            $error = "Gagal memperbarui data.";
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
        <h4 class="mt-3 mb-4"><?= htmlspecialchars($pageTitle) ?></h4>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card custom-card shadow-sm">
            <div class="card-body">
                <form method="POST">
                    <!-- Info Pengajuan -->
                    <div class="mb-3">
                        <label class="form-label">Pengajuan</label>
                        <input type="text" class="form-control"
                            value="<?= $peng['nama'] ?> - Tujuan Ke(<?= $peng['tujuan'] ?>) - (<?= date('d-m-Y', strtotime($peng['tanggal_berangkat'])) ?>)" readonly>
                        <input type="hidden" name="id_pengajuan" value="<?= $id_pengajuan ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nomor SPT</label>
                        <input type="text" name="nomor_spt" class="form-control"
                            value="<?= htmlspecialchars($data['nomor_spt']) ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal SPT</label>
                        <input type="date" name="tanggal_spt" class="form-control"
                            value="<?= htmlspecialchars($data['tanggal_spt']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Maksud Perjalanan</label>
                        <textarea class="form-control" rows="3" readonly><?= htmlspecialchars($data['maksud_perjalanan']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Lama Perjalanan</label>
                        <input type="text" class="form-control"
                            value="<?= htmlspecialchars($data['lama_perjalanan']) ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Transportasi</label>
                        <input type="text" name="transportasi" class="form-control"
                            value="<?= htmlspecialchars($data['transportasi']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ditandatangani Oleh</label>
                        <select name="ditandatangani_oleh" class="form-select">
                            <option value="">-- Pilih Kepala --</option>
                            <?php while ($k = $kepala->fetch_assoc()): ?>
                                <option value="<?= $k['id'] ?>"
                                    <?= $data['ditandatangani_oleh'] == $k['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['nama']) ?> - <?= htmlspecialchars($k['jabatan']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <small class="text-muted">Kosongkan jika belum ingin ditandatangani.</small>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-success">
                            <i class="fe fe-save me-1"></i> Simpan Perubahan
                        </button>
                        <a href="<?= BASE_URL ?>/modules/shared/spt/index.php" class="btn btn-secondary">Batal</a>
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
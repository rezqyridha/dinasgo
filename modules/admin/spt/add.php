<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Tambah Surat Perintah Tugas (SPT)';
$role = $_SESSION['role'] ?? '';
$id_user = $_SESSION['id_user'] ?? 0;

// Validasi role
if ($role !== 'admin') {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

$error = '';
$input = [
    'id_pengajuan' => '',
    'nomor_spt' => '',
    'tanggal_spt' => '',
    'ditandatangani_oleh' => ''
];

// Ambil pengajuan yg disetujui & belum punya SPT
$pengajuan = $conn->query("
    SELECT p.id, peg.nama, p.tujuan, p.tanggal_berangkat, p.keperluan
    FROM pengajuan_perjalanan p
    JOIN pegawai peg ON p.id_pegawai = peg.id
    WHERE p.status = 'disetujui'
      AND p.id NOT IN (SELECT id_pengajuan FROM spt)
    ORDER BY p.tanggal_berangkat DESC
");

// Ambil daftar kepala
$kepala = $conn->query("SELECT id, nama, nip, jabatan FROM kepala ORDER BY nama ASC");

// Generate nomor SPT otomatis
$sptCount = $conn->query("SELECT COUNT(*) AS total FROM spt")->fetch_assoc()['total'] ?? 0;
$formattedNumber = str_pad($sptCount + 1, 3, '0', STR_PAD_LEFT);
$tahun = date('Y');
$autoNomorSPT = "SPT-$formattedNumber/$tahun";

if ($input['nomor_spt'] === '') {
    $input['nomor_spt'] = $autoNomorSPT;
}

// Handle submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($input as $key => $_) {
        $input[$key] = trim($_POST[$key] ?? '');
    }

    if (in_array('', [$input['id_pengajuan'], $input['nomor_spt'], $input['tanggal_spt']])) {
        $error = "Semua field wajib diisi.";
    } else {
        // Ambil detail pengajuan
        $stmtP = $conn->prepare("SELECT keperluan, tanggal_berangkat, tanggal_kembali FROM pengajuan_perjalanan WHERE id = ?");
        $stmtP->bind_param("i", $input['id_pengajuan']);
        $stmtP->execute();
        $pengajuanData = $stmtP->get_result()->fetch_assoc();

        $maksud_perjalanan = $pengajuanData['keperluan'];
        $lama = (new DateTime($pengajuanData['tanggal_berangkat']))->diff(new DateTime($pengajuanData['tanggal_kembali']))->days + 1;
        $lama_perjalanan = $lama . ' hari';
        $transportasi = $_POST['transportasi'] ?? '';

        // FK penandatangan aman NULL
        $penandatangan_id = !empty($input['ditandatangani_oleh']) ? (int)$input['ditandatangani_oleh'] : NULL;

        // Status otomatis
        $status_spt = $penandatangan_id ? 'ditandatangani' : 'draft';

        $stmt = $conn->prepare("
            INSERT INTO spt 
            (id_pengajuan, nomor_spt, tanggal_spt, maksud_perjalanan, lama_perjalanan, transportasi, ditandatangani_oleh, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        // NULL-safe binding
        $stmt->bind_param(
            "isssssis",
            $input['id_pengajuan'],
            $input['nomor_spt'],
            $input['tanggal_spt'],
            $maksud_perjalanan,
            $lama_perjalanan,
            $transportasi,
            $penandatangan_id,
            $status_spt
        );

        if ($stmt->execute()) {
            header("Location: " . BASE_URL . "/modules/shared/spt/index.php?msg=added&obj=spt");
            exit;
        } else {
            header("Location: " . BASE_URL . "/modules/shared/spt/index.php?msg=failed&obj=spt");
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

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card custom-card shadow-sm">
            <div class="card-body">
                <form method="POST">
                    <!-- Pengajuan -->
                    <div class="mb-3">
                        <label class="form-label">Pilih Pengajuan</label>
                        <select name="id_pengajuan" class="form-select" required onchange="fetchPengajuan(this.value)">
                            <option value="">-- Pilih Pengajuan --</option>
                            <?php while ($row = $pengajuan->fetch_assoc()): ?>
                                <option value="<?= $row['id'] ?>" <?= $input['id_pengajuan'] == $row['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['nama']) ?> - <?= htmlspecialchars($row['tujuan']) ?> (<?= date('d-m-Y', strtotime($row['tanggal_berangkat'])) ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Nomor SPT -->
                    <div class="mb-3">
                        <label class="form-label">Nomor SPT</label>
                        <input type="text" name="nomor_spt" class="form-control" value="<?= htmlspecialchars($input['nomor_spt']) ?>" readonly>
                    </div>

                    <!-- Tanggal -->
                    <div class="mb-3">
                        <label class="form-label">Tanggal SPT</label>
                        <input type="date" name="tanggal_spt" class="form-control" value="<?= htmlspecialchars($input['tanggal_spt']) ?>" required>
                    </div>

                    <!-- Maksud -->
                    <div class="mb-3">
                        <label class="form-label">Maksud Perjalanan</label>
                        <textarea class="form-control" id="maksud_perjalanan" rows="3" readonly></textarea>
                    </div>

                    <!-- Lama -->
                    <div class="mb-3">
                        <label class="form-label">Lama Perjalanan</label>
                        <input type="text" id="lama_perjalanan" class="form-control" readonly>
                    </div>

                    <!-- Transportasi -->
                    <div class="mb-3">
                        <label class="form-label">Transportasi</label>
                        <input type="text" name="transportasi" id="transportasi" class="form-control" required>
                    </div>

                    <!-- Penandatangan -->
                    <div class="mb-3">
                        <label class="form-label">Ditandatangani Oleh</label>
                        <select name="ditandatangani_oleh" class="form-select">
                            <option value="">-- Pilih Pejabat Penandatangan --</option>
                            <?php while ($row = $kepala->fetch_assoc()): ?>
                                <option value="<?= $row['id'] ?>" <?= $input['ditandatangani_oleh'] == $row['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['nama']) ?> - <?= htmlspecialchars($row['jabatan']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary"><i class="fe fe-save me-1"></i> Simpan</button>
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

<script>
    function fetchPengajuan(id) {
        if (!id) return;

        document.getElementById('maksud_perjalanan').value = '';
        document.getElementById('lama_perjalanan').value = '';
        document.getElementById('transportasi').value = '';

        fetch(`ajax_get_pengajuan.php?id=${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('maksud_perjalanan').value = data.keperluan;
                    document.getElementById('lama_perjalanan').value = data.lama;
                    document.getElementById('transportasi').value = data.transportasi;
                }
            });
    }
</script>
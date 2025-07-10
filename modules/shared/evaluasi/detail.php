<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Detail Evaluasi Perjalanan';

$role = $_SESSION['role'] ?? '';
$id_user = $_SESSION['id_user'] ?? 0;

// Role yang boleh akses
$allowed_roles = ['pegawai', 'atasan', 'admin'];
if (!in_array($role, $allowed_roles)) {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

// Validasi ID
$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: index.php?msg=invalid&obj=evaluasi");
    exit;
}

// Ambil data evaluasi + relasi
$query = "
    SELECT ep.*, 
           pp.tujuan, pp.tanggal_berangkat, pp.tanggal_kembali,
           peg.nama AS nama_pegawai, peg.nip, peg.jabatan
    FROM evaluasi_perjalanan ep
    JOIN pengajuan_perjalanan pp ON ep.id_pengajuan = pp.id
    JOIN pegawai peg ON ep.id_pegawai = peg.id
    WHERE ep.id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    header("Location: index.php?msg=notfound&obj=evaluasi");
    exit;
}

// Jika pegawai, pastikan hanya lihat miliknya
if ($role === 'pegawai') {
    $stmt = $conn->prepare("SELECT id FROM pegawai WHERE id_user = ?");
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $stmt->bind_result($id_pegawai);
    $stmt->fetch();
    $stmt->close();

    if ($id_pegawai != $data['id_pegawai']) {
        header("Location: " . BASE_URL . "/unauthorized.php");
        exit;
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
            <a href="index.php" class="btn btn-secondary btn-sm mt-4">
                <i class="fa fa-arrow-left"></i>
                Kembali
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">

                <div class="row mb-2">
                    <div class="col-md-3 fw-bold">Nama Pegawai</div>
                    <div class="col-md-9"><?= htmlspecialchars($data['nama_pegawai']) ?></div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-3 fw-bold">NIP</div>
                    <div class="col-md-9"><?= htmlspecialchars($data['nip']) ?></div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-3 fw-bold">Jabatan</div>
                    <div class="col-md-9"><?= htmlspecialchars($data['jabatan']) ?></div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-3 fw-bold">Tujuan Perjalanan</div>
                    <div class="col-md-9"><?= htmlspecialchars($data['tujuan']) ?></div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-3 fw-bold">Periode</div>
                    <div class="col-md-9"><?= date('d-m-Y', strtotime($data['tanggal_berangkat'])) . ' s.d. ' . date('d-m-Y', strtotime($data['tanggal_kembali'])) ?></div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-3 fw-bold">Status</div>
                    <div class="col-md-9">
                        <span class="badge bg-<?=
                                                $data['status'] === 'draft' ? 'secondary' : ($data['status'] === 'diajukan' ? 'warning' : ($data['status'] === 'disetujui' ? 'success' : ($data['status'] === 'ditolak' ? 'danger' : ($data['status'] === 'selesai' ? 'info' : 'secondary'))))
                                                ?>">
                            <?= ucfirst($data['status']) ?>
                        </span>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-3 fw-bold">Kendala</div>
                    <div class="col-md-9"><?= nl2br(htmlspecialchars($data['kendala'] ?? '-')) ?></div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-3 fw-bold">Hasil</div>
                    <div class="col-md-9"><?= nl2br(htmlspecialchars($data['hasil'] ?? '-')) ?></div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-3 fw-bold">Saran</div>
                    <div class="col-md-9"><?= nl2br(htmlspecialchars($data['saran'] ?? '-')) ?></div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3 fw-bold">Lampiran</div>
                    <div class="col-md-9">
                        <?php if ($data['lampiran']): ?>
                            <a href="<?= BASE_URL ?>/uploads/evaluasi/<?= htmlspecialchars($data['lampiran']) ?>" target="_blank">Unduh Lampiran</a>
                        <?php else: ?>
                            Tidak ada lampiran.
                        <?php endif; ?>
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
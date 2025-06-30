<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Detail SPPD';
$role = $_SESSION['role'] ?? '';
if (!in_array($role, ['admin', 'atasan'])) {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

$id = $_GET['id'] ?? 0;
$id = (int)$id;

// Ambil data lengkap SPPD
$stmt = $conn->prepare("
    SELECT sp.*, 
           pp.tujuan, pp.tanggal_berangkat, pp.tanggal_kembali, pp.keperluan, pp.estimasi_biaya,
           spt.nomor_spt, spt.tanggal_spt, spt.maksud_perjalanan, spt.lama_perjalanan, spt.transportasi,
           peg.nama AS nama_pegawai, peg.jabatan
    FROM sppd sp
    JOIN pengajuan_perjalanan pp ON sp.id_pengajuan = pp.id
    JOIN spt ON pp.id = spt.id_pengajuan
    JOIN pegawai peg ON pp.id_pegawai = peg.id
    WHERE sp.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    header("Location: index.php?msg=notfound&obj=sppd");
    exit;
}

require_once LAYOUTS_PATH . '/head.php';
require_once LAYOUTS_PATH . '/header.php';
require_once LAYOUTS_PATH . '/topbar.php';
require_once LAYOUTS_PATH . '/sidebar.php';
?>

<div class="main-content app-content">
    <div class="container-fluid">
        <h4 class="mb-4"><?= htmlspecialchars($pageTitle) ?></h4>

        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="250">Nomor SPPD</th>
                        <td>: <?= htmlspecialchars($data['nomor_sppd']) ?></td>
                    </tr>
                    <tr>
                        <th>Tanggal Terbit</th>
                        <td>: <?= date('d-m-Y', strtotime($data['tanggal_terbit'])) ?></td>
                    </tr>
                    <tr>
                        <th>Nama Pegawai</th>
                        <td>: <?= htmlspecialchars($data['nama_pegawai']) ?> (<?= htmlspecialchars($data['jabatan']) ?>)</td>
                    </tr>
                    <tr>
                        <th>Tujuan Perjalanan</th>
                        <td>: <?= htmlspecialchars($data['tujuan']) ?></td>
                    </tr>
                    <tr>
                        <th>Tanggal Berangkat</th>
                        <td>: <?= date('d-m-Y', strtotime($data['tanggal_berangkat'])) ?></td>
                    </tr>
                    <tr>
                        <th>Tanggal Kembali</th>
                        <td>: <?= date('d-m-Y', strtotime($data['tanggal_kembali'])) ?></td>
                    </tr>
                    <tr>
                        <th>Keperluan</th>
                        <td>: <?= nl2br(htmlspecialchars($data['keperluan'])) ?></td>
                    </tr>
                    <tr>
                        <th>Estimasi Biaya</th>
                        <td>: Rp <?= number_format($data['estimasi_biaya'], 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <th>Nomor SPT</th>
                        <td>: <?= htmlspecialchars($data['nomor_spt']) ?></td>
                    </tr>
                    <tr>
                        <th>Tanggal SPT</th>
                        <td>: <?= date('d-m-Y', strtotime($data['tanggal_spt'])) ?></td>
                    </tr>
                    <tr>
                        <th>Maksud Perjalanan</th>
                        <td>: <?= nl2br(htmlspecialchars($data['maksud_perjalanan'])) ?></td>
                    </tr>
                    <tr>
                        <th>Lama Perjalanan</th>
                        <td>: <?= htmlspecialchars($data['lama_perjalanan']) ?></td>
                    </tr>
                    <tr>
                        <th>Transportasi</th>
                        <td>: <?= htmlspecialchars($data['transportasi']) ?></td>
                    </tr>
                    <tr>
                        <th>Catatan Tambahan</th>
                        <td>: <?= nl2br(htmlspecialchars($data['catatan'])) ?></td>
                    </tr>
                </table>

                <div class="d-flex justify-content-between m-3">
                    <a href="<?= BASE_URL ?>/modules/shared/sppd/index.php" class="btn btn-secondary">
                        <i class="fe fe-arrow-left me-1"></i> Kembali
                    </a>
                    <?php if ($role === 'admin'): ?>
                        <a href="cetak_sppd.php?id=<?= $id ?>" class="btn btn-purple" target="_blank">
                            <i class="fe fe-printer me-1"></i> Cetak SPPD
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once LAYOUTS_PATH . '/footer.php';
require_once LAYOUTS_PATH . '/scripts.php';
?>
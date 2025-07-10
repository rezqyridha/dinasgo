<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Detail Rincian Biaya';
$role = $_SESSION['role'] ?? '';
$id_user = $_SESSION['id_user'] ?? 0;

// RBAC: Hanya admin, pegawai, bendahara
$canRead = in_array($role, ['admin', 'pegawai', 'bendahara']);
if (!$canRead) {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header("Location: " . BASE_URL . "/modules/shared/rincian_biaya/index.php?msg=invalid");
    exit;
}

// Ambil data rincian
$stmt = $conn->prepare("
    SELECT rb.*, 
           p.tujuan, p.tanggal_berangkat, 
           u.nama AS nama_pembuat,
           peg.nama AS nama_pegawai
    FROM rincian_biaya rb
    JOIN pengajuan_perjalanan p ON rb.id_pengajuan = p.id
    JOIN user u ON rb.dibuat_oleh = u.id
    JOIN pegawai peg ON p.id_pegawai = peg.id
    WHERE rb.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$rincian = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$rincian) {
    header("Location: " . BASE_URL . "/modules/shared/rincian_biaya/index.php?msg=notfound");
    exit;
}

// Pegawai hanya boleh melihat miliknya (id_pemilik)
if ($role === 'pegawai' && $rincian['id_pemilik'] != $id_user) {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

// Ambil detail rincian
$details = $conn->query("
    SELECT * FROM rincian_biaya_detail
    WHERE id_rincian = $id
");

require_once LAYOUTS_PATH . '/head.php';
require_once LAYOUTS_PATH . '/header.php';
require_once LAYOUTS_PATH . '/topbar.php';
require_once LAYOUTS_PATH . '/sidebar.php';
?>

<div class="main-content app-content">
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
            <h4 class="mb-0">Detail Rincian Biaya</h4>
            <a href="<?= BASE_URL ?>/modules/shared/rincian_biaya/index.php" class="btn btn-sm btn-secondary">
                <i class="fe fe-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="card shadow-sm border">
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <th style="width: 180px;">Nomor Rincian</th>
                        <td>: <?= htmlspecialchars($rincian['nomor_rincian']) ?></td>
                    </tr>
                    <tr>
                        <th>Tanggal Rincian</th>
                        <td>: <?= date('d-m-Y', strtotime($rincian['tanggal_rincian'])) ?></td>
                    </tr>
                    <tr>
                        <th>Nama Pegawai</th>
                        <td>: <?= htmlspecialchars($rincian['nama_pegawai']) ?></td>
                    </tr>
                    <tr>
                        <th>Pembuat</th>
                        <td>: <?= htmlspecialchars($rincian['nama_pembuat']) ?></td>
                    </tr>
                    <tr>
                        <th>Tujuan</th>
                        <td>: <?= htmlspecialchars($rincian['tujuan']) ?> (<?= date('d-m-Y', strtotime($rincian['tanggal_berangkat'])) ?>)</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>:
                            <?php
                            $badge = match ($rincian['status']) {
                                'draft' => 'secondary',
                                'diajukan' => 'warning',
                                'disetujui' => 'success',
                                'ditolak' => 'danger',
                                'selesai' => 'primary',
                                default => 'light'
                            };
                            ?>
                            <span class="badge bg-<?= $badge ?>"><?= ucfirst($rincian['status']) ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th>Total Biaya</th>
                        <td>: <strong>Rp <?= number_format($rincian['jumlah_total'], 0, ',', '.') ?></strong></td>
                    </tr>
                </table>

                <div class="text-end">
                    <?php if (in_array($role, ['admin', 'bendahara']) && in_array($rincian['status'], ['disetujui', 'selesai'])): ?>
                        <a href="cetak_rincian.php?id=<?= $rincian['id'] ?>" target="_blank" class="btn btn-primary ms-2">
                            <i class="fe fe-printer me-1"></i> Cetak Bukti
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card mt-4 shadow-sm border">
            <div class="card-header bg-light fw-bold">Detail Rincian Biaya</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered m-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Jenis Biaya</th>
                                <th>Keterangan</th>
                                <th>Jumlah</th>
                                <th>Satuan</th>
                                <th>Harga Satuan</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1;
                            while ($d = $details->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($d['jenis_biaya']) ?></td>
                                    <td><?= htmlspecialchars($d['keterangan']) ?></td>
                                    <td><?= $d['jumlah'] ?></td>
                                    <td><?= htmlspecialchars($d['satuan']) ?></td>
                                    <td>Rp <?= number_format($d['harga_satuan'], 0, ',', '.') ?></td>
                                    <td>Rp <?= number_format($d['total'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endwhile; ?>
                            <?php if ($no === 1): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Tidak ada detail.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<?php
require_once LAYOUTS_PATH . '/footer.php';
require_once LAYOUTS_PATH . '/scripts.php';
?>
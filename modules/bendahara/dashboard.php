<?php
require_once __DIR__ . '/../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Dashboard Bendahara';
$id_user = $_SESSION['id_user'];

require_once LAYOUTS_PATH . '/head.php';
require_once LAYOUTS_PATH . '/header.php';
require_once LAYOUTS_PATH . '/topbar.php';
require_once LAYOUTS_PATH . '/sidebar.php';

// Ambil data statistik
$totalRincianDiajukan = $conn->query("SELECT COUNT(*) as total FROM rincian_biaya WHERE status = 'diajukan'")->fetch_assoc()['total'];
$totalPencairan = $conn->query("SELECT COUNT(*) as total FROM pencairan_dana")->fetch_assoc()['total'];
$totalDanaDicairkan = $conn->query("SELECT SUM(jumlah_dana) as total FROM pencairan_dana")->fetch_assoc()['total'] ?? 0;
?>

<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex align-items-center justify-content-between mb-4 mt-4">
            <h4 class="mb-0">Selamat datang, Bendahara!</h4>
        </div>

        <div class="row">
            <!-- Kartu statistik -->
            <div class="col-md-4">
                <div class="card border shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted">Rincian Biaya Diajukan</h6>
                        <h3 class="text-primary"><?= $totalRincianDiajukan ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted">Jumlah Transaksi Pencairan</h6>
                        <h3 class="text-success"><?= $totalPencairan ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted">Total Dana Dicairkan</h6>
                        <h3 class="text-danger">Rp <?= number_format($totalDanaDicairkan, 0, ',', '.') ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Verifikasi -->
        <div class="mt-5">
            <h5 class="mb-3">Rincian Biaya yang Menunggu Persetujuan</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nomor</th>
                            <th>Tanggal</th>
                            <th>Jumlah Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "
                            SELECT id, nomor_rincian, tanggal_rincian, jumlah_total, status
                            FROM rincian_biaya
                            WHERE status = 'diajukan'
                            ORDER BY tanggal_rincian DESC
                        ";
                        $res = $conn->query($query);
                        if ($res->num_rows > 0):
                            while ($row = $res->fetch_assoc()):
                        ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['nomor_rincian']) ?></td>
                                    <td><?= date('d-m-Y', strtotime($row['tanggal_rincian'])) ?></td>
                                    <td>Rp <?= number_format($row['jumlah_total'], 0, ',', '.') ?></td>
                                    <td><span class="badge bg-warning"><?= ucfirst($row['status']) ?></span></td>
                                    <td>
                                        <button class="btn btn-sm btn-purple" onclick="verifikasiRincian(<?= $row['id'] ?>)">
                                            <i class="fe fe-check-circle me-1"></i> Verifikasi
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile;
                        else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Tidak ada rincian yang diajukan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function verifikasiRincian(id) {
        Swal.fire({
            title: 'Verifikasi Rincian Biaya',
            text: 'Pilih tindakan yang ingin dilakukan:',
            icon: 'question',
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: 'Setujui',
            denyButtonText: 'Tolak',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `<?= BASE_URL ?>/modules/bendahara/rincian_biaya/verifikasi.php?id=${id}&action=setujui`;
            } else if (result.isDenied) {
                window.location.href = `<?= BASE_URL ?>/modules/bendahara/rincian_biaya/verifikasi.php?id=${id}&action=tolak`;
            }
        });
    }
</script>

<?php
require_once LAYOUTS_PATH . '/footer.php';
require_once LAYOUTS_PATH . '/scripts.php'; // pastikan ini sudah include notifier.js
?>
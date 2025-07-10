<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Daftar Evaluasi Perjalanan';

$role = $_SESSION['role'] ?? '';
$id_user = $_SESSION['id_user'] ?? 0;

// Role yang diizinkan
$canRead = in_array($role, ['pegawai', 'atasan', 'admin']);
if (!$canRead) {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

// Ambil data sesuai role
if ($role === 'pegawai') {
    // Ambil id_pegawai dari user login
    $stmt = $conn->prepare("SELECT id FROM pegawai WHERE id_user = ?");
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $stmt->bind_result($id_pegawai);
    $stmt->fetch();
    $stmt->close();

    $id_pegawai = $id_pegawai ?? 0;

    $query = "
        SELECT ep.*, 
               pp.tujuan, 
               peg.nama AS nama_pegawai 
        FROM evaluasi_perjalanan ep
        JOIN pengajuan_perjalanan pp ON ep.id_pengajuan = pp.id
        JOIN pegawai peg ON ep.id_pegawai = peg.id
        WHERE ep.id_pegawai = ?
        ORDER BY ep.id DESC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_pegawai);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Atasan & Admin melihat semua
    $query = "
        SELECT ep.*, 
               pp.tujuan, 
               peg.nama AS nama_pegawai 
        FROM evaluasi_perjalanan ep
        JOIN pengajuan_perjalanan pp ON ep.id_pengajuan = pp.id
        JOIN pegawai peg ON ep.id_pegawai = peg.id
        ORDER BY ep.id DESC
    ";
    $result = $conn->query($query);
}

require_once LAYOUTS_PATH . '/head.php';
require_once LAYOUTS_PATH . '/header.php';
require_once LAYOUTS_PATH . '/topbar.php';
require_once LAYOUTS_PATH . '/sidebar.php';
?>

<div class="main-content app-content">
    <div class="container-fluid">

        <div class="card custom-card mt-5 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title mb-0"><?= htmlspecialchars($pageTitle) ?></div>
                <?php if ($role === 'pegawai'): ?>
                    <a href="<?= BASE_URL ?>/modules/pegawai/evaluasi/add.php" class="btn btn-sm btn-primary">
                        <i class="fe fe-plus me-1"></i> Tambah Evaluasi
                    </a>
                <?php endif; ?>
            </div>

            <div class="card-body">
                <div class="mb-3 d-flex justify-content-end">
                    <input type="text" id="searchBox" class="form-control w-25" placeholder="Cari evaluasi...">
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle mb-0" id="tabel-evaluasi">
                        <thead class="table-primary">
                            <tr>
                                <th>No</th>
                                <th>Nama Pegawai</th>
                                <th>Tujuan</th>
                                <th>Status</th>
                                <th>Lampiran</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php $no = 1;
                                while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['nama_pegawai']) ?></td>
                                        <td><?= htmlspecialchars($row['tujuan']) ?></td>
                                        <td>
                                            <span class="badge bg-<?=
                                                                    $row['status'] === 'draft' ? 'secondary' : ($row['status'] === 'diajukan' ? 'warning' : ($row['status'] === 'disetujui' ? 'success' : ($row['status'] === 'ditolak' ? 'danger' : ($row['status'] === 'selesai' ? 'info' : 'secondary'))))
                                                                    ?>">
                                                <?= ucfirst($row['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($row['lampiran']): ?>
                                                <a href="<?= BASE_URL ?>/uploads/evaluasi/<?= $row['lampiran'] ?>" target="_blank">Lihat</a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-list d-flex justify-content-center">
                                                <a href="detail.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info me-1" title="Lihat Detail">
                                                    <i class="fe fe-eye"></i>
                                                </a>
                                                <?php if ($role === 'pegawai' && $row['status'] === 'draft'): ?>
                                                    <a href="<?= BASE_URL ?>/modules/pegawai/evaluasi/ajukan.php?id=<?= $row['id'] ?>"
                                                        class="btn btn-sm btn-success me-1"
                                                        title="Ajukan Evaluasi">
                                                        <i class="fe fe-send"></i>
                                                    </a>
                                                <?php endif; ?>

                                                <?php if ($row['status'] === 'draft' && ($role === 'pegawai' || $role === 'admin')): ?>
                                                    <a href="<?= BASE_URL ?>/modules/<?= $role ?>/evaluasi/edit.php?id=<?= $row['id'] ?>"
                                                        class="btn btn-sm btn-warning me-1" title="Edit">
                                                        <i class="fe fe-edit"></i>
                                                    </a>
                                                    <button onclick="confirmDelete('<?= BASE_URL ?>/modules/<?= $role ?>/evaluasi/delete.php?id=<?= $row['id'] ?>')"
                                                        class="btn btn-sm btn-danger" title="Hapus">
                                                        <i class="fe fe-trash-2"></i>
                                                    </button>
                                                <?php endif; ?>

                                                <?php if ($role === 'atasan' && $row['status'] === 'diajukan'): ?>
                                                    <button
                                                        class="btn btn-sm btn-success me-1"
                                                        onclick="verifikasiAtasan(<?= $row['id'] ?>)">
                                                        <i class="fe fe-check-square" title="Verifikasi"></i>
                                                    </button>
                                                <?php endif; ?>

                                                <?php if ($role === 'admin' && $row['status'] === 'disetujui'): ?>
                                                    <button
                                                        class="btn btn-sm btn-primary me-1"
                                                        onclick="finalisasiEvaluasi(<?= $row['id'] ?>)">
                                                        <i class="fe fe-flag" title="Finalisasi"></i>
                                                    </button>
                                                <?php endif; ?>

                                                <?php if (in_array($role, ['admin', 'atasan']) && in_array($row['status'], ['selesai', 'disetujui'])): ?>
                                                    <a href="<?= BASE_URL ?>/modules/shared/evaluasi/cetak_evaluasi.php?id=<?= $row['id'] ?>"
                                                        class="btn btn-sm btn-purple me-1"
                                                        target="_blank" title="Cetak Evaluasi">
                                                        <i class="fe fe-printer"></i>
                                                    </a>
                                                <?php endif; ?>

                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Belum ada data evaluasi.</td>
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

<script>
    document.getElementById("searchBox").addEventListener("keyup", function() {
        const filter = this.value.toLowerCase();
        document.querySelectorAll("#tabel-evaluasi tbody tr").forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
        });
    });

    function verifikasiAtasan(id) {
        Swal.fire({
            title: 'Verifikasi Evaluasi',
            text: 'Pilih tindakan:',
            icon: 'question',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonText: 'Setujui',
            denyButtonText: 'Tolak',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `<?= BASE_URL ?>/modules/atasan/evaluasi/verifikasi.php?id=${id}&aksi=setujui`;
            } else if (result.isDenied) {
                window.location.href = `<?= BASE_URL ?>/modules/atasan/evaluasi/verifikasi.php?id=${id}&aksi=tolak`;
            }
        });
    }

    function finalisasiEvaluasi(id) {
        Swal.fire({
            title: 'Finalisasi Evaluasi?',
            text: 'Pastikan evaluasi sudah diverifikasi dengan benar.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Finalisasi',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `<?= BASE_URL ?>/modules/admin/evaluasi/finalisasi.php?id=${id}`;
            }
        });
    }
</script>
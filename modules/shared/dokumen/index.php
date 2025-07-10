<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Manajemen Dokumen';
$role = $_SESSION['role'];
$id_user = $_SESSION['id_user'];

//  Proteksi RBAC: hanya Admin, Pegawai, Atasan
$canRead = in_array($role, ['admin', 'pegawai', 'atasan']);
if (!$canRead) {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

//  Ambil data sesuai role
if ($role === 'pegawai') {
    // Ambil hanya dokumen milik pegawai ini
    $query = "
        SELECT d.*, pp.tujuan, peg.nama AS nama_pegawai
        FROM dokumen d
        JOIN pengajuan_perjalanan pp ON d.id_pengajuan = pp.id
        JOIN pegawai peg ON pp.id_pegawai = peg.id
        WHERE d.id_user = ?
        ORDER BY d.uploaded_at DESC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $result = $stmt->get_result();
} elseif ($role === 'atasan') {
    // Ambil semua dokumen pegawainya (bawahan)
    $query = "
        SELECT d.*, pp.tujuan, peg.nama AS nama_pegawai
        FROM dokumen d
        JOIN pengajuan_perjalanan pp ON d.id_pengajuan = pp.id
        JOIN pegawai peg ON pp.id_pegawai = peg.id
        WHERE peg.id_atasan = ?
        ORDER BY d.uploaded_at DESC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_user);  // id_user di sini adalah atasan login
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Admin: lihat semua
    $query = "
        SELECT d.*, pp.tujuan, peg.nama AS nama_pegawai
        FROM dokumen d
        JOIN pengajuan_perjalanan pp ON d.id_pengajuan = pp.id
        JOIN pegawai peg ON pp.id_pegawai = peg.id
        ORDER BY d.uploaded_at DESC
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
                <?php if (in_array($role, ['admin', 'pegawai'])): ?>
                    <a href="<?= BASE_URL ?>/modules/shared/dokumen/add.php" class="btn btn-sm btn-primary">
                        <i class="fe fe-plus me-1"></i> Upload Dokumen
                    </a>
                <?php endif; ?>
            </div>

            <div class="card-body">
                <div class="mb-3 d-flex justify-content-end">
                    <input type="text" id="searchBox" class="form-control w-25" placeholder="Cari Dokumen...">
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0" id="tabel-dokumen">
                        <thead class="table-primary">
                            <tr>
                                <th>No</th>
                                <th>Nama File</th>
                                <th>Jenis</th>
                                <th>Pengajuan</th>
                                <th>Nama Pegawai</th>
                                <th>Uploaded At</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php $no = 1;
                                while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/uploads/dokumen/<?= (int)$row['id_pengajuan'] ?>/<?= htmlspecialchars($row['nama_file']) ?>" ...>
                                                <?= htmlspecialchars($row['nama_file']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php
                                            // Ubah 'surat_tugas' menjadi 'Surat Tugas', 'bukti_biaya' menjadi 'Bukti Biaya'
                                            $jenis = str_replace('_', ' ', $row['jenis']);
                                            echo htmlspecialchars(ucwords($jenis));
                                            ?>
                                        </td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/modules/shared/dokumen/detail_pengajuan.php?id_pengajuan=<?= (int)$row['id_pengajuan'] ?>"
                                                title="Lihat Detail Pengajuan">
                                                <?= htmlspecialchars($row['tujuan']) ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($row['nama_pegawai']) ?></td>
                                        <td><?= date('d-m-Y H:i', strtotime($row['uploaded_at'])) ?></td>
                                        <td class="text-center">
                                            <div class="btn-list d-flex justify-content-center">
                                                <a href="<?= BASE_URL ?>/modules/shared/dokumen/detail.php?id=<?= $row['id'] ?>"
                                                    class="btn btn-sm btn-info me-1" title="Detail">
                                                    <i class="fe fe-eye"></i>
                                                </a>
                                                <?php if ($role === 'admin'): ?>
                                                    <a href="<?= BASE_URL ?>/modules/admin/dokumen/edit.php?id=<?= $row['id'] ?>"
                                                        class="btn btn-sm btn-warning me-1" title="Edit">
                                                        <i class="fe fe-edit"></i>
                                                    </a>
                                                    <button onclick="confirmDelete('<?= BASE_URL ?>/modules/admin/dokumen/delete.php?id=<?= $row['id'] ?>')"
                                                        class="btn btn-sm btn-danger" title="Hapus">
                                                        <i class="fe fe-trash-2"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Belum ada dokumen diunggah.</td>
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
        document.querySelectorAll("#tabel-dokumen tbody tr").forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
        });
    });
</script>
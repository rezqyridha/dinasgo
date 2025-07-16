<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once AUTH_PATH . '/session.php';
require_once CONFIG_PATH . '/koneksi.php';

$pageTitle = 'Manajemen Pegawai';

// Validasi role admin saja
if ($_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

// Query data pegawai
$query = "SELECT * FROM pegawai ORDER BY nama ASC";
$result = $conn->query($query);

// Jika gagal ambil data
if (!$result) {
    die("Gagal mengambil data pegawai: " . $conn->error);
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
                <div class="card-title mb-0">Manajemen Data Pegawai</div>
                <a href="add.php" class="btn btn-sm btn-primary">
                    <i class="fe fe-user-plus me-1"></i> Tambah Pegawai
                </a>
            </div>

            <div class="card-body">
                <div class="mb-3 d-flex justify-content-end">
                    <input type="text" id="searchBox" class="form-control w-25" placeholder="Cari pegawai...">
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle table-striped" id="tabel-pegawai">
                        <thead class="table-primary">
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>NIP</th>
                                <th>Jabatan</th>
                                <th>No. HP</th>
                                <th>Email</th>
                                <th>Alamat</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php $no = 1;
                                while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['nama']) ?></td>
                                        <td><?= htmlspecialchars($row['nip']) ?></td>
                                        <td><?= htmlspecialchars($row['jabatan']) ?></td>
                                        <td><?= htmlspecialchars($row['no_hp']) ?></td>
                                        <td><?= htmlspecialchars($row['email']) ?></td>
                                        <td><?= htmlspecialchars($row['alamat']) ?></td>
                                        <td class="text-center">
                                            <div class="btn-list d-flex justify-content-center">
                                                <!-- Tombol Edit -->
                                                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning me-1" title="Edit">
                                                    <i class="fe fe-edit"></i>
                                                </a>

                                                <!-- Tombol Hapus -->
                                                <button onclick="confirmDelete('delete.php?id=<?= $row['id'] ?>')" class="btn btn-sm btn-danger me-1" title="Hapus">
                                                    <i class="fe fe-trash-2"></i>
                                                </button>

                                                <?php if (empty($row['id_user'])): ?>
                                                    <!-- Tombol Buat User (Modal Trigger) -->
                                                    <button type="button"
                                                        class="btn btn-sm btn-success"
                                                        title="Buat Akun User"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalBuatUser"
                                                        data-idpeg="<?= $row['id'] ?>"
                                                        data-namapeg="<?= htmlspecialchars($row['nama']) ?>">
                                                        <i class="fe fe-user-plus"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-dark border d-flex align-items-center justify-content-center" style="height:32px; min-width:90px;">
                                                        Sudah Punya Akun
                                                    </span>
                                                <?php endif; ?>

                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Belum ada data pegawai.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Modal Buat User -->
                <div class="modal fade" id="modalBuatUser" tabindex="-1" aria-labelledby="modalBuatUserLabel" aria-hidden="true">
                    <div class="modal-dialog modal-md">
                        <div class="modal-content">
                            <form action="buat_user.php" method="POST">
                                <div class="modal-header">
                                    <h5 class="modal-title">Buat Akun User Pegawai</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="id_pegawai" id="idPegawai">
                                    <div class="mb-3">
                                        <label for="namaPegawai" class="form-label">Nama Pegawai</label>
                                        <input type="text" class="form-control" id="namaPegawai" name="nama" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" name="username" id="username" class="form-control" required maxlength="50" placeholder="Masukkan username">
                                    </div>
                                    <div class="mb-3 position-relative">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="input-group">
                                            <input type="password" name="password" id="password" class="form-control" required minlength="5" placeholder="Minimal 5 karakter">
                                            <button type="button" class="btn btn-outline-secondary" tabindex="-1" id="togglePassword" title="Lihat Password">
                                                <i class="fe fe-eye" id="eyeIcon"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="role" class="form-label">Role</label>
                                        <select name="role" id="role" class="form-select" required>
                                            <option value="" hidden>-- Pilih Role --</option>
                                            <option value="pegawai">Pegawai</option>
                                            <option value="atasan">Atasan</option>
                                            <option value="bendahara">Bendahara</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary"><i class="fe fe-save"></i> Simpan</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                </div>
                            </form>
                        </div>
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

<script>
    // Live search
    document.getElementById("searchBox").addEventListener("keyup", function() {
        const filter = this.value.toLowerCase();
        document.querySelectorAll("#tabel-pegawai tbody tr").forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
        });
    });

    // Auto isi modal Buat User
    var modalBuatUser = document.getElementById('modalBuatUser');
    modalBuatUser.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var idPegawai = button.getAttribute('data-idpeg');
        var namaPegawai = button.getAttribute('data-namapeg');

        modalBuatUser.querySelector('#idPegawai').value = idPegawai;
        modalBuatUser.querySelector('#namaPegawai').value = namaPegawai;
    });

    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        const pwInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        if (pwInput.type === 'password') {
            pwInput.type = 'text';
            eyeIcon.className = 'fe fe-eye-off';
        } else {
            pwInput.type = 'password';
            eyeIcon.className = 'fe fe-eye';
        }
    });
</script>
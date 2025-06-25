<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once AUTH_PATH . '/session.php';
require_once CONFIG_PATH . '/koneksi.php';

$pageTitle = 'Manajemen User';

if ($_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/modules/{$_SESSION['role']}/dashboard.php?msg=unauthorized");
    exit;
}

$query = "SELECT * FROM user ORDER BY role, nama";
$result = $conn->query($query);
$currentId = $_SESSION['id_user'];

function getRoleBadgeClass($role)
{
    return match (strtolower($role)) {
        'admin'     => 'bg-danger',
        'pegawai'   => 'bg-primary',
        'atasan'    => 'bg-info',
        'bendahara' => 'bg-success',
        default     => 'bg-secondary'
    };
}
?>

<!DOCTYPE html>
<html lang="id">
<?php include_once LAYOUTS_PATH . '/head.php'; ?>

<body>
    <div class="page">
        <?php
        include_once LAYOUTS_PATH . '/header.php';
        include_once LAYOUTS_PATH . '/topbar.php';
        include_once LAYOUTS_PATH . '/sidebar.php';
        ?>

        <div class="main-content app-content">
            <div class="container-fluid">

                <div class="card custom-card mt-5 shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="card-title mb-0">Manajemen User</div>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                            <i class="fe fe-user-plus me-1"></i> Tambah User
                        </button>
                    </div>

                    <div class="card-body">
                        <div class="mb-3 d-flex justify-content-end">
                            <input type="text" id="searchBox" class="form-control w-25" placeholder="Cari user...">
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped align-middle mb-0" id="tabel-user">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1;
                                    while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($row['nama']) ?></td>
                                            <td><?= htmlspecialchars($row['username']) ?></td>
                                            <td><span class="badge <?= getRoleBadgeClass($row['role']) ?>"><?= ucfirst($row['role']) ?></span></td>
                                            <td><span class="badge <?= $row['status'] === 'aktif' ? 'bg-success' : 'bg-danger' ?>"><?= ucfirst($row['status']) ?></span></td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning" title="Edit"><i class="fe fe-edit"></i></a>
                                                    <?php if ($row['id'] != $currentId): ?>
                                                        <form action="reset.php" method="POST" class="d-inline">
                                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                            <button type="submit" onclick="resetPassword(this.form); return false;" class="btn btn-secondary" title="Reset Password"><i class="fe fe-refresh-cw"></i></button>
                                                        </form>
                                                        <button onclick="confirmDelete('delete.php?id=<?= $row['id'] ?>')" class="btn btn-danger" title="Hapus"><i class="fe fe-trash-2"></i></button>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-dark border">Diri Sendiri</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                    <?php if ($result->num_rows === 0): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Belum ada pengguna terdaftar.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal Tambah -->
        <div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <form action="add.php" method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title">Tambah User Baru</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" name="username" id="username" class="form-control" required maxlength="50" placeholder="Masukkan username">
                            </div>
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama</label>
                                <input type="text" name="nama" id="nama" class="form-control" required maxlength="100" placeholder="Masukkan nama lengkap">
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
                                    <option value="admin">Admin</option>
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

        <?php
        include_once LAYOUTS_PATH . '/footer.php';
        include_once LAYOUTS_PATH . '/scripts.php';
        ?>
    </div>

    <script>
        document.getElementById("searchBox").addEventListener("keyup", function() {
            const filter = this.value.toLowerCase();
            document.querySelectorAll("#tabel-user tbody tr").forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.getElementById('togglePassword');
            const eyeIcon = document.getElementById('eyeIcon');
            toggleBtn.addEventListener('click', function() {
                const type = passwordInput.type === 'password' ? 'text' : 'password';
                passwordInput.type = type;
                eyeIcon.className = type === 'password' ? 'fe fe-eye' : 'fe fe-eye-off';
            });
        });

        function resetPassword(form) {
            Swal.fire({
                title: 'Reset Password?',
                text: 'Password user akan diatur ulang ke default. Lanjutkan?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Reset',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>
</body>

</html>
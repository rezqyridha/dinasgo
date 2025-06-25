<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once AUTH_PATH . '/session.php';
require_once CONFIG_PATH . '/koneksi.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php?msg=unauthorized");
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: index.php?msg=invalid");
    exit;
}

// Ambil data lama
$stmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    header("Location: index.php?msg=invalid");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $nama     = trim($_POST['nama'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = trim($_POST['role'] ?? '');
    $status   = trim($_POST['status'] ?? 'aktif');

    if ($username === '' || $nama === '' || $role === '') {
        header("Location: edit.php?id=$id&msg=kosong");
        exit;
    }

    // Cek duplikat username (kecuali dirinya sendiri)
    $cek = $conn->prepare("SELECT id FROM user WHERE username = ? AND id != ?");
    $cek->bind_param("si", $username, $id);
    $cek->execute();
    $cek->store_result();
    if ($cek->num_rows > 0) {
        $cek->close();
        header("Location: edit.php?id=$id&msg=duplicate");
        exit;
    }
    $cek->close();

    if ($password !== '') {
        $stmt = $conn->prepare("UPDATE user SET username=?, password=?, nama=?, role=?, status=? WHERE id=?");
        $stmt->bind_param("sssssi", $username, $password, $nama, $role, $status, $id);
    } else {
        $stmt = $conn->prepare("UPDATE user SET username=?, nama=?, role=?, status=? WHERE id=?");
        $stmt->bind_param("ssssi", $username, $nama, $role, $status, $id);
    }


    if ($stmt->execute()) {
        header("Location: index.php?msg=updated");
    } else {
        header("Location: index.php?msg=error");
    }
    exit;
}
$pageTitle = 'Edit Pengguna';

require_once LAYOUTS_PATH . '/head.php';
require_once LAYOUTS_PATH . '/header.php';
require_once LAYOUTS_PATH . '/topbar.php';
require_once LAYOUTS_PATH . '/sidebar.php';
?>

<div class="main-content app-content">
    <div class="container-fluid">
        <div class="card custom-card mt-5 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title mb-0">Edit Pengguna</div>
                <a href="index.php" class="btn btn-sm btn-dark">← Kembali</a>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" name="username" id="username" class="form-control" required value="<?= htmlspecialchars($data['username']) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" id="nama" class="form-control" required value="<?= htmlspecialchars($data['nama']) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password (kosongkan jika tidak ingin diubah)</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password" class="form-control" placeholder="Biarkan kosong jika tidak ingin mengubah password">
                            <button type="button" class="btn btn-outline-secondary" id="togglePassword" tabindex="-1">
                                <i class="fe fe-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select name="role" id="role" class="form-select" required>
                            <option value="admin" <?= $data['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="atasan" <?= $data['role'] === 'atasan' ? 'selected' : '' ?>>Atasan</option>
                            <option value="bendahara" <?= $data['role'] === 'bendahara' ? 'selected' : '' ?>>Bendahara</option>
                            <option value="pegawai" <?= $data['role'] === 'pegawai' ? 'selected' : '' ?>>Pegawai</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="aktif" <?= $data['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="nonaktif" <?= $data['status'] === 'nonaktif' ? 'selected' : '' ?>>Non Aktif</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fe fe-save"></i> Simpan Perubahan
                    </button>
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
    document.addEventListener('DOMContentLoaded', function() {
        const passwordInput = document.getElementById('password');
        const toggleBtn = document.getElementById('togglePassword');
        const eyeIcon = document.getElementById('eyeIcon');
        toggleBtn.addEventListener('click', function() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fe-eye');
                eyeIcon.classList.add('fe-eye-off');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fe-eye-off');
                eyeIcon.classList.add('fe-eye');
            }
        });
    });
</script>
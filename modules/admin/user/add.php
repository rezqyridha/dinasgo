<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once BASE_PATH . '/auth/session.php';
require_once BASE_PATH . '/config/koneksi.php';

$pageTitle = 'Tambah User';
$current_page = str_replace(BASE_PATH, '', __FILE__);
$isAdmin = ($_SESSION['role'] === 'admin');

if (!$isAdmin) {
    header("Location: " . BASE_URL . "/modules/{$_SESSION['role']}/dashboard.php?msg=unauthorized");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    if ($nama === '' || $username === '' || $password === '' || $role === '') {
        $error = 'Semua field wajib diisi.';
    } else {
        $cek = $conn->prepare("SELECT id FROM user WHERE username = ? OR nama = ?");
        $cek->bind_param("ss", $username, $nama);
        $cek->execute();
        $cek->store_result();

        if ($cek->num_rows > 0) {
            header("Location: index.php?msg=duplicate");
            exit;
        } else {
            // Tidak pakai hash karena masih tahap pengujian
            $stmt = $conn->prepare("INSERT INTO user (nama, username, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nama, $username, $password, $role);

            if ($stmt->execute()) {
                header("Location: index.php?msg=added");
                exit;
            } else {
                header("Location: index.php?msg=error");
            }
        }
        $cek->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<?php include_once BASE_PATH . '/layouts/head.php'; ?>

<body>
    <div class="page">
        <?php
        include_once BASE_PATH . '/layouts/header.php';
        include_once BASE_PATH . '/layouts/topbar.php';
        include_once BASE_PATH . '/layouts/sidebar.php';
        ?>

        <div class="main-content app-content">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0"><?= htmlspecialchars($pageTitle) ?></h2>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="card custom-card">
                    <div class="card-body">
                        <form method="POST" autocomplete="off">
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Lengkap</label>
                                <input type="text" name="nama" class="form-control" required placeholder="Masukkan nama lengkap">
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required placeholder="Masukkan username unik">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="text" name="password" class="form-control" required placeholder="Bisa dilihat untuk pengujian">
                            </div>
                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select name="role" class="form-select" required>
                                    <option value="">-- Pilih Role --</option>
                                    <option value="admin">Admin</option>
                                    <option value="pegawai">Pegawai</option>
                                    <option value="atasan">Atasan</option>
                                    <option value="bendahara">Bendahara</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <a href="index.php" class="btn btn-secondary">Batal</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php
        include_once BASE_PATH . '/layouts/footer.php';
        include_once BASE_PATH . '/layouts/scripts.php';
        ?>
    </div>
</body>

</html>
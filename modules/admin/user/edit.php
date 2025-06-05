<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once BASE_PATH . '/auth/session.php';
require_once BASE_PATH . '/config/koneksi.php';

$pageTitle = 'Edit User';
$id = $_GET['id'] ?? null;
$isAdmin = ($_SESSION['role'] === 'admin');

// Validasi role
if (!$isAdmin || !is_numeric($id)) {
    header("Location: index.php?msg=unauthorized");
    exit;
}

// Ambil data user lama
$stmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("Location: index.php?msg=invalid");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = $_POST['role'] ?? 'pegawai';
    $status = $_POST['status'] ?? 'aktif';

    if ($username === '' || $password === '') {
        header("Location: edit.php?id=$id&msg=kosong");
        exit;
    }

    // Cek apakah tidak ada perubahan pada field yang wajib
    $noChange = (
        $username === $user['username'] &&
        $password === $user['password'] &&
        $status === $user['status']
    );

    if ($noChange) {
        header("Location: edit.php?id=$id&msg=nochange");
        exit;
    }

    // Cek duplikat username dan nama, kecuali milik sendiri
    $cek = $conn->prepare("SELECT id FROM user WHERE username = ? AND nama = ? AND id != ?");
    $cek->bind_param("ssi", $username, $nama, $id);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows > 0) {
        $cek->close();
        header("Location: edit.php?id=$id&msg=duplicate");
        exit;
    }
    $cek->close();

    // Update data
    $update = $conn->prepare("UPDATE user SET nama = ?, username = ?, password = ?, role = ?, status = ? WHERE id = ?");
    $update->bind_param("sssssi", $nama, $username, $password, $role, $status, $id);

    if ($update->execute()) {
        header("Location: index.php?msg=updated");
    } else {
        header("Location: edit.php?id=$id&msg=failed");
    }
    exit;
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
                    <h2 class="mt-3 mb-0"><?= htmlspecialchars($pageTitle) ?></h2>
                </div>

                <div class="card custom-card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="nama" name="nama"
                                    value="<?= htmlspecialchars($user['nama']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username"
                                    value="<?= htmlspecialchars($user['username']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="text" class="form-control" id="password" name="password"
                                    value="<?= htmlspecialchars($user['password']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" name="role" id="role" required>
                                    <?php if ($user['role'] === 'admin'): ?>
                                        <option value="admin" selected>Admin</option>
                                    <?php endif; ?>

                                    <option value="pegawai" <?= $user['role'] === 'pegawai' ? 'selected' : '' ?>>Pegawai</option>
                                    <option value="atasan" <?= $user['role'] === 'atasan' ? 'selected' : '' ?>>Atasan</option>
                                    <option value="bendahara" <?= $user['role'] === 'bendahara' ? 'selected' : '' ?>>Bendahara</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" name="status" id="status">
                                    <option value="aktif" <?= $user['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                    <option value="nonaktif" <?= $user['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
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
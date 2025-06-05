<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once BASE_PATH . '/auth/session.php';
require_once BASE_PATH . '/config/koneksi.php';

$pageTitle = 'Edit Pegawai';
$isAdmin = ($_SESSION['role'] === 'admin');

if (!$isAdmin) {
    header("Location: index.php?msg=unauthorized");
    exit;
}

$id = $_GET['id'] ?? '';
if (!is_numeric($id)) {
    header("Location: index.php?msg=invalid");
    exit;
}

// Ambil data pegawai
$stmt = $conn->prepare("SELECT * FROM pegawai WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$pegawai = $result->fetch_assoc();
if (!$pegawai) {
    header("Location: index.php?msg=invalid");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nip = trim($_POST['nip'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');

    if ($nip === '' || $nama === '' || $jabatan === '') {
        header("Location: edit.php?id=$id&msg=kosong");
        exit;
    }

    if (!ctype_digit($nip)) {
        header("Location: edit.php?id=$id&msg=invalid");
        exit;
    }

    if (!empty($no_hp) && !ctype_digit($no_hp)) {
        header("Location: edit.php?id=$id&msg=invalid");
        exit;
    }

    // Cek apakah ada perubahan
    if (
        $nip === $pegawai['nip'] &&
        $nama === $pegawai['nama'] &&
        $jabatan === $pegawai['jabatan'] &&
        $no_hp === $pegawai['no_hp'] &&
        $email === $pegawai['email'] &&
        $alamat === $pegawai['alamat']
    ) {
        header("Location: edit.php?id=$id&msg=nochange");
        exit;
    }

    // Proses update
    $stmt = $conn->prepare("UPDATE pegawai SET nip = ?, nama = ?, jabatan = ?, no_hp = ?, email = ?, alamat = ? WHERE id = ?");
    $stmt->bind_param("ssssssi", $nip, $nama, $jabatan, $no_hp, $email, $alamat, $id);
    if ($stmt->execute()) {
        header("Location: index.php?msg=updated");
        exit;
    } else {
        header("Location: edit.php?id=$id&msg=failed");
        exit;
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
                    <h2 class="mt-3 mb-0"><?= htmlspecialchars($pageTitle) ?></h2>
                </div>

                <div class="card custom-card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="nip" class="form-label">NIP</label>
                                <input type="text" class="form-control" id="nip" name="nip"
                                    value="<?= htmlspecialchars($pegawai['nip']) ?>" required placeholder="Masukkan Nomor Induk Pegawai" pattern="\d+">
                            </div>
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama</label>
                                <input type="text" class="form-control" id="nama" name="nama"
                                    value="<?= htmlspecialchars($pegawai['nama']) ?>" required placeholder="Masukkan Nama Lengkap">
                            </div>
                            <div class="mb-3">
                                <label for="jabatan" class="form-label">Jabatan</label>
                                <input type="text" class="form-control" id="jabatan" name="jabatan"
                                    value="<?= htmlspecialchars($pegawai['jabatan']) ?>" required placeholder="Masukkan Jabatan Pegawai">
                            </div>
                            <div class="mb-3">
                                <label for="no_hp" class="form-label">No HP</label>
                                <input type="text" class="form-control" id="no_hp" name="no_hp"
                                    value="<?= htmlspecialchars($pegawai['no_hp']) ?>" placeholder="Contoh: 081234567890" pattern="\d+">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?= htmlspecialchars($pegawai['email']) ?>" placeholder="Contoh: email@domain.com">
                            </div>
                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3" placeholder="Masukkan alamat lengkap pegawai"><?= htmlspecialchars($pegawai['alamat']) ?></textarea>
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
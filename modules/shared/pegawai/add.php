<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once BASE_PATH . '/auth/session.php';
require_once BASE_PATH . '/config/koneksi.php';

$pageTitle = 'Tambah Pegawai';
$isAdmin = ($_SESSION['role'] === 'admin');

if (!$isAdmin) {
    header("Location: " . BASE_URL . "/modules/shared/pegawai/index.php?msg=unauthorized");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nip = trim($_POST['nip'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $id_user = $_SESSION['user_id'];

    if ($nip === '' || $nama === '' || $jabatan === '') {
        $error = 'Field NIP, Nama, dan Jabatan wajib diisi.';
    } elseif (!ctype_digit($nip)) {
        $error = 'NIP hanya boleh berisi angka.';
    } elseif (!empty($no_hp) && !ctype_digit($no_hp)) {
        $error = 'Nomor HP hanya boleh berisi angka.';
    } else {
        $cek = $conn->prepare("SELECT id FROM pegawai WHERE nip = ? AND nama = ?");
        $cek->bind_param("ss", $nip, $nama);
        $cek->execute();
        $cek->store_result();

        if ($cek->num_rows > 0) {
            header("Location: add.php?msg=duplicate");
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO pegawai (id_user, nip, nama, jabatan, no_hp, email, alamat) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $id_user, $nip, $nama, $jabatan, $no_hp, $email, $alamat);

        if ($stmt->execute()) {
            header("Location: index.php?msg=added");
            exit;
        } else {
            header("Location: add.php?msg=failed");
            exit;
        }
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

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="card custom-card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="nip" class="form-label">NIP</label>
                                <input type="text" class="form-control" id="nip" name="nip"
                                    placeholder="Masukkan Nomor Induk Pegawai" pattern="\d+" title="Hanya boleh angka" required>
                            </div>
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama</label>
                                <input type="text" class="form-control" id="nama" name="nama" placeholder="Masukkan Nama Lengkap" required>
                            </div>
                            <div class="mb-3">
                                <label for="jabatan" class="form-label">Jabatan</label>
                                <input type="text" class="form-control" id="jabatan" name="jabatan" placeholder="Masukkan Jabatan Pegawai" required>
                            </div>
                            <div class="mb-3">
                                <label for="no_hp" class="form-label">No HP</label>
                                <input type="text" class="form-control" id="no_hp" name="no_hp"
                                    placeholder="Contoh: 081234567890" pattern="\d+" title="Hanya boleh angka">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Contoh: email@domain.com">
                            </div>
                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3" placeholder="Masukkan alamat lengkap pegawai"></textarea>
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
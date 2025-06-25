<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once AUTH_PATH . '/session.php';
require_once CONFIG_PATH . '/koneksi.php';

$pageTitle = 'Tambah Pegawai';

// Validasi hanya admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama    = trim($_POST['nama'] ?? '');
    $nip     = trim($_POST['nip'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? '');
    $no_hp   = trim($_POST['no_hp'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $alamat  = trim($_POST['alamat'] ?? '');

    if ($nama === '' || $nip === '' || $jabatan === '') {
        header("Location: add.php?msg=kosong");
        exit;
    }

    // Cek duplikat NIP
    $cek = $conn->prepare("SELECT id FROM pegawai WHERE nip = ?");
    $cek->bind_param("s", $nip);
    $cek->execute();
    $cek->store_result();
    if ($cek->num_rows > 0) {
        $cek->close();
        header("Location: add.php?msg=duplicate");
        exit;
    }
    $cek->close();

    // Cek apakah nama sama dengan nama di user
    $id_user = null;
    $cekUser = $conn->prepare("SELECT id FROM user WHERE nama = ? LIMIT 1");
    $cekUser->bind_param("s", $nama);
    $cekUser->execute();
    $hasil = $cekUser->get_result();
    if ($hasil && $hasil->num_rows > 0) {
        $id_user = $hasil->fetch_assoc()['id'];
    }
    $cekUser->close();

    // Insert data
    $stmt = $conn->prepare("INSERT INTO pegawai (id_user, nama, nip, jabatan, no_hp, email, alamat) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $id_user, $nama, $nip, $jabatan, $no_hp, $email, $alamat);

    if ($stmt->execute()) {
        header("Location: index.php?msg=added");
    } else {
        header("Location: index.php?msg=error");
    }
    exit;
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
                <div class="card-title mb-0">Tambah Pegawai</div>
                <a href="index.php" class="btn btn-sm btn-dark"><i class="fe fe-arrow-left me-1"></i> Kembali</a>
            </div>

            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama" id="nama" class="form-control" required maxlength="100" placeholder="Masukkan nama lengkap">
                    </div>

                    <div class="mb-3">
                        <label for="nip" class="form-label">NIP <span class="text-danger">*</span></label>
                        <input type="text" name="nip" id="nip" class="form-control" required maxlength="30" placeholder="Masukkan NIP">
                    </div>

                    <div class="mb-3">
                        <label for="jabatan" class="form-label">Jabatan <span class="text-danger">*</span></label>
                        <input type="text" name="jabatan" id="jabatan" class="form-control" required maxlength="50" placeholder="Masukkan jabatan">
                    </div>

                    <div class="mb-3">
                        <label for="no_hp" class="form-label">No. HP</label>
                        <input type="text" name="no_hp" id="no_hp" class="form-control" maxlength="20" placeholder="Contoh: 08123456789">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" maxlength="100" placeholder="Masukkan email">
                    </div>

                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat</label>
                        <textarea name="alamat" id="alamat" class="form-control" rows="2" maxlength="255" placeholder="Masukkan alamat"></textarea>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-primary"><i class="fe fe-save me-1"></i> Simpan</button>
                        <a href="index.php" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once LAYOUTS_PATH . '/footer.php';
require_once LAYOUTS_PATH . '/scripts.php';
?>
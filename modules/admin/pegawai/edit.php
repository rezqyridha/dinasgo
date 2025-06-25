<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once AUTH_PATH . '/session.php';
require_once CONFIG_PATH . '/koneksi.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php?msg=unauthorized&obj=pegawai");
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: index.php?msg=invalid&obj=pegawai");
    exit;
}

// Ambil data pegawai
$stmt = $conn->prepare("SELECT * FROM pegawai WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    header("Location: index.php?msg=invalid&obj=pegawai");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nip     = trim($_POST['nip'] ?? '');
    $nama    = trim($_POST['nama'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? '');
    $no_hp   = trim($_POST['no_hp'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $alamat  = trim($_POST['alamat'] ?? '');

    if ($nip === '' || $jabatan === '' || $nama === '') {
        header("Location: edit.php?id=$id&msg=kosong&obj=pegawai");
        exit;
    }

    // Tidak boleh ubah nama jika sudah punya id_user
    if (!is_null($data['id_user']) && $nama !== $data['nama']) {
        header("Location: edit.php?id=$id&msg=locked&obj=pegawai");
        exit;
    }

    // Deteksi tidak ada perubahan
    if (
        $nip === $data['nip'] &&
        $nama === $data['nama'] &&
        $jabatan === $data['jabatan'] &&
        $no_hp === $data['no_hp'] &&
        $email === $data['email'] &&
        $alamat === $data['alamat']
    ) {
        header("Location: edit.php?id=$id&msg=nochange&obj=pegawai");
        exit;
    }

    // Update data
    $stmt = $conn->prepare("UPDATE pegawai SET nip = ?, nama = ?, jabatan = ?, no_hp = ?, email = ?, alamat = ? WHERE id = ?");
    $stmt->bind_param("ssssssi", $nip, $nama, $jabatan, $no_hp, $email, $alamat, $id);

    if ($stmt->execute()) {
        header("Location: index.php?msg=updated&obj=pegawai");
    } else {
        header("Location: edit.php?id=$id&msg=failed&obj=pegawai");
    }
    exit;
}
$pageTitle = 'Edit Pegawai';
require_once LAYOUTS_PATH . '/head.php';
require_once LAYOUTS_PATH . '/header.php';
require_once LAYOUTS_PATH . '/topbar.php';
require_once LAYOUTS_PATH . '/sidebar.php';

?>

<div class="main-content app-content">
    <div class="container-fluid">
        <div class="card custom-card mt-5 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title mb-0">Edit Pegawai</div>
                <a href="index.php" class="btn btn-sm btn-dark">← Kembali</a>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="nip" class="form-label">NIP</label>
                        <input type="text" name="nip" id="nip" class="form-control" required maxlength="30"
                            value="<?= htmlspecialchars($data['nip']) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" id="nama" class="form-control" required maxlength="100"
                            value="<?= htmlspecialchars($data['nama']) ?>" <?= !is_null($data['id_user']) ? 'readonly' : '' ?>>
                        <?php if (!is_null($data['id_user'])): ?>
                            <small class="text-muted fst-italic">Nama dikunci karena sudah terhubung ke akun user.</small>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="jabatan" class="form-label">Jabatan</label>
                        <input type="text" name="jabatan" id="jabatan" class="form-control" required maxlength="100"
                            value="<?= htmlspecialchars($data['jabatan']) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="no_hp" class="form-label">No. HP</label>
                        <input type="text" name="no_hp" id="no_hp" class="form-control" maxlength="20"
                            value="<?= htmlspecialchars($data['no_hp']) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" maxlength="100"
                            value="<?= htmlspecialchars($data['email']) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat</label>
                        <textarea name="alamat" id="alamat" class="form-control" rows="2"><?= htmlspecialchars($data['alamat']) ?></textarea>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary"><i class="fe fe-save me-1"></i> Simpan Perubahan</button>
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
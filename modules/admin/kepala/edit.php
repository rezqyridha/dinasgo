<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Edit Kepala';
$role = $_SESSION['role'] ?? '';

// Hanya admin yang boleh akses
if ($role !== 'admin') {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

// Ambil ID kepala
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: index.php?msg=invalid_id&obj=kepala");
    exit;
}

// Ambil data kepala
$stmt = $conn->prepare("SELECT * FROM kepala WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$kepala = $result->fetch_assoc();
$stmt->close();

if (!$kepala) {
    header("Location: index.php?msg=not_found&obj=kepala");
    exit;
}

$error = '';
$input = [
    'nama' => $kepala['nama'],
    'nip' => $kepala['nip'],
    'jabatan' => $kepala['jabatan'],
    'tahun' => $kepala['tahun']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input['nama'] = trim($_POST['nama'] ?? '');
    $input['nip'] = trim($_POST['nip'] ?? '');
    $input['jabatan'] = trim($_POST['jabatan'] ?? '');
    $input['tahun'] = trim($_POST['tahun'] ?? '');

    // Validasi wajib
    if ($input['nama'] === '' || $input['nip'] === '' || $input['jabatan'] === '') {
        $error = "Nama, NIP, dan Jabatan wajib diisi.";
    } else {
        // Cek duplikat NIP kecuali diri sendiri
        $cek = $conn->prepare("SELECT id FROM kepala WHERE nip = ? AND id != ?");
        $cek->bind_param("si", $input['nip'], $id);
        $cek->execute();
        $cek->store_result();
        if ($cek->num_rows > 0) {
            $error = "NIP sudah digunakan oleh kepala lain.";
        } else {
            $stmt = $conn->prepare("
                UPDATE kepala SET nama = ?, nip = ?, jabatan = ?, tahun = ?
                WHERE id = ?
            ");
            $stmt->bind_param(
                "ssssi",
                $input['nama'],
                $input['nip'],
                $input['jabatan'],
                $input['tahun'],
                $id
            );

            if ($stmt->execute()) {
                header("Location: index.php?msg=updated&obj=kepala");
                exit;
            } else {
                $error = "Gagal memperbarui data kepala.";
            }
            $stmt->close();
        }
        $cek->close();
    }
}

require_once LAYOUTS_PATH . '/head.php';
require_once LAYOUTS_PATH . '/header.php';
require_once LAYOUTS_PATH . '/topbar.php';
require_once LAYOUTS_PATH . '/sidebar.php';
?>

<div class="main-content app-content">
    <div class="container-fluid">
        <h4 class="mb-4 mt-4"><?= htmlspecialchars($pageTitle) ?></h4>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nama Kepala <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" required maxlength="100"
                            value="<?= htmlspecialchars($input['nama']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">NIP <span class="text-danger">*</span></label>
                        <input type="text" name="nip" class="form-control" required maxlength="30"
                            value="<?= htmlspecialchars($input['nip']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jabatan <span class="text-danger">*</span></label>
                        <input type="text" name="jabatan" class="form-control" required maxlength="100"
                            value="<?= htmlspecialchars($input['jabatan']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tahun</label>
                        <input type="text" name="tahun" class="form-control" maxlength="4"
                            value="<?= htmlspecialchars($input['tahun']) ?>">
                        <small class="text-muted">Opsional, isi tahun jika diperlukan.</small>
                    </div>

                    <div class="mt-4 d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">
                            <i class="fe fe-save me-1"></i> Simpan Perubahan
                        </button>
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
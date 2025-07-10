<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

// Role check: Pegawai & Admin boleh upload tambahan
$role = $_SESSION['role'] ?? '';
$id_user = $_SESSION['id_user'] ?? 0;

if (!in_array($role, ['pegawai', 'admin'])) {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

// POST data
$id_pengajuan = (int) ($_POST['id_pengajuan'] ?? 0);
$jenis = trim($_POST['jenis'] ?? '');
$redirectTo = trim($_POST['redirect'] ?? '');

$allowed_jenis = ['surat_tugas', 'bukti_pengeluaran', 'sppd', 'lainnya'];

if ($id_pengajuan <= 0 || $jenis === '' || !in_array($jenis, $allowed_jenis) || empty($_FILES['file']['name'])) {
    header("Location: " . BASE_URL . "/modules/shared/dokumen/index.php?msg=invalid&obj=dokumen");
    exit;
}

// ✅ Cek FK kombinasi
$stmtCheck = $conn->prepare("SELECT 1 FROM dokumen WHERE id_pengajuan = ? AND jenis = ?");
$stmtCheck->bind_param("is", $id_pengajuan, $jenis);
$stmtCheck->execute();
$stmtCheck->store_result();
if ($stmtCheck->num_rows > 0) {
    $stmtCheck->close();
    header("Location: " . BASE_URL . "/modules/shared/dokumen/index.php?msg=duplicate&obj=dokumen");
    exit;
}
$stmtCheck->close();

// Upload settings
$upload_dir = dirname(__DIR__, 3) . "/uploads/dokumen/{$id_pengajuan}/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$allowed_ext = ['pdf', 'docx'];
$max_size = 5 * 1024 * 1024;

$ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowed_ext)) {
    header("Location: " . BASE_URL . "/modules/shared/dokumen/index.php?msg=invalidext&obj=dokumen");
    exit;
}

if ($_FILES['file']['size'] > $max_size) {
    header("Location: " . BASE_URL . "/modules/shared/dokumen/index.php?msg=toolarge&obj=dokumen");
    exit;
}

$tmpName = $_FILES['file']['tmp_name'];
$safeName = preg_replace("/[^A-Za-z0-9_\-\.]/", "_", basename($_FILES['file']['name']));
$fileName = time() . '_' . $safeName;
$targetPath = $upload_dir . $fileName;

if (move_uploaded_file($tmpName, $targetPath)) {
    $stmt = $conn->prepare("INSERT INTO dokumen (id_pengajuan, id_user, nama_file, jenis) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $id_pengajuan, $id_user, $fileName, $jenis);

    if ($stmt->execute()) {
        $lastInsertedId = $stmt->insert_id;
        $redirect = $redirectTo !== ''
            ? BASE_URL . '/modules/shared/dokumen/' . $redirectTo . '&msg=added&obj=dokumen'
            : BASE_URL . "/modules/shared/dokumen/detail.php?id={$lastInsertedId}&msg=added&obj=dokumen";
    } else {
        $redirect = BASE_URL . "/modules/shared/dokumen/index.php?msg=dberror&obj=dokumen";
    }
} else {
    $redirect = BASE_URL . "/modules/shared/dokumen/index.php?msg=movefail&obj=dokumen";
}

header("Location: " . $redirect);
exit;

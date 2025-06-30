<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

// Cek role
$role = $_SESSION['role'] ?? '';
$id_user = $_SESSION['id_user'] ?? 0;

if ($role !== 'pegawai') {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

// Ambil POST data
$id_pengajuan = (int) ($_POST['id_pengajuan'] ?? 0);
$jenis = trim($_POST['jenis'] ?? '');

if ($id_pengajuan <= 0 || $jenis === '' || empty($_FILES['files']['name'][0])) {
    header("Location: " . BASE_URL . "/modules/shared/dokumen/index.php?msg=invalid&obj=dokumen");
    exit;
}

// Siapkan folder upload
$upload_dir = dirname(__DIR__, 3) . "/uploads/dokumen/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$success = true;
$lastInsertedId = 0;

foreach ($_FILES['files']['name'] as $index => $name) {
    $tmpName = $_FILES['files']['tmp_name'][$index];
    $safeName = preg_replace("/[^A-Za-z0-9\_\-\.]/", "_", basename($name));
    $fileName = time() . '_' . $safeName;
    $targetPath = $upload_dir . $fileName;

    if (move_uploaded_file($tmpName, $targetPath)) {
        $stmt = $conn->prepare("INSERT INTO dokumen (id_pengajuan, id_user, nama_file, jenis) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $id_pengajuan, $id_user, $fileName, $jenis);

        if ($stmt->execute()) {
            $lastInsertedId = $stmt->insert_id;
        } else {
            $success = false;
            break;
        }
    } else {
        $success = false;
        break;
    }
}

// Redirect ke detail.php ID dokumen terakhir
if ($success) {
    $redirect = BASE_URL . "/modules/shared/dokumen/index.php?msg=added&obj=dokumen";
} else {
    $redirect = BASE_URL . "/modules/shared/dokumen/detail.php?id=" . $lastInsertedId . "&msg=failed&obj=dokumen";
}

header("Location: " . $redirect);
exit;

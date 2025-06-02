<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once BASE_PATH . '/auth/session.php';
require_once BASE_PATH . '/config/koneksi.php';

// Hanya admin yang boleh menghapus
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php?msg=unauthorized");
    exit;
}

// Validasi ID
$id = $_GET['id'] ?? '';
if (!ctype_digit($id)) {
    header("Location: index.php?msg=invalid");
    exit;
}

// Cek apakah data ada
$cek = $conn->prepare("SELECT id FROM pegawai WHERE id = ?");
$cek->bind_param("i", $id);
$cek->execute();
$cek->store_result();

if ($cek->num_rows === 0) {
    $cek->close();
    header("Location: index.php?msg=invalid");
    exit;
}
$cek->close();

// Lakukan penghapusan
$delete = $conn->prepare("DELETE FROM pegawai WHERE id = ?");
$delete->bind_param("i", $id);

if ($delete->execute()) {
    header("Location: index.php?msg=deleted");
} else {
    header("Location: index.php?msg=failed");
}
exit;

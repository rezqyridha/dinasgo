<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once BASE_PATH . '/auth/session.php';
require_once BASE_PATH . '/config/koneksi.php';

// Pastikan hanya admin yang bisa mengakses
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php?msg=unauthorized");
    exit;
}

// Validasi parameter ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php?msg=invalid");
    exit;
}

$id = (int)$_GET['id'];

// Cek apakah user ada
$cekUser = $conn->prepare("SELECT id FROM user WHERE id = ?");
$cekUser->bind_param("i", $id);
$cekUser->execute();
$cekUser->store_result();

if ($cekUser->num_rows === 0) {
    $cekUser->close();
    header("Location: index.php?msg=invalid");
    exit;
}
$cekUser->close();

// Cek relasi di tabel lain
$tables = ['pegawai', 'dokumen', 'notifikasi'];
foreach ($tables as $table) {
    $cekRelasi = $conn->prepare("SELECT 1 FROM $table WHERE id_user = ? LIMIT 1");
    $cekRelasi->bind_param("i", $id);
    $cekRelasi->execute();
    $cekRelasi->store_result();

    if ($cekRelasi->num_rows > 0) {
        $cekRelasi->close();
        header("Location: index.php?msg=fk_blocked");
        exit;
    }
    $cekRelasi->close();
}

// Jika aman, hapus user
$stmt = $conn->prepare("DELETE FROM user WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: index.php?msg=deleted");
} else {
    header("Location: index.php?msg=failed");
}
exit;

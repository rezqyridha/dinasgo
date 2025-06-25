<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once AUTH_PATH . '/session.php';
require_once CONFIG_PATH . '/koneksi.php';

// Validasi role
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php?msg=unauthorized&obj=pegawai");
    exit;
}

// Validasi ID
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: index.php?msg=invalid&obj=pegawai");
    exit;
}

// Ambil data lama
$stmt = $conn->prepare("SELECT nama FROM pegawai WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

// Jika data tidak ditemukan
if (!$data) {
    header("Location: index.php?msg=notfound&obj=pegawai");
    exit;
}

// Proses hapus
$stmt = $conn->prepare("DELETE FROM pegawai WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: index.php?msg=deleted&obj=pegawai");
} else {
    header("Location: index.php?msg=error&obj=pegawai");
}
$stmt->close();
exit;

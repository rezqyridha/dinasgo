<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$role = $_SESSION['role'] ?? '';
if ($role !== 'admin') {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: " . BASE_URL . "/modules/shared/dokumen/index.php?msg=invalid&obj=dokumen");
    exit;
}

// Ambil data file + folder pengajuan
$stmt = $conn->prepare("SELECT id_pengajuan, nama_file FROM dokumen WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($id_pengajuan, $nama_file);
$stmt->fetch();
$stmt->close();

if (!$nama_file) {
    header("Location: " . BASE_URL . "/modules/shared/dokumen/index.php?msg=notfound&obj=dokumen");
    exit;
}

// Hapus file fisik
$filePath = dirname(__DIR__, 3) . "/uploads/dokumen/{$id_pengajuan}/" . $nama_file;
if (file_exists($filePath)) {
    unlink($filePath);
}

// Hapus data di DB
$stmtDel = $conn->prepare("DELETE FROM dokumen WHERE id = ?");
$stmtDel->bind_param("i", $id);
if ($stmtDel->execute()) {
    header("Location: " . BASE_URL . "/modules/shared/dokumen/index.php?msg=deleted&obj=dokumen");
} else {
    header("Location: " . BASE_URL . "/modules/shared/dokumen/index.php?msg=failed&obj=dokumen");
}
exit;

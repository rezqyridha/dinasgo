<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once BASE_PATH . '/auth/session.php';
require_once BASE_PATH . '/config/koneksi.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/modules/" . $_SESSION['role'] . "/dashboard.php?msg=unauthorized");
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Ambil data SPT
$query = $conn->prepare("SELECT status FROM spt WHERE id = ?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();
$spt = $result->fetch_assoc();

if (!$spt) {
    echo "<script>alert('Data tidak ditemukan.'); window.location.href='" . BASE_URL . "/modules/shared/spt/index.php';</script>";
    exit;
}

// Validasi hanya status draft yang boleh dihapus
if ($spt['status'] !== 'draft') {
    echo "<script>alert('SPT tidak dapat dihapus karena sudah diproses.'); window.location.href='" . BASE_URL . "/modules/shared/spt/index.php';</script>";
    exit;
}

// Hapus
$stmt = $conn->prepare("DELETE FROM spt WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: " . BASE_URL . "/modules/shared/spt/index.php?msg=deleted");
    exit;
} else {
    echo "<script>alert('Gagal menghapus data.'); window.location.href='" . BASE_URL . "/modules/shared/spt/index.php';</script>";
}

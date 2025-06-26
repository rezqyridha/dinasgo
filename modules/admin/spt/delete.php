<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

// Hanya admin yang boleh akses
if ($_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/unauthorized.php?msg=unauthorized&obj=spt");
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Cek keberadaan data
$stmt = $conn->prepare("SELECT status FROM spt WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$spt = $result->fetch_assoc();

if (!$spt) {
    header("Location: " . BASE_URL . "/modules/shared/spt/index.php?msg=invalid&obj=spt");
    exit;
}

// Hanya bisa hapus jika status = draft
if ($spt['status'] !== 'draft') {
    header("Location: " . BASE_URL . "/modules/shared/spt/index.php?msg=locked&obj=spt");
    exit;
}

// Eksekusi hapus
$delete = $conn->prepare("DELETE FROM spt WHERE id = ?");
$delete->bind_param("i", $id);

if ($delete->execute()) {
    header("Location: " . BASE_URL . "/modules/shared/spt/index.php?msg=deleted&obj=spt");
    exit;
} else {
    header("Location: " . BASE_URL . "/modules/shared/spt/index.php?msg=error&obj=spt");
    exit;
}

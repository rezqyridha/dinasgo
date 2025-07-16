<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$role = $_SESSION['role'] ?? '';

// Hanya admin yang boleh akses
if ($role !== 'admin') {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

// Validasi ID kepala
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: index.php?msg=invalid_id&obj=kepala");
    exit;
}

// Cek data kepala ada
$stmt = $conn->prepare("SELECT id FROM kepala WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    header("Location: index.php?msg=not_found&obj=kepala");
    exit;
}
$stmt->close();

// Eksekusi hapus
$delete = $conn->prepare("DELETE FROM kepala WHERE id = ?");
$delete->bind_param("i", $id);

if ($delete->execute()) {
    header("Location: index.php?msg=deleted&obj=kepala");
} else {
    header("Location: index.php?msg=error&obj=kepala");
}
exit;

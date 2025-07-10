<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$role = $_SESSION['role'] ?? '';
$id_user = $_SESSION['id_user'] ?? 0;

// Hanya admin boleh finalisasi
if ($role !== 'admin') {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: " . BASE_URL . "/modules/shared/evaluasi/index.php?msg=invalid&obj=evaluasi");
    exit;
}

// Validasi status harus disetujui
$stmt = $conn->prepare("SELECT id FROM evaluasi_perjalanan WHERE id = ? AND status = 'disetujui'");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    header("Location: " . BASE_URL . "/modules/shared/evaluasi/index.php?msg=notfound&obj=evaluasi");
    exit;
}

// Update status ke selesai dan simpan id_admin
$stmt = $conn->prepare("
    UPDATE evaluasi_perjalanan 
    SET status = 'selesai', id_admin = ?
    WHERE id = ?
");
$stmt->bind_param("ii", $id_user, $id);

if ($stmt->execute()) {
    header("Location: " . BASE_URL . "/modules/shared/evaluasi/index.php?msg=success&obj=evaluasi");
    exit;
} else {
    header("Location: " . BASE_URL . "/modules/shared/evaluasi/index.php?msg=error&obj=evaluasi");
    exit;
}

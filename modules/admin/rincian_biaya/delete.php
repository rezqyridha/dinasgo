<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$role = $_SESSION['role'] ?? '';
$id_user = $_SESSION['id_user'] ?? 0;

// Hanya admin yang boleh hapus
if ($role !== 'admin') {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

// Validasi ID
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header("Location: " . BASE_URL . "/modules/shared/rincian_biaya/index.php?msg=invalid_id");
    exit;
}

// Cek data ada
$stmt = $conn->prepare("SELECT id FROM rincian_biaya WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    header("Location: " . BASE_URL . "/modules/shared/rincian_biaya/index.php?msg=not_found");
    exit;
}
$stmt->close();

// Hapus master — detail otomatis terhapus
$stmtDelete = $conn->prepare("DELETE FROM rincian_biaya WHERE id = ?");
$stmtDelete->bind_param("i", $id);
$stmtDelete->execute();

header("Location: " . BASE_URL . "/modules/shared/rincian_biaya/index.php?msg=deleted&obj=rincian");
exit;

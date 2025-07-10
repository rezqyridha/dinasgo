<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$role = $_SESSION['role'] ?? '';
$id_user = $_SESSION['id_user'] ?? 0;

if ($role !== 'bendahara') {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: " . BASE_URL . "/modules/shared/pencairan_dana/index.php?msg=invalid_id&obj=pencairan");
    exit;
}

// Cek data dan status
$stmt = $conn->prepare("SELECT status FROM pencairan_dana WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: " . BASE_URL . "/modules/shared/pencairan_dana/index.php?msg=not_found&obj=pencairan");
    exit;
}

$status = $result->fetch_assoc()['status'];
if (in_array($status, ['dicairkan', 'selesai'])) {
    header("Location: " . BASE_URL . "/modules/shared/pencairan_dana/index.php?msg=forbidden_delete&obj=pencairan");
    exit;
}

// Hapus data
$stmtDel = $conn->prepare("DELETE FROM pencairan_dana WHERE id = ?");
$stmtDel->bind_param("i", $id);

if ($stmtDel->execute()) {
    header("Location: " . BASE_URL . "/modules/shared/pencairan_dana/index.php?msg=deleted&obj=pencairan");
    exit;
} else {
    header("Location: " . BASE_URL . "/modules/shared/pencairan_dana/index.php?msg=error&obj=pencairan");
    exit;
}

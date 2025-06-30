<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$role = $_SESSION['role'] ?? '';
if ($role !== 'admin') {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id < 1) {
    header("Location: " . BASE_URL . "/modules/shared/sppd/index.php?msg=invalid&obj=sppd");
    exit;
}

// Pastikan data ada
$stmtCek = $conn->prepare("SELECT id FROM sppd WHERE id = ?");
$stmtCek->bind_param("i", $id);
$stmtCek->execute();
$result = $stmtCek->get_result();

if ($result->num_rows === 0) {
    header("Location: " . BASE_URL . "/modules/shared/sppd/index.php?msg=notfound&obj=sppd");
    exit;
}

// Lanjutkan delete
$stmtDel = $conn->prepare("DELETE FROM sppd WHERE id = ?");
$stmtDel->bind_param("i", $id);

if ($stmtDel->execute()) {
    header("Location: " . BASE_URL . "/modules/shared/sppd/index.php?msg=deleted&obj=sppd");
    exit;
} else {
    header("Location: " . BASE_URL . "/modules/shared/sppd/index.php?msg=failed&obj=sppd");
    exit;
}

<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$role = $_SESSION['role'] ?? '';
$id_user = $_SESSION['id_user'] ?? 0;

// Hanya atasan yang boleh akses
if ($role !== 'atasan') {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
$aksi = $_GET['aksi'] ?? '';

if ($id <= 0 || !in_array($aksi, ['setujui', 'tolak'])) {
    header("Location: " . BASE_URL . "/modules/shared/evaluasi/index.php?msg=invalid&obj=evaluasi");
    exit;
}

// Pastikan status diajukan
$stmt = $conn->prepare("SELECT id FROM evaluasi_perjalanan WHERE id = ? AND status = 'diajukan'");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    header("Location: " . BASE_URL . "/modules/shared/evaluasi/index.php?msg=notfound&obj=evaluasi");
    exit;
}

$newStatus = ($aksi === 'setujui') ? 'disetujui' : 'ditolak';

// Update status & id_atasan
$stmt = $conn->prepare("
    UPDATE evaluasi_perjalanan 
    SET status = ?, id_atasan = ?
    WHERE id = ?
");
$stmt->bind_param("sii", $newStatus, $id_user, $id);

if ($stmt->execute()) {
    $msg = ($aksi === 'setujui') ? 'disetujui' : 'ditolak';
    header("Location: " . BASE_URL . "/modules/shared/evaluasi/index.php?msg=$msg&obj=evaluasi");
    exit;
} else {
    header("Location: " . BASE_URL . "/modules/shared/evaluasi/index.php?msg=error&obj=evaluasi");
    exit;
}

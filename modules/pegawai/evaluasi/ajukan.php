<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$role = $_SESSION['role'] ?? '';
$id_user = $_SESSION['id_user'] ?? 0;

// Hanya pegawai bisa ajukan
if ($role !== 'pegawai') {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

// Validasi ID
$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: " . BASE_URL . "/modules/shared/evaluasi/index.php?msg=invalid&obj=evaluasi");
    exit;
}

// Cari ID pegawai
$stmt = $conn->prepare("SELECT id FROM pegawai WHERE id_user = ?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$stmt->bind_result($id_pegawai);
$stmt->fetch();
$stmt->close();

// Ambil evaluasi milik pegawai & status draft
$stmt = $conn->prepare("
    SELECT id FROM evaluasi_perjalanan 
    WHERE id = ? AND id_pegawai = ? AND status = 'draft'
");
$stmt->bind_param("ii", $id, $id_pegawai);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    header("Location: " . BASE_URL . "/modules/shared/evaluasi/index.php?msg=notfound&obj=evaluasi");
    exit;
}

// Update status ke diajukan
$stmt = $conn->prepare("
    UPDATE evaluasi_perjalanan 
    SET status = 'diajukan' 
    WHERE id = ? AND id_pegawai = ?
");
$stmt->bind_param("ii", $id, $id_pegawai);

if ($stmt->execute()) {
    header("Location: " . BASE_URL . "/modules/shared/evaluasi/index.php?msg=diajukan&obj=evaluasi");
    exit;
} else {
    header("Location: " . BASE_URL . "/modules/shared/evaluasi/index.php?msg=error&obj=evaluasi");
    exit;
}

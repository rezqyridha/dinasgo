<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

//  Hanya untuk role bendahara
if ($_SESSION['role'] !== 'bendahara') {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

//  Ambil dan validasi parameter GET
$id     = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$action = $_GET['action'] ?? '';

if (!$id || !in_array($action, ['setujui', 'tolak'])) {
    header("Location: " . BASE_URL . "/modules/shared/rincian_biaya/index.php?msg=invalid");
    exit;
}

//  Cek apakah data dengan ID dan status diajukan tersedia
$stmt = $conn->prepare("SELECT id FROM rincian_biaya WHERE id = ? AND status = 'diajukan'");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: " . BASE_URL . "/modules/shared/rincian_biaya/index.php?msg=notfound");
    exit;
}

//  Lakukan update status sesuai action
$statusBaru = $action === 'setujui' ? 'disetujui' : 'ditolak';
$stmtUpdate = $conn->prepare("UPDATE rincian_biaya SET status = ?, id_bendahara_verifikasi = ? WHERE id = ?");
$stmtUpdate->bind_param("sii", $statusBaru, $_SESSION['id_user'], $id);
$stmtUpdate->execute();


//  Redirect kembali dengan pesan
header("Location: " . BASE_URL . "/modules/shared/rincian_biaya/index.php?msg=$statusBaru");
exit;

<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$role = $_SESSION['role'] ?? '';
$id_user = $_SESSION['id_user'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php?msg=invalid_method&obj=pencairan");
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$aksi = trim($_POST['aksi'] ?? '');

if ($id <= 0 || !$aksi) {
    header("Location: index.php?msg=invalid_param&obj=pencairan");
    exit;
}

// Ambil data status sekarang
$stmt = $conn->prepare("SELECT status FROM pencairan_dana WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    header("Location: index.php?msg=not_found&obj=pencairan");
    exit;
}

$status = $data['status'];

// === Bendahara verifikasi CAIRKAN ===
if ($aksi === 'cairkan' && $status === 'draft' && $role === 'bendahara') {

    $tanggal_pencairan = trim($_POST['tanggal_pencairan'] ?? '');

    if (empty($tanggal_pencairan)) {
        header("Location: index.php?msg=invalid_date&obj=pencairan");
        exit;
    }

    $stmt = $conn->prepare("
        UPDATE pencairan_dana 
        SET status = 'dicairkan', tanggal_pencairan = ? 
        WHERE id = ?
    ");
    $stmt->bind_param("si", $tanggal_pencairan, $id);

    if ($stmt->execute()) {
        header("Location: index.php?msg=verified_draft&obj=pencairan");
    } else {
        header("Location: index.php?msg=error&obj=pencairan");
    }
    exit;

    // === Admin verifikasi FINALISASI ===
} elseif ($aksi === 'finalisasi' && $status === 'dicairkan' && $role === 'admin') {

    $tanggal_finalisasi = trim($_POST['tanggal_finalisasi'] ?? '');

    if (empty($tanggal_finalisasi)) {
        header("Location: index.php?msg=invalid_date&obj=pencairan");
        exit;
    }

    $stmt = $conn->prepare("
        UPDATE pencairan_dana 
        SET status = 'selesai', tanggal_finalisasi = ?, id_admin_finalisasi = ? 
        WHERE id = ?
    ");
    $stmt->bind_param("sii", $tanggal_finalisasi, $id_user, $id);

    if ($stmt->execute()) {
        header("Location: index.php?msg=finalized&obj=pencairan");
    } else {
        header("Location: index.php?msg=error&obj=pencairan");
    }
    exit;
} else {
    // Tidak boleh verifikasi status ini
    header("Location: index.php?msg=forbidden_status&obj=pencairan");
    exit;
}

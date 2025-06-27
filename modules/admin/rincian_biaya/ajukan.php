<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: ' . BASE_URL . '/unauthorized.php');
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (!$id) {
    header('Location: ' . BASE_URL . '/modules/shared/rincian_biaya/index.php?msg=invalid');
    exit;
}

//  Cek apakah rincian ditemukan dan masih draft
$q = $conn->prepare("SELECT * FROM rincian_biaya WHERE id = ? AND status = 'draft'");
$q->bind_param("i", $id);
$q->execute();
$r = $q->get_result();

if ($r->num_rows === 0) {
    header('Location: ' . BASE_URL . '/modules/shared/rincian_biaya/index.php?msg=notfound');
    exit;
}

//  Cek apakah memiliki minimal 1 detail
$cekDetail = $conn->prepare("SELECT COUNT(*) AS jml FROM rincian_biaya_detail WHERE id_rincian = ?");
$cekDetail->bind_param("i", $id);
$cekDetail->execute();
$res = $cekDetail->get_result()->fetch_assoc();

if ($res['jml'] < 1) {
    header('Location: ' . BASE_URL . '/modules/shared/rincian_biaya/index.php?msg=nodetail');
    exit;
}

//  Update status ke diajukan
$update = $conn->prepare("UPDATE rincian_biaya SET status = 'diajukan', updated_at = NOW() WHERE id = ?");
$update->bind_param("i", $id);
$update->execute();

header('Location: ' . BASE_URL . '/modules/shared/rincian_biaya/index.php?msg=diajukan');
exit;

<?php
require_once __DIR__ . '/../config/constants.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//  Validasi login
if (!isset($_SESSION['id_user']) || !isset($_SESSION['role'])) {
    header('Location: ' . BASE_URL . '/auth/login.php?msg=unauthorized');
    exit;
}


//  Cegah double load
if (defined('SESSION_VALIDATED')) return;
define('SESSION_VALIDATED', true);
//  Info dasar session
$currentRole   = $_SESSION['role'];
$currentUserId = $_SESSION['id_user'];

//  Deteksi folder & modul dari URL
$path = $_SERVER['REQUEST_URI'];
$segments = explode('/', trim(parse_url($path, PHP_URL_PATH), '/'));
$key = array_search('modules', $segments);

if ($key === false) return;

$folder = $segments[$key + 1] ?? '';
$modul  = $segments[$key + 2] ?? '';

//  Konfigurasi Akses RBAC
$sharedAccess = [
    'pegawai'       => ['admin', 'pegawai'],
    'pengajuan'     => ['admin', 'pegawai'],
    'spt'           => ['admin', 'pegawai', 'bendahara', 'atasan'],
    'sppd'          => ['admin', 'atasan'],
    'rincian_biaya' => ['admin', 'pegawai', 'bendahara', 'atasan'],
    'pencairan_dana' => ['admin', 'pegawai', 'bendahara'],
    'laporan'       => ['admin', 'bendahara', 'atasan'],
    'notifikasi'    => ['admin', 'pegawai', 'atasan'],
    'surat'         => ['admin', 'pegawai'],
];

//  Validasi RBAC
if ($folder === 'shared') {
    if (!isset($sharedAccess[$modul]) || !in_array($currentRole, $sharedAccess[$modul])) {
        header("Location: " . BASE_URL . "/modules/$currentRole/dashboard.php?msg=unauthorized");
        exit;
    }
} elseif ($folder !== $currentRole) {
    header("Location: " . BASE_URL . "/modules/$currentRole/dashboard.php?msg=forbidden");
    exit;
}

<?php
require_once __DIR__ . '/../config/constants.php';
session_start();

// ✅ Proteksi login wajib
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

// ✅ Cegah double include
if (defined('SESSION_CHECKED')) return;
define('SESSION_CHECKED', true);

// ✅ Ambil informasi user saat ini
$currentRole = $_SESSION['role'];
$currentUserId = $_SESSION['user_id'];

// ✅ Deteksi folder dan modul berdasarkan URL
$currentPath = str_replace('\\', '/', $_SERVER['PHP_SELF']);
$segments = explode('/', trim($currentPath, '/'));

// Pastikan 'modules' ada di URL
$key = array_search('modules', $segments);
if ($key === false) {
    // Jika tidak di dalam folder modules, tidak validasi RBAC
    return;
}

$currentFolder = $segments[$key + 1] ?? '';
$currentModule = $segments[$key + 2] ?? ''; // bisa berupa 'spt', 'pengajuan', dll

// ✅ Daftar RBAC untuk modul shared (bisa diperluas)
$sharedAccessControl = [
    'pegawai' => ['admin', 'pegawai'],
    'spt' => ['admin', 'pegawai', 'bendahara', 'atasan'],
    'pengajuan' => ['admin', 'pegawai'],
    'laporan' => ['admin', 'bendahara', 'atasan'],
    'surat' => ['admin', 'pegawai'],
    'notifikasi' => ['admin', 'pegawai', 'atasan'],
];

// ✅ Validasi berdasarkan folder
if ($currentFolder === 'shared') {
    // Modul tidak dikenal
    if (!array_key_exists($currentModule, $sharedAccessControl)) {
        header("Location: " . BASE_URL . "/modules/$currentRole/index.php?error=forbidden_shared");
        exit;
    }

    // Role tidak diizinkan
    if (!in_array($currentRole, $sharedAccessControl[$currentModule])) {
        header("Location: " . BASE_URL . "/modules/$currentRole/index.php?error=unauthorized");
        exit;
    }
} else {
    // Akses ke folder role lain
    if ($currentFolder !== $currentRole) {
        header("Location: " . BASE_URL . "/modules/$currentRole/index.php?error=forbidden");
        exit;
    }
}

<?php
require_once __DIR__ . '/../config/constants.php';
session_start();

// Cek login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

// Ambil folder role dari path URL
$currentPath = str_replace('\\', '/', $_SERVER['PHP_SELF']);
$segments = explode('/', trim($currentPath, '/'));

$key = array_search('modules', $segments);
$currentFolder = $segments[$key + 1] ?? '';

// Validasi: hanya boleh akses folder milik sendiri atau shared
if (
    $currentFolder !== $_SESSION['role'] &&
    $currentFolder !== 'shared'
) {
    header('Location: ' . BASE_URL . '/modules/' . $_SESSION['role'] . '/index.php?error=unauthorized');
    exit;
}

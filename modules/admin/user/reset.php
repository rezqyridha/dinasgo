<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once AUTH_PATH . '/session.php';
require_once CONFIG_PATH . '/koneksi.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php?msg=unauthorized");
    exit;
}

$id       = intval($_POST['id'] ?? 0);
$loginId  = $_SESSION['id_user'] ?? 0;

if ($id <= 0 || $id === $loginId) {
    header("Location: index.php?msg=invalid");
    exit;
}

// Pastikan user ada
$cek = $conn->prepare("SELECT id FROM user WHERE id = ?");
$cek->bind_param("i", $id);
$cek->execute();
$cek->store_result();

if ($cek->num_rows === 0) {
    $cek->close();
    header("Location: index.php?msg=invalid");
    exit;
}
$cek->close();

// Reset password ke default
$password_default = 'user123';
$hash = password_hash($password_default, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE user SET password = ? WHERE id = ?");
$stmt->bind_param("si", $password_default, $id);

if ($stmt->execute()) {
    header("Location: index.php?msg=reset");
} else {
    header("Location: index.php?msg=failed");
}
exit;

<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once AUTH_PATH . '/session.php';
require_once CONFIG_PATH . '/koneksi.php';

$pageTitle = 'Tambah User';

// Hanya admin yang boleh akses
if ($_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/modules/{$_SESSION['role']}/dashboard.php?msg=unauthorized");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? '';
    $status   = $_POST['status'] ?? 'aktif';

    if ($nama === '' || $username === '' || $password === '' || $role === '') {
        header("Location: index.php?msg=kosong");
        exit;
    }

    // Cek duplikat nama atau username
    $cek = $conn->prepare("SELECT id FROM user WHERE username = ? OR nama = ?");
    $cek->bind_param("ss", $username, $nama);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows > 0) {
        header("Location: index.php?msg=duplicate");
        exit;
    }
    $cek->close();

    // Jika perlu hashing:
    // $hashed = password_hash($password, PASSWORD_DEFAULT);


    $stmt = $conn->prepare("INSERT INTO user (nama, username, password, role, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nama, $username, $password, $role, $status);

    if ($stmt->execute()) {
        header("Location: index.php?msg=added");
    } else {
        header("Location: index.php?msg=error");
    }
    exit;
}

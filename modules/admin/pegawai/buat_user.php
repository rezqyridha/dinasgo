<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once AUTH_PATH . '/session.php';
require_once CONFIG_PATH . '/koneksi.php';

// Hanya admin yang boleh akses
if ($_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

// Tangkap data dari modal form
$idPegawai = intval($_POST['id_pegawai'] ?? 0);
$username  = trim($_POST['username'] ?? '');
$password  = trim($_POST['password'] ?? '');
$role      = trim($_POST['role'] ?? 'pegawai'); // default

// Validasi input dasar
if ($idPegawai <= 0 || $username === '' || $password === '' || $role === '') {
    header("Location: index.php?msg=invalid_id&obj=user");
    exit;
}

// Cek pegawai ada & belum punya user
$cek = $conn->prepare("SELECT nama, id_user FROM pegawai WHERE id = ?");
$cek->bind_param("i", $idPegawai);
$cek->execute();
$res = $cek->get_result();
if ($res->num_rows === 0) {
    header("Location: index.php?msg=not_found&obj=pegawai");
    exit;
}
$dataPegawai = $res->fetch_assoc();
if (!empty($dataPegawai['id_user'])) {
    header("Location: index.php?msg=duplicate&obj=user");
    exit;
}
$namaPegawai = $dataPegawai['nama'];

// Cek duplikat username
$cekUname = $conn->prepare("SELECT id FROM user WHERE username = ?");
$cekUname->bind_param("s", $username);
$cekUname->execute();
$cekUname->store_result();
if ($cekUname->num_rows > 0) {
    header("Location: index.php?msg=duplicate&obj=username");
    exit;
}
$cekUname->close();

// Hash password
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Insert ke tabel user
$stmt = $conn->prepare("
  INSERT INTO user (nama, username, password, role, status)
  VALUES (?, ?, ?, ?, 'aktif')
");
$stmt->bind_param("ssss", $namaPegawai, $username, $password, $role);

if ($stmt->execute()) {
    $idUserBaru = $conn->insert_id;

    // Update FK di tabel pegawai
    $stmt2 = $conn->prepare("UPDATE pegawai SET id_user = ? WHERE id = ?");
    $stmt2->bind_param("ii", $idUserBaru, $idPegawai);
    $stmt2->execute();

    header("Location: index.php?msg=added&obj=user");
} else {
    header("Location: index.php?msg=error&obj=user");
}
exit;

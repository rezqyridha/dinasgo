<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$role = $_SESSION['role'];
$id_user = $_SESSION['id_user'];

if (!in_array($role, ['admin', 'pegawai'])) {
    header("Location: index.php?msg=unauthorized&obj=pengajuan");
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: index.php?msg=invalid&obj=pengajuan");
    exit;
}

// Ambil data pengajuan
$stmt = $conn->prepare("
    SELECT p.id, p.id_pegawai, p.status 
    FROM pengajuan_perjalanan p
    JOIN pegawai pg ON p.id_pegawai = pg.id
    WHERE p.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

if (!$data) {
    header("Location: index.php?msg=invalid&obj=pengajuan");
    exit;
}

// Jika pegawai, pastikan hanya hapus miliknya
if ($role === 'pegawai') {
    $stmt = $conn->prepare("SELECT id FROM pegawai WHERE id_user = ?");
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $stmt->bind_result($id_pegawai);
    $stmt->fetch();
    $stmt->close();

    if ($data['id_pegawai'] != $id_pegawai) {
        header("Location: index.php?msg=unauthorized&obj=pengajuan");
        exit;
    }

    // Tidak boleh hapus jika status bukan diajukan
    if ($data['status'] !== 'diajukan') {
        header("Location: index.php?msg=locked&obj=pengajuan");
        exit;
    }
}

// Lanjut hapus
$stmt = $conn->prepare("DELETE FROM pengajuan_perjalanan WHERE id = ?");
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    header("Location: index.php?msg=deleted&obj=pengajuan");
} else {
    header("Location: index.php?msg=failed&obj=pengajuan");
}
exit;

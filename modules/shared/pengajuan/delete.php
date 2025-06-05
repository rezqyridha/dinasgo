<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once BASE_PATH . '/auth/session.php';
require_once BASE_PATH . '/config/koneksi.php';

if ($_SESSION['role'] !== 'pegawai') {
    header("Location: index.php?msg=unauthorized");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header("Location: index.php?msg=invalid");
    exit;
}

// Cek status pengajuan
$cek = $conn->prepare("SELECT status FROM pengajuan_perjalanan WHERE id = ?");
$cek->bind_param("i", $id);
$cek->execute();
$result = $cek->get_result();
$data = $result->fetch_assoc();
$cek->close();

if (!$data) {
    header("Location: index.php?msg=notfound");
    exit;
}

// Proteksi: hanya pemilik data yang bisa hapus
if ($data['id_pegawai'] != $_SESSION['user_id']) {
    header("Location: index.php?msg=forbidden");
    exit;
}

// Cek relasi ke modul lain
$cekRelasi = $conn->prepare("
    SELECT 1 FROM dokumen WHERE id_pengajuan = ?
    UNION
    SELECT 1 FROM evaluasi_perjalanan WHERE id_pengajuan = ?
    UNION
    SELECT 1 FROM pencairan_dana WHERE id_pengajuan = ?
    UNION
    SELECT 1 FROM persetujuan WHERE id_pengajuan = ?
    UNION
    SELECT 1 FROM sppd WHERE id_pengajuan = ?
    LIMIT 1
");
$cekRelasi->bind_param("iiiii", $id, $id, $id, $id, $id);
$cekRelasi->execute();
$cekRelasi->store_result();

if ($cekRelasi->num_rows > 0) {
    $cekRelasi->close();
    header("Location: index.php?msg=fk_blocked");
    exit;
}
$cekRelasi->close();

// Hapus data jika aman
$delete = $conn->prepare("DELETE FROM pengajuan_perjalanan WHERE id = ?");
$delete->bind_param("i", $id);
$delete->execute();

header("Location: index.php?msg=deleted");
exit;

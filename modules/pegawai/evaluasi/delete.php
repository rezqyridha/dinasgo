<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$role = $_SESSION['role'] ?? '';
$id_user = $_SESSION['id_user'] ?? 0;

// Hanya pegawai yang bisa hapus evaluasi
if ($role !== 'pegawai') {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

// Validasi ID
$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: ../shared/evaluasi/index.php?msg=invalid&obj=evaluasi");
    exit;
}

// Cari ID pegawai
$stmt = $conn->prepare("SELECT id FROM pegawai WHERE id_user = ?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$stmt->bind_result($id_pegawai);
$stmt->fetch();
$stmt->close();

// Ambil evaluasi milik pegawai & status draft
$stmt = $conn->prepare("
    SELECT lampiran FROM evaluasi_perjalanan 
    WHERE id = ? AND id_pegawai = ? AND status = 'draft'
");
$stmt->bind_param("ii", $id, $id_pegawai);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    header("Location: " . BASE_URL . "/modules/shared/evaluasi/index.php?msg=notfound&obj=evaluasi");
    exit;
}

// Hapus lampiran file jika ada
$upload_dir = dirname(__DIR__, 3) . "/uploads/evaluasi/";
$lampiranPath = $upload_dir . $data['lampiran'];

if (is_file($lampiranPath) && file_exists($lampiranPath)) {
    unlink($lampiranPath);
}

// Hapus dari DB
$stmt = $conn->prepare("DELETE FROM evaluasi_perjalanan WHERE id = ? AND id_pegawai = ?");
$stmt->bind_param("ii", $id, $id_pegawai);

if ($stmt->execute()) {
    header("Location: " . BASE_URL . "/modules/shared/evaluasi/index.php?msg=deleted&obj=evaluasi");
    exit;
} else {
    header("Location: " . BASE_URL . "/modules/shared/evaluasi/index.php?msg=error&obj=evaluasi");
    exit;
}

<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';

$id_pengajuan = (int) ($_GET['id_pengajuan'] ?? 0);

if ($id_pengajuan <= 0) {
    echo '<div class="alert alert-warning">ID pengajuan tidak valid.</div>';
    exit;
}

// Cari file dokumen jenis 'bukti_pengeluaran' untuk pengajuan ini
$stmt = $conn->prepare("
    SELECT nama_file FROM dokumen
    WHERE id_pengajuan = ? AND jenis = 'bukti_pengeluaran'
    LIMIT 1
");
$stmt->bind_param("i", $id_pengajuan);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($result && $result['nama_file']) {
    $safeFile = htmlspecialchars($result['nama_file']);
    $url = BASE_URL . "/uploads/dokumen/{$id_pengajuan}/{$safeFile}";

    echo "
    <div class='alert alert-info'>
        <strong>Bukti Pengeluaran Tersedia:</strong><br>
        <a href='{$url}' target='_blank' class='btn btn-sm btn-primary me-2'>
            <i class='fe fe-eye me-1'></i> Lihat Bukti
        </a>
        <a href='{$url}' download class='btn btn-sm btn-success'>
            <i class='fe fe-download me-1'></i> Download Bukti
        </a>
    </div>
    ";
} else {
    echo "
    <div class='alert alert-warning'>
        Tidak ditemukan file bukti pengeluaran untuk pengajuan ini.
    </div>
    ";
}

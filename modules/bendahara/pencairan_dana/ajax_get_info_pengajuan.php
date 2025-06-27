<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

// Proteksi hanya bendahara
if ($_SESSION['role'] !== 'bendahara') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
$response = ['success' => false];

if ($id <= 0) {
    echo json_encode($response);
    exit;
}

// Ambil estimasi biaya dari pengajuan
$stmt1 = $conn->prepare("SELECT estimasi_biaya FROM pengajuan_perjalanan WHERE id = ?");
$stmt1->bind_param("i", $id);
$stmt1->execute();
$result1 = $stmt1->get_result();

if ($result1->num_rows === 0) {
    echo json_encode($response);
    exit;
}

$estimasi = $result1->fetch_assoc()['estimasi_biaya'] ?? 0;

// Ambil rincian biaya yang disetujui
$stmt2 = $conn->prepare("SELECT id, jumlah_total FROM rincian_biaya WHERE id_pengajuan = ? AND status = 'disetujui' LIMIT 1");
$stmt2->bind_param("i", $id);
$stmt2->execute();
$result2 = $stmt2->get_result();

if ($result2->num_rows === 0) {
    echo json_encode($response);
    exit;
}

$rowRincian = $result2->fetch_assoc();
$id_rincian = $rowRincian['id'];
$jumlah_total = $rowRincian['jumlah_total'];

// Ambil rincian detail
$stmt3 = $conn->prepare("SELECT jenis_biaya, jumlah, satuan, harga_satuan FROM rincian_biaya_detail WHERE id_rincian = ?");
$stmt3->bind_param("i", $id_rincian);
$stmt3->execute();
$result3 = $stmt3->get_result();

$detail_html = '<ul class="mb-0 ps-3">';
while ($row = $result3->fetch_assoc()) {
    $label = htmlspecialchars($row['jenis_biaya']);
    $jumlah = (int) $row['jumlah'];
    $satuan = htmlspecialchars($row['satuan']);
    $harga = number_format($row['harga_satuan'], 0, ',', '.');
    $total = number_format($row['jumlah'] * $row['harga_satuan'], 0, ',', '.');
    $detail_html .= "<li>{$label}: {$jumlah} {$satuan} x Rp{$harga} = Rp{$total}</li>";
}
$detail_html .= '</ul>';

// Kirim response
$response = [
    'success' => true,
    'estimasi' => number_format($estimasi, 0, ',', '.'),
    'total' => number_format($jumlah_total, 0, ',', '.'),
    'total_raw' => $jumlah_total,
    'detail_html' => $detail_html
];

echo json_encode($response);

<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'msg' => 'Unauthorized']);
    exit;
}


header('Content-Type: application/json');

$id = (int) ($_GET['id'] ?? 0);
$response = ['success' => false];

if ($id > 0) {
    $query = $conn->prepare("
        SELECT keperluan, 
               DATEDIFF(tanggal_kembali, tanggal_berangkat) + 1 AS lama, 
               'Mobil Dinas' AS transportasi
        FROM pengajuan_perjalanan
        WHERE id = ?
    ");
    $query->bind_param("i", $id);
    $query->execute();
    $result = $query->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $response['success'] = true;
        $response['keperluan'] = $row['keperluan'];
        $response['lama'] = $row['lama'] . ' hari';
        $response['transportasi'] = $row['transportasi'] ?? 'Mobil Dinas'; // fallback
    }
}

echo json_encode($response);

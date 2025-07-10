<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';
require_once FPDF_PATH . '/fpdf.php';

// Hanya admin & atasan boleh cetak
$role = $_SESSION['role'] ?? '';
if (!in_array($role, ['admin', 'atasan'])) {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    die('ID Evaluasi tidak valid.');
}

// Ambil data evaluasi + relasi
$stmt = $conn->prepare("
    SELECT e.*, 
           p.tujuan, 
           p.tanggal_berangkat,
           peg.nama AS nama_pegawai,
           u_atasan.nama AS nama_atasan,
           u_admin.nama AS nama_admin
    FROM evaluasi_perjalanan e
    JOIN pengajuan_perjalanan p ON e.id_pengajuan = p.id
    JOIN pegawai peg ON e.id_pegawai = peg.id
    LEFT JOIN user u_atasan ON e.id_atasan = u_atasan.id
    LEFT JOIN user u_admin ON e.id_admin = u_admin.id
    WHERE e.id = ? AND e.status IN ('disetujui', 'selesai')
");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    die('Evaluasi tidak ditemukan atau belum diverifikasi.');
}

// Helper format tanggal
function fmt($tgl)
{
    return date('d-m-Y', strtotime($tgl));
}

// === FPDF ===
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetMargins(20, 15, 20);

// Kop Surat
$pdf->Image($_SERVER['DOCUMENT_ROOT'] . '/dinasgo/assets/images/balai/PUPR.png', 20, 12, 12);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 6, 'KEMENTERIAN PEKERJAAN UMUM DAN PERUMAHAN RAKYAT', 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 7, 'BALAI WILAYAH SUNGAI KALIMANTAN III BANJARMASIN', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Jl. Yos Sudarso No.10, Telaga Biru, Banjarmasin Barat, Kalimantan Selatan 70117', 0, 1, 'C');
$pdf->Cell(0, 0, '', 'B', 1, 'C');
$pdf->Ln(10);

// Judul
$pdf->SetFont('Arial', 'BU', 12);
$pdf->Cell(0, 8, 'LAPORAN EVALUASI PERJALANAN DINAS', 0, 1, 'C');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 7, 'Nomor Evaluasi: EVL-' . str_pad($data['id'], 4, '0', STR_PAD_LEFT), 0, 1, 'C');
$pdf->Ln(6);

// Informasi Umum
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(50, 7, 'Nama Pegawai', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, $data['nama_pegawai'], 0, 1);

$pdf->Cell(50, 7, 'Tujuan Perjalanan', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, $data['tujuan'], 0, 1);

$pdf->Cell(50, 7, 'Tanggal Berangkat', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, fmt($data['tanggal_berangkat']), 0, 1);

$pdf->Cell(50, 7, 'Status', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, ucfirst($data['status']), 0, 1);

$pdf->Ln(5);

// Rincian Evaluasi
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 7, 'Kendala:', 0, 1);
$pdf->SetFont('Arial', '', 11);
$pdf->MultiCell(0, 7, $data['kendala'] ?: '-', 0, 'J');

$pdf->Ln(2);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 7, 'Hasil:', 0, 1);
$pdf->SetFont('Arial', '', 11);
$pdf->MultiCell(0, 7, $data['hasil'] ?: '-', 0, 'J');

$pdf->Ln(2);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 7, 'Saran:', 0, 1);
$pdf->SetFont('Arial', '', 11);
$pdf->MultiCell(0, 7, $data['saran'] ?: '-', 0, 'J');

$pdf->Ln(5);

// Lampiran
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(50, 7, 'Lampiran', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, $data['lampiran'] ?: '-', 0, 1);

$pdf->Ln(15);

// TTD
$pdf->Cell(0, 7, 'Banjarmasin, ' . fmt(date('Y-m-d')), 0, 1, 'R');

$pdf->Cell(0, 7, 'Mengetahui,', 0, 1, 'R');

if ($data['status'] === 'disetujui') {
    $pdf->Cell(0, 7, 'Atasan', 0, 1, 'R');
    $pdf->Ln(20);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 7, $data['nama_atasan'] ?: '-', 0, 1, 'R');
} elseif ($data['status'] === 'selesai') {
    $pdf->Cell(0, 7, 'Admin', 0, 1, 'R');
    $pdf->Ln(20);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 7, $data['nama_admin'] ?: '-', 0, 1, 'R');
}

$pdf->Output('I', 'Evaluasi_' . $data['id'] . '.pdf');

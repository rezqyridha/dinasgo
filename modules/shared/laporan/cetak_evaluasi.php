<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';
require_once FPDF_PATH . '/fpdf.php';

// RBAC
$role = $_SESSION['role'] ?? '';
if (!in_array($role, ['admin', 'atasan'])) {
    header("Location: ../../unauthorized.php");
    exit;
}

// Filter status
$status = $_GET['status'] ?? 'semua';

$where = "";
if ($status && $status !== 'semua') {
    $where = "WHERE ev.status = ?";
}

$query = "
    SELECT ev.*, peg.nama AS nama_pegawai
    FROM evaluasi_perjalanan ev
    JOIN pegawai peg ON ev.id_pegawai = peg.id
    $where
    ORDER BY ev.id ASC
";

if ($where) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $status);
} else {
    $stmt = $conn->prepare($query);
}

$stmt->execute();
$result = $stmt->get_result();

// PDF
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->SetMargins(15, 15, 15);
$pdf->AddPage();

// Header surat
$pdf->Image($_SERVER['DOCUMENT_ROOT'] . '/dinasgo/assets/images/balai/PUPR.png', 15, 12, 12);
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
$pdf->Cell(0, 7, 'Status: ' . ucfirst($status), 0, 1, 'C');
$pdf->Ln(4);

// Header tabel
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(230, 230, 230);
$w = [10, 30, 35, 35, 35, 20, 25]; // total = 190 mm

$pdf->Cell($w[0], 8, 'No', 1, 0, 'C', true);
$pdf->Cell($w[1], 8, 'Nama Pegawai', 1, 0, 'C', true);
$pdf->Cell($w[2], 8, 'Hasil', 1, 0, 'C', true);
$pdf->Cell($w[3], 8, 'Kendala', 1, 0, 'C', true);
$pdf->Cell($w[4], 8, 'Saran', 1, 0, 'C', true);
$pdf->Cell($w[5], 8, 'Lamp.', 1, 0, 'C', true);
$pdf->Cell($w[6], 8, 'Status', 1, 1, 'C', true);

// Isi
$pdf->SetFont('Arial', '', 9);
$no = 1;

while ($row = $result->fetch_assoc()) {
    $x = $pdf->GetX();
    $y = $pdf->GetY();

    // MultiCell 3 kolom dinamis
    $pdf->Cell($w[0], 6, $no++, 0); // No
    $pdf->Cell($w[1], 6, $row['nama_pegawai'], 0);

    $x1 = $pdf->GetX();
    $y1 = $pdf->GetY();
    $pdf->MultiCell($w[2], 6, $row['hasil'], 0);
    $h1 = $pdf->GetY() - $y1;

    $pdf->SetXY($x1 + $w[2], $y1);
    $pdf->MultiCell($w[3], 6, $row['kendala'], 0);
    $h2 = $pdf->GetY() - $y1;

    $pdf->SetXY($x1 + $w[2] + $w[3], $y1);
    $pdf->MultiCell($w[4], 6, $row['saran'], 0);
    $h3 = $pdf->GetY() - $y1;

    $h = max($h1, $h2, $h3, 6);

    // Kotak ulang baris
    $pdf->Rect($x, $y, $w[0], $h);
    $pdf->Rect($x + $w[0], $y, $w[1], $h);
    $pdf->Rect($x + $w[0] + $w[1], $y, $w[2], $h);
    $pdf->Rect($x + $w[0] + $w[1] + $w[2], $y, $w[3], $h);
    $pdf->Rect($x + $w[0] + $w[1] + $w[2] + $w[3], $y, $w[4], $h);
    $pdf->Rect($x + $w[0] + $w[1] + $w[2] + $w[3] + $w[4], $y, $w[5], $h);
    $pdf->Rect($x + $w[0] + $w[1] + $w[2] + $w[3] + $w[4] + $w[5], $y, $w[6], $h);

    $pdf->SetXY($x + $w[0] + $w[1] + $w[2] + $w[3] + $w[4], $y);
    $pdf->Cell($w[5], $h, ($row['lampiran'] ? 'Ada' : '-'), 0, 0, 'C');

    $pdf->Cell($w[6], $h, ucfirst($row['status']), 0, 1, 'C');
}

// TTD
$pdf->Ln(10);
$pdf->Cell(0, 7, 'Banjarmasin, ' . date('d-m-Y'), 0, 1, 'R');
$pdf->Cell(0, 7, 'Mengetahui,', 0, 1, 'R');
$pdf->Cell(0, 7, ucfirst($role), 0, 1, 'R');
$pdf->Ln(20);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 7, htmlspecialchars($_SESSION['nama'] ?? '_________________'), 0, 1, 'R');

$pdf->Output('I', 'Laporan_Evaluasi_Perjalanan_' . date('Ymd') . '.pdf');

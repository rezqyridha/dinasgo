<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';
require_once FPDF_PATH . '/fpdf.php';

$allowed_roles = ['admin', 'atasan'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: ../../unauthorized.php");
    exit;
}

// Filter tanggal
$dari = $_GET['dari'] ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');

if (!$dari || !$sampai) {
    die('Filter tanggal tidak valid.');
}

// Query data
$whereClause = "WHERE DATE(pp.tanggal_berangkat) >= '$dari' AND DATE(pp.tanggal_berangkat) <= '$sampai'";
$query = "
    SELECT pp.*, peg.nama AS nama_pegawai, peg.nip, peg.jabatan,
           spt.nomor_spt, sppd.nomor_sppd, rb.jumlah_total
    FROM pengajuan_perjalanan pp
    JOIN pegawai peg ON pp.id_pegawai = peg.id
    LEFT JOIN spt ON spt.id_pengajuan = pp.id
    LEFT JOIN sppd ON sppd.id_pengajuan = pp.id
    LEFT JOIN rincian_biaya rb ON rb.id_pengajuan = pp.id
    $whereClause
    ORDER BY pp.tanggal_berangkat ASC
";
$result = $conn->query($query);

// PDF setup
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->SetMargins(20, 15, 20);
$pdf->AddPage();

// Kop surat
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
$pdf->Cell(0, 8, 'LAPORAN PERJALANAN DINAS', 0, 1, 'C');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 7, 'Periode: ' . date('d-m-Y', strtotime($dari)) . ' s.d. ' . date('d-m-Y', strtotime($sampai)), 0, 1, 'C');
$pdf->Ln(4);

// Header tabel
$w = [
    'no' => 10,
    'nama' => 35,
    'tujuan' => 30,
    'tgl' => 25,
    'spt' => 25,
    'sppd' => 27,
    'rincian' => 30
];
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell($w['no'], 8, 'No', 1, 0, 'C', true);
$pdf->Cell($w['nama'], 8, 'Nama Pegawai', 1, 0, 'C', true);
$pdf->Cell($w['tujuan'], 8, 'Tujuan', 1, 0, 'C', true);
$pdf->Cell($w['tgl'], 8, 'Tgl Berangkat', 1, 0, 'C', true);
$pdf->Cell($w['spt'], 8, 'SPT', 1, 0, 'C', true);
$pdf->Cell($w['sppd'], 8, 'SPPD', 1, 0, 'C', true);
$pdf->Cell($w['rincian'], 8, 'Rincian (Rp)', 1, 1, 'C', true);

// Isi tabel wrap + tinggi baris dinamis
$pdf->SetFont('Arial', '', 10);
$no = 1;
while ($row = $result->fetch_assoc()) {
    $x = $pdf->GetX();
    $y = $pdf->GetY();

    // No
    $pdf->MultiCell($w['no'], 7, $no++, 1, 'C');
    $h1 = $pdf->GetY() - $y;

    $pdf->SetXY($x + $w['no'], $y);
    $pdf->MultiCell($w['nama'], 7, $row['nama_pegawai'], 1);
    $h2 = $pdf->GetY() - $y;

    $pdf->SetXY($x + $w['no'] + $w['nama'], $y);
    $pdf->MultiCell($w['tujuan'], 7, $row['tujuan'], 1);
    $h3 = $pdf->GetY() - $y;

    // Hitung tinggi maksimum
    $maxH = max($h1, $h2, $h3, 7);

    // Tanggal
    $pdf->SetXY($x + $w['no'] + $w['nama'] + $w['tujuan'], $y);
    $pdf->Cell($w['tgl'], $maxH, date('d-m-Y', strtotime($row['tanggal_berangkat'])), 1, 0, 'C');

    // SPT
    $pdf->Cell($w['spt'], $maxH, $row['nomor_spt'] ?: '-', 1, 0, 'C');

    // SPPD
    $pdf->Cell($w['sppd'], $maxH, $row['nomor_sppd'] ?: '-', 1, 0, 'C');

    // Rincian
    $jumlah = floatval($row['jumlah_total'] ?? 0);
    $pdf->Cell($w['rincian'], $maxH, 'Rp ' . number_format($jumlah, 0, ',', '.'), 1, 1, 'R');
}

$pdf->Ln(10);

// TTD
$pdf->Cell(0, 7, 'Banjarmasin, ' . date('d-m-Y'), 0, 1, 'R');
$pdf->Cell(0, 7, 'Mengetahui,', 0, 1, 'R');
$pdf->Cell(0, 7, 'Admin', 0, 1, 'R');
$pdf->Ln(20);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 7, htmlspecialchars($_SESSION['nama'] ?? '_________________'), 0, 1, 'R');

// Output
$pdf->Output('I', 'Laporan_Perjalanan_Dinas_' . $dari . '_' . $sampai . '.pdf');

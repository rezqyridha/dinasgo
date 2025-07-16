<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';
require_once FPDF_PATH . '/fpdf.php';

// RBAC: hanya admin & bendahara
$role = $_SESSION['role'] ?? '';
if (!in_array($role, ['admin', 'bendahara'])) {
    header("Location: ../../unauthorized.php");
    exit;
}

// Filter tanggal
$dari = $_GET['dari'] ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');

// Query data header
$whereClause = "WHERE DATE(rb.tanggal_rincian) >= '$dari' AND DATE(rb.tanggal_rincian) <= '$sampai'";
$query = "
    SELECT rb.*, peg.nama AS nama_pegawai, p.tujuan
    FROM rincian_biaya rb
    JOIN pengajuan_perjalanan p ON rb.id_pengajuan = p.id
    JOIN pegawai peg ON p.id_pegawai = peg.id
    $whereClause
    ORDER BY rb.tanggal_rincian ASC
";
$result = $conn->query($query);

// PDF setup
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->SetMargins(20, 15, 20);
$pdf->AddPage();

// Fungsi Kop Surat
function kopSurat($pdf)
{
    $pdf->Image($_SERVER['DOCUMENT_ROOT'] . '/dinasgo/assets/images/balai/PUPR.png', 20, 12, 12);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 6, 'KEMENTERIAN PEKERJAAN UMUM DAN PERUMAHAN RAKYAT', 0, 1, 'C');
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 7, 'BALAI WILAYAH SUNGAI KALIMANTAN III BANJARMASIN', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 6, 'Jl. Yos Sudarso No.10, Telaga Biru, Banjarmasin Barat, Kalimantan Selatan 70117', 0, 1, 'C');
    $pdf->Cell(0, 0, '', 'B', 1, 'C');
    $pdf->Ln(10);
}

// Fungsi Judul
function judulLaporan($pdf, $dari, $sampai)
{
    $pdf->SetFont('Arial', 'BU', 12);
    $pdf->Cell(0, 8, 'LAPORAN RINCIAN BIAYA PERJALANAN DINAS', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 7, 'Periode: ' . date('d-m-Y', strtotime($dari)) . ' s.d. ' . date('d-m-Y', strtotime($sampai)), 0, 1, 'C');
    $pdf->Ln(4);
}

// Fungsi Header Tabel Utama
function headerTabelUtama($pdf, $w)
{
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(230, 230, 230);
    $pdf->Cell($w[0], 8, 'No', 1, 0, 'C', true);
    $pdf->Cell($w[1], 8, 'Nomor', 1, 0, 'C', true);
    $pdf->Cell($w[2], 8, 'Nama Pegawai', 1, 0, 'C', true);
    $pdf->Cell($w[3], 8, 'Tujuan', 1, 0, 'C', true);
    $pdf->Cell($w[4], 8, 'Tgl Rincian', 1, 0, 'C', true);
    $pdf->Cell($w[5], 8, 'Total Biaya', 1, 0, 'C', true);
    $pdf->Cell($w[6], 8, 'Status', 1, 1, 'C', true);
}

// Cetak Kop, Judul Awal
kopSurat($pdf);
judulLaporan($pdf, $dari, $sampai);

$w = [10, 28, 32, 28, 22, 30, 30]; // total 180 mm

$pdf->SetFont('Arial', '', 10);
$no = 1;

while ($row = $result->fetch_assoc()) {

    // Jika posisi mendekati batas bawah, halaman baru
    if ($pdf->GetY() > 240) {
        $pdf->AddPage();
        kopSurat($pdf);
        judulLaporan($pdf, $dari, $sampai);
    }

    // Tampilkan header tabel utama SETIAP data
    headerTabelUtama($pdf, $w);

    $pdf->Cell($w[0], 7, $no++, 1, 0, 'C');
    $pdf->Cell($w[1], 7, $row['nomor_rincian'], 1);
    $pdf->Cell($w[2], 7, $row['nama_pegawai'], 1);
    $pdf->Cell($w[3], 7, $row['tujuan'], 1);
    $pdf->Cell($w[4], 7, date('d-m-Y', strtotime($row['tanggal_rincian'])), 1);
    $pdf->Cell($w[5], 7, 'Rp ' . number_format($row['jumlah_total'], 0, ',', '.'), 1, 0, 'R');
    $pdf->Cell($w[6], 7, ucfirst($row['status']), 1, 1, 'C');

    // Detail:
    if ($pdf->GetY() > 250) {
        $pdf->AddPage();
        kopSurat($pdf);
        judulLaporan($pdf, $dari, $sampai);
    }
    $pdf->Cell(array_sum($w), 7, 'Detail:', 1, 1, 'L');

    // Header detail
    $pdf->SetFont('Arial', 'I', 9);
    $wDetail = [35, 55, 10, 20, 60];
    $pdf->Cell($wDetail[0], 7, 'Jenis Biaya', 1);
    $pdf->Cell($wDetail[1], 7, 'Keterangan', 1);
    $pdf->Cell($wDetail[2], 7, 'Jml', 1, 0, 'C');
    $pdf->Cell($wDetail[3], 7, 'Satuan', 1, 0, 'C');
    $pdf->Cell($wDetail[4], 7, 'Harga', 1, 1, 'R');

    // Data detail
    $stmtDetail = $conn->prepare("SELECT * FROM rincian_biaya_detail WHERE id_rincian = ?");
    $stmtDetail->bind_param("i", $row['id']);
    $stmtDetail->execute();
    $detail = $stmtDetail->get_result();

    $pdf->SetFont('Arial', '', 9);
    while ($d = $detail->fetch_assoc()) {
        if ($pdf->GetY() > 260) {
            $pdf->AddPage();
            kopSurat($pdf);
            judulLaporan($pdf, $dari, $sampai);
        }
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->Cell($wDetail[0], 6, $d['jenis_biaya'], 1);
        $xKeterangan = $pdf->GetX();
        $yKeterangan = $pdf->GetY();
        $pdf->MultiCell($wDetail[1], 6, $d['keterangan'], 1);
        $h = $pdf->GetY() - $yKeterangan;
        $pdf->SetXY($xKeterangan + $wDetail[1], $yKeterangan);
        $pdf->Cell($wDetail[2], $h, $d['jumlah'], 1, 0, 'C');
        $pdf->Cell($wDetail[3], $h, $d['satuan'], 1, 0, 'C');
        $pdf->Cell($wDetail[4], $h, 'Rp ' . number_format($d['total'], 0, ',', '.'), 1, 1, 'R');
    }
    $stmtDetail->close();
}

$pdf->Ln(10);

// TTD
$pdf->Cell(0, 7, 'Banjarmasin, ' . date('d-m-Y'), 0, 1, 'R');
$pdf->Cell(0, 7, 'Mengetahui,', 0, 1, 'R');
$pdf->Cell(0, 7, ucfirst($role), 0, 1, 'R');
$pdf->Ln(20);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 7, htmlspecialchars($_SESSION['nama'] ?? '_________________'), 0, 1, 'R');

$pdf->Output('I', 'Laporan_Rincian_Biaya_' . $dari . '_' . $sampai . '.pdf');

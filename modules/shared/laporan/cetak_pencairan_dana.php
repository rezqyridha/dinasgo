<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';
require_once FPDF_PATH . '/fpdf.php';

// RBAC: admin & bendahara
$role = $_SESSION['role'] ?? '';
if (!in_array($role, ['admin', 'bendahara'])) {
    header("Location: ../../unauthorized.php");
    exit;
}

// Filter
$dari = $_GET['dari'] ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');
$status = $_GET['status'] ?? '';

// Query
$whereClause = "WHERE DATE(pd.tanggal_pencairan) >= ? AND DATE(pd.tanggal_pencairan) <= ?";
$params = [$dari, $sampai];
$types = "ss";

if ($status && $status !== 'semua') {
    $whereClause .= " AND pd.status = ?";
    $params[] = $status;
    $types .= "s";
}

$stmt = $conn->prepare("
    SELECT pd.*, rb.nomor_rincian, peg.nama AS nama_pegawai
    FROM pencairan_dana pd
    JOIN rincian_biaya rb ON pd.id_rincian_biaya = rb.id
    JOIN pengajuan_perjalanan pp ON pd.id_pengajuan = pp.id
    JOIN pegawai peg ON pp.id_pegawai = peg.id
    $whereClause
    ORDER BY pd.tanggal_pencairan ASC
");
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// PDF init
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->SetMargins(20, 15, 20); // margin kiri-kanan 20mm
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
$pdf->Cell(0, 8, 'LAPORAN PENCAIRAN DANA', 0, 1, 'C');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 7, 'Periode: ' . date('d-m-Y', strtotime($dari)) . ' s.d. ' . date('d-m-Y', strtotime($sampai)), 0, 1, 'C');
$pdf->Ln(4);

// Header tabel
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(230, 230, 230);

// ✅ Lebar proporsional, total ±175 mm
$w = [10, 22, 25, 28, 40, 30, 20]; // Total: 175 mm

$pdf->Cell($w[0], 8, 'No', 1, 0, 'C', true);
$pdf->Cell($w[1], 8, 'Nomor', 1, 0, 'C', true);
$pdf->Cell($w[2], 8, 'Tgl Cair', 1, 0, 'C', true);
$pdf->Cell($w[3], 8, 'Nomor Rincian', 1, 0, 'C', true);
$pdf->Cell($w[4], 8, 'Nama Pegawai', 1, 0, 'C', true);
$pdf->Cell($w[5], 8, 'Jumlah Dana', 1, 0, 'C', true);
$pdf->Cell($w[6], 8, 'Status', 1, 1, 'C', true);

// Isi
$pdf->SetFont('Arial', '', 10);
$no = 1;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell($w[0], 7, $no++, 1, 0, 'C');
        $pdf->Cell($w[1], 7, htmlspecialchars($row['id']) . '/' . date('Y', strtotime($row['tanggal_pencairan'])), 1);
        $pdf->Cell($w[2], 7, date('d-m-Y', strtotime($row['tanggal_pencairan'])), 1);
        $pdf->Cell($w[3], 7, htmlspecialchars($row['nomor_rincian']), 1);
        $pdf->Cell($w[4], 7, htmlspecialchars($row['nama_pegawai']), 1);
        $pdf->Cell($w[5], 7, 'Rp ' . htmlspecialchars($row['jumlah_dana']), 1, 0, 'R');
        $pdf->Cell($w[6], 7, ucfirst($row['status']), 1, 1, 'C');
    }
} else {
    $pdf->Cell(array_sum($w), 7, 'Data tidak ditemukan.', 1, 1, 'C');
}

$pdf->Ln(10);

// TTD
$pdf->Cell(0, 7, 'Banjarmasin, ' . date('d-m-Y'), 0, 1, 'R');
$pdf->Cell(0, 7, 'Mengetahui,', 0, 1, 'R');
$pdf->Cell(0, 7, ucfirst($role), 0, 1, 'R');
$pdf->Ln(20);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 7, htmlspecialchars($_SESSION['nama'] ?? '_________________'), 0, 1, 'R');

$pdf->Output('I', 'Laporan_Pencairan_Dana_' . $dari . '_' . $sampai . '.pdf');

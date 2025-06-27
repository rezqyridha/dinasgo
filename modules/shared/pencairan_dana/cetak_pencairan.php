<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';
require_once FPDF_PATH . '/fpdf.php';

// ✅ Role protection: hanya admin dan bendahara
if (!in_array($_SESSION['role'], ['admin', 'bendahara'])) {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    header("Location: index.php?msg=invalid&obj=pencairan");
    exit;
}

// ✅ Ambil data pencairan
$stmt = $conn->prepare("
    SELECT pd.*, 
           peg.nama AS nama_pegawai, 
           p.tujuan, p.estimasi_biaya, p.tanggal_berangkat,
           u.nama AS bendahara
    FROM pencairan_dana pd
    JOIN pengajuan_perjalanan p ON pd.id_pengajuan = p.id
    JOIN pegawai peg ON p.id_pegawai = peg.id
    LEFT JOIN user u ON pd.id_bendahara = u.id
    WHERE pd.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    header("Location: index.php?msg=notfound&obj=pencairan");
}

// ✅ Ambil detail rincian
$stmtDetail = $conn->prepare("
    SELECT d.jenis_biaya, d.jumlah, d.satuan, d.harga_satuan 
    FROM rincian_biaya rb
    JOIN rincian_biaya_detail d ON rb.id = d.id_rincian
    WHERE rb.id_pengajuan = ? AND rb.status = 'disetujui'
");
$stmtDetail->bind_param("i", $data['id_pengajuan']);
$stmtDetail->execute();
$detail = $stmtDetail->get_result();

// === Mulai FPDF ===
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetMargins(20, 15, 20);

// === KOP SURAT ===
$pdf->Image($_SERVER['DOCUMENT_ROOT'] . '/dinasgo/assets/images/balai/PUPR.png', 20, 12, 12);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 6, 'KEMENTERIAN PEKERJAAN UMUM DAN PERUMAHAN RAKYAT', 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 7, 'BALAI WILAYAH SUNGAI KALIMANTAN III BANJARMASIN', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Jl. Yos Sudarso No.10, Telaga Biru, Banjarmasin Barat, Kalimantan Selatan 70117', 0, 1, 'C');
$pdf->Cell(0, 0, '', 'B', 1, 'C');
$pdf->Ln(10);

// === JUDUL ===
$pdf->SetFont('Arial', 'BU', 12);
$pdf->Cell(0, 8, 'BUKTI PENCARIAN DANA PERJALANAN DINAS', 0, 1, 'C');
$pdf->Ln(4);

// === IDENTITAS ===
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(50, 7, 'Nama Pegawai', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, $data['nama_pegawai'], 0, 1);

$pdf->Cell(50, 7, 'Tujuan Perjalanan', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, $data['tujuan'], 0, 1);

$pdf->Cell(50, 7, 'Tanggal Berangkat', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, date('d-m-Y', strtotime($data['tanggal_berangkat'])), 0, 1);

$pdf->Cell(50, 7, 'Tanggal Pencairan', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, date('d-m-Y', strtotime($data['tanggal_pencairan'])), 0, 1);

$pdf->Ln(5);

// === TABEL RINCIAN ===
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(230, 230, 230);
$pdf->SetX(20);
$pdf->Cell(10, 8, 'No', 1, 0, 'C', true);
$pdf->Cell(50, 8, 'Jenis Biaya', 1, 0, 'C', true);
$pdf->Cell(20, 8, 'Jumlah', 1, 0, 'C', true);
$pdf->Cell(25, 8, 'Satuan', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'Harga Satuan (Rp)', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'Subtotal (Rp)', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 10);
$no = 1;
$total = 0;
while ($row = $detail->fetch_assoc()) {
    $subtotal = $row['jumlah'] * $row['harga_satuan'];
    $total += $subtotal;

    $pdf->SetX(20);
    $pdf->Cell(10, 8, $no++, 1, 0, 'C');
    $pdf->Cell(50, 8, $row['jenis_biaya'], 1);
    $pdf->Cell(20, 8, $row['jumlah'], 1, 0, 'C');
    $pdf->Cell(25, 8, $row['satuan'], 1, 0, 'C');
    $pdf->Cell(35, 8, number_format($row['harga_satuan'], 0, ',', '.'), 1, 0, 'R');
    $pdf->Cell(30, 8, number_format($subtotal, 0, ',', '.'), 1, 1, 'R');
}

// === TOTAL
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetX(20);
$pdf->Cell(140, 8, 'TOTAL RINCIAN BIAYA', 1, 0, 'R');
$pdf->Cell(30, 8, 'Rp ' . number_format($total, 0, ',', '.'), 1, 1, 'R');

$pdf->Ln(8);

// === JUMLAH DANA DICAIRKAN ===
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(60, 8, 'Jumlah Dana Dicairkan', 0, 0);
$pdf->Cell(5, 8, ':', 0, 0);
$pdf->Cell(0, 8, 'Rp ' . $data['jumlah_dana'], 0, 1);

// === TTD ===
$pdf->Ln(25);
$pdf->Cell(0, 7, 'Banjarmasin, ' . date('d-m-Y'), 0, 1, 'R');
$pdf->Cell(0, 7, 'Bendahara Pengeluaran', 0, 1, 'R');
$pdf->Ln(20);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 7, $data['bendahara'] ?? '_____________________', 0, 1, 'R');

$pdf->Output('I', 'Bukti_Pencairan_' . $data['id'] . '.pdf');

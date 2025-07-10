<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';
require_once FPDF_PATH . '/fpdf.php';

// RBAC: hanya admin & bendahara
if (!in_array($_SESSION['role'], ['admin', 'bendahara'])) {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

// Validasi ID
$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    header("Location: " . BASE_URL . "/modules/shared/rincian_biaya/index.php?msg=invalid");
    exit;
}

// Validasi status
$stmt = $conn->prepare("SELECT status FROM rincian_biaya WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$status = $stmt->get_result()->fetch_assoc()['status'] ?? null;
$stmt->close();

if (!$status || !in_array($status, ['disetujui', 'selesai'])) {
    header("Location: " . BASE_URL . "/modules/shared/rincian_biaya/index.php?msg=locked");
    exit;
}

// Ambil data header (nama pegawai & nama bendahara)
$stmt = $conn->prepare("
    SELECT rb.*, 
           p.tujuan, 
           u.nama AS pembuat,
           peg.nama AS nama_pegawai,
           bend.nama AS nama_bendahara
    FROM rincian_biaya rb
    JOIN pengajuan_perjalanan p ON rb.id_pengajuan = p.id
    JOIN user u ON rb.dibuat_oleh = u.id
    JOIN user peg ON rb.id_pemilik = peg.id
    LEFT JOIN user bend ON rb.id_bendahara_verifikasi = bend.id
    WHERE rb.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    die('Data tidak ditemukan.');
}

// Detail
$detail = $conn->query("
    SELECT jenis_biaya, keterangan, jumlah, satuan, harga_satuan
    FROM rincian_biaya_detail
    WHERE id_rincian = $id
");

$nama_bendahara = $data['nama_bendahara'] ?? '(Bendahara)';

// === FPDF ===
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetMargins(20, 15, 20);

// HEADER
$pdf->Image($_SERVER['DOCUMENT_ROOT'] . '/dinasgo/assets/images/balai/PUPR.png', 20, 12, 12);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 6, 'KEMENTERIAN PEKERJAAN UMUM DAN PERUMAHAN RAKYAT', 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 7, 'BALAI WILAYAH SUNGAI KALIMANTAN III BANJARMASIN', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Jl. Yos Sudarso No.10, Telaga Biru, Banjarmasin Barat, Kalimantan Selatan 70117', 0, 1, 'C');
$pdf->Cell(0, 0, '', 'B', 1, 'C');
$pdf->Ln(10);

// JUDUL
$pdf->SetFont('Arial', 'BU', 12);
$pdf->Cell(0, 8, 'LAPORAN RINCIAN BIAYA PERJALANAN DINAS', 0, 1, 'C');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 8, 'Nomor: ' . $data['nomor_rincian'], 0, 1, 'C');
$pdf->Ln(4);

// IDENTITAS
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(50, 7, 'Tanggal Rincian', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, date('d-m-Y', strtotime($data['tanggal_rincian'])), 0, 1);

$pdf->Cell(50, 7, 'Nama Pegawai', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, $data['nama_pegawai'], 0, 1);

$pdf->Cell(50, 7, 'Tujuan Perjalanan', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, $data['tujuan'], 0, 1);

$pdf->Cell(50, 7, 'Dibuat Oleh', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, $data['pembuat'], 0, 1);

$pdf->Ln(8);

// TABEL
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(230, 230, 230);
$pdf->SetX(20);
$pdf->Cell(10, 8, 'No', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'Jenis Biaya', 1, 0, 'C', true);
$pdf->Cell(40, 8, 'Keterangan', 1, 0, 'C', true);
$pdf->Cell(15, 8, 'Jml', 1, 0, 'C', true);
$pdf->Cell(20, 8, 'Satuan', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'Harga Sat (Rp)', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'Subtotal (Rp)', 1, 1, 'C', true);

// ISI
$pdf->SetFont('Arial', '', 10);
$no = 1;
$total = 0;
while ($row = $detail->fetch_assoc()) {
    $subtotal = $row['jumlah'] * $row['harga_satuan'];
    $pdf->SetX(20);
    $pdf->Cell(10, 8, $no++, 1, 0, 'C');
    $pdf->Cell(35, 8, $row['jenis_biaya'], 1);
    $pdf->Cell(40, 8, $row['keterangan'], 1);
    $pdf->Cell(15, 8, $row['jumlah'], 1, 0, 'C');
    $pdf->Cell(20, 8, $row['satuan'], 1, 0, 'C');
    $pdf->Cell(30, 8, number_format($row['harga_satuan'], 0, ',', '.'), 1, 0, 'R');
    $pdf->Cell(30, 8, number_format($subtotal, 0, ',', '.'), 1, 1, 'R');
    $total += $subtotal;
}

// TOTAL
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetX(20);
$pdf->Cell(150, 8, 'TOTAL', 1, 0, 'R');
$pdf->Cell(30, 8, 'Rp ' . number_format($total, 0, ',', '.'), 1, 1, 'R');

$pdf->Ln(15);

// TTD
$pdf->Cell(0, 7, 'Banjarmasin, ' . date('d-m-Y'), 0, 1, 'R');
$pdf->Cell(0, 7, 'Disetujui Oleh,', 0, 1, 'R');
$pdf->Cell(0, 7, 'Bendahara Pengeluaran', 0, 1, 'R');
$pdf->Ln(20);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 7, $nama_bendahara, 0, 1, 'R');

$pdf->Output('I', 'Rincian_Biaya_' . $data['nomor_rincian'] . '.pdf');

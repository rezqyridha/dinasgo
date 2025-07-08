<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';
require_once FPDF_PATH . '/fpdf.php';

// Hanya bendahara atau admin boleh cetak
if (!in_array($_SESSION['role'], ['bendahara', 'admin'])) {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    die('ID pencairan tidak valid.');
}

// Ambil data lengkap
$stmt = $conn->prepare("
    SELECT pd.*, 
           pp.tujuan, pp.tanggal_berangkat, pp.tanggal_kembali,
           rb.nomor_rincian, rb.jumlah_total AS total_rincian,
           u.nama AS nama_bendahara 
    FROM pencairan_dana pd
    JOIN pengajuan_perjalanan pp ON pd.id_pengajuan = pp.id
    JOIN rincian_biaya rb ON rb.id_pengajuan = pp.id
    JOIN user u ON pd.id_bendahara = u.id
    WHERE pd.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    die('Data tidak ditemukan.');
}

// Ambil detail rincian
$detail = $conn->query("
    SELECT jenis_biaya, jumlah, satuan, harga_satuan
    FROM rincian_biaya_detail d
    JOIN rincian_biaya rb ON d.id_rincian = rb.id
    WHERE rb.id_pengajuan = {$data['id_pengajuan']} AND rb.status = 'disetujui'
");

function fmt($tgl)
{
    return date('d-m-Y', strtotime($tgl));
}

// === PDF ===
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetMargins(20, 15, 20);

// === KOP ===
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
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 7, 'Nomor: PCD-' . str_pad($data['id'], 3, '0', STR_PAD_LEFT) . '/' . date('Y', strtotime($data['tanggal_pencairan'])), 0, 1, 'C');
$pdf->Ln(4);

// === IDENTITAS ===
$pdf->Cell(50, 7, 'Nomor Rincian', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, $data['nomor_rincian'], 0, 1);

$pdf->Cell(50, 7, 'Tujuan Perjalanan', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, $data['tujuan'], 0, 1);

$pdf->Cell(50, 7, 'Periode', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, fmt($data['tanggal_berangkat']) . ' s.d. ' . fmt($data['tanggal_kembali']), 0, 1);

$pdf->Cell(50, 7, 'Tanggal Pencairan', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, fmt($data['tanggal_pencairan']), 0, 1);

$pdf->Ln(4);

// === TABEL DETAIL ===
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(230, 230, 230);
$pdf->SetX(20);
$pdf->Cell(10, 8, 'No', 1, 0, 'C', true);
$pdf->Cell(50, 8, 'Jenis Biaya', 1, 0, 'C', true);
$pdf->Cell(20, 8, 'Jumlah', 1, 0, 'C', true);
$pdf->Cell(25, 8, 'Satuan', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'Harga Satuan', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'Subtotal', 1, 1, 'C', true);

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

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetX(20);
$pdf->Cell(140, 8, 'TOTAL RINCIAN', 1, 0, 'R');
$pdf->Cell(30, 8, 'Rp ' . number_format($total, 0, ',', '.'), 1, 1, 'R');

$pdf->Ln(5);

// Karena jumlah_dana disimpan VARCHAR, pastikan diformat dulu
$jumlah_cair = preg_replace('/[^0-9]/', '', $data['jumlah_dana']);
$jumlah_cair = (int) $jumlah_cair;

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(60, 8, 'Jumlah Dana Dicairkan', 0, 0);
$pdf->Cell(5, 8, ':', 0, 0);
$pdf->Cell(0, 8, 'Rp ' . number_format($jumlah_cair, 0, ',', '.'), 0, 1);


// === TTD ===
$pdf->Ln(25);
$pdf->Cell(0, 7, 'Banjarmasin, ' . fmt($data['tanggal_pencairan']), 0, 1, 'R');
$pdf->Cell(0, 7, 'Bendahara Pengeluaran', 0, 1, 'R');
$pdf->Ln(20);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 7, $data['nama_bendahara'], 0, 1, 'R');

$pdf->Output('I', 'Pencairan_Dana_' . $data['id'] . '.pdf');

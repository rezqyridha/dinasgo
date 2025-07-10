<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';
require_once FPDF_PATH . '/fpdf.php';

// Hanya admin & bendahara boleh cetak
if (!in_array($_SESSION['role'], ['admin', 'bendahara'])) {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    die('ID tidak valid.');
}

// Ambil data pencairan
$stmt = $conn->prepare("
    SELECT pd.*, 
           pp.tujuan, pp.tanggal_berangkat, pp.tanggal_kembali,
           rb.nomor_rincian, rb.jumlah_total AS total_rincian,
           bendahara.nama AS nama_bendahara,
           admin.nama AS nama_admin
    FROM pencairan_dana pd
    JOIN pengajuan_perjalanan pp ON pd.id_pengajuan = pp.id
    JOIN rincian_biaya rb ON rb.id = pd.id_rincian_biaya
    LEFT JOIN user bendahara ON pd.id_bendahara = bendahara.id
    LEFT JOIN user admin ON pd.id_admin_finalisasi = admin.id
    WHERE pd.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    die('Data tidak ditemukan.');
}

// Detail rincian
$detail = $conn->query("
    SELECT jenis_biaya, jumlah, satuan, harga_satuan 
    FROM rincian_biaya_detail 
    WHERE id_rincian = {$data['id_rincian_biaya']}
");

function fmt($tgl)
{
    return date('d-m-Y', strtotime($tgl));
}

// === FPDF ===
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

// === INFORMASI ===
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(50, 7, 'Nomor Rincian', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, $data['nomor_rincian'], 0, 1);

$pdf->Cell(50, 7, 'Tujuan Perjalanan', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, $data['tujuan'], 0, 1);

$pdf->Cell(50, 7, 'Periode', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, fmt($data['tanggal_berangkat']) . ' s.d. ' . fmt($data['tanggal_kembali']), 0, 1);

$pdf->Cell(50, 7, 'Status Pencairan', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, ucfirst($data['status']), 0, 1);

$pdf->Cell(50, 7, 'Tanggal Pencairan', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, fmt($data['tanggal_pencairan']), 0, 1);

$pdf->Cell(50, 7, 'Tanggal Finalisasi', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, ($data['tanggal_finalisasi'] ? fmt($data['tanggal_finalisasi']) : '-'), 0, 1);

$pdf->Ln(4);

// === TABEL ===
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

$pdf->Ln(4);

$jumlah_cair = preg_replace('/[^0-9]/', '', $data['jumlah_dana']);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(60, 8, 'Jumlah Dana Dicairkan', 0, 0);
$pdf->Cell(5, 8, ':', 0, 0);
$pdf->Cell(0, 8, 'Rp ' . number_format($jumlah_cair, 0, ',', '.'), 0, 1);

$pdf->Ln(15);

// === TTD Bendahara & Admin Sampingan ===
$pdf->SetFont('Arial', '', 11);

$y = $pdf->GetY();
$pdf->SetX(20);
$pdf->Cell(90, 6, 'Banjarmasin, ' . date('d-m-Y'), 0, 0, 'L');
$pdf->Cell(0, 6, 'Disetujui oleh:', 0, 1, 'R');

$pdf->SetX(20);
$pdf->Cell(90, 6, 'Bendahara', 0, 0, 'L');
$pdf->Cell(0, 6, 'Admin Finalisasi', 0, 1, 'R');

$pdf->Ln(18);

$pdf->SetFont('Arial', 'B', 11);
$pdf->SetX(20);
$pdf->Cell(90, 6, $data['nama_bendahara'] ?? '-', 0, 0, 'L');
$pdf->Cell(0, 6, $data['nama_admin'] ?? '-', 0, 1, 'R');

$pdf->Output('I', 'Pencairan_Dana_' . $data['id'] . '.pdf');

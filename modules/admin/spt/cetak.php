<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';
require_once FPDF_PATH . '/fpdf.php';

//  Hanya admin yang boleh cetak
if ($_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

//  Ambil ID
$id = (int) ($_GET['id'] ?? 0);

//  Validasi status SPT sebelum cetak
$cek = $conn->prepare("SELECT status FROM spt WHERE id = ?");
$cek->bind_param("i", $id);
$cek->execute();
$resStatus = $cek->get_result();
$status = $resStatus->fetch_assoc()['status'] ?? null;

if (!$status || $status === 'dibatalkan') {
    header("Location: " . BASE_URL . "/modules/shared/spt/index.php?msg=locked&obj=spt");
    exit;
}

// Ambil data lengkap JOIN ke kepala
$stmt = $conn->prepare("
    SELECT spt.*, 
           peg.nama AS nama_pegawai, peg.nip, peg.jabatan, 
           p.tujuan, p.tanggal_berangkat, p.tanggal_kembali,
           k.nama AS penandatangan, k.jabatan AS jabatan_kepala
    FROM spt
    JOIN pengajuan_perjalanan p ON spt.id_pengajuan = p.id
    JOIN pegawai peg ON p.id_pegawai = peg.id
    LEFT JOIN kepala k ON spt.ditandatangani_oleh = k.id
    WHERE spt.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    header("Location: " . BASE_URL . "/modules/shared/spt/index.php?msg=invalid&obj=spt");
    exit;
}

// ============================
//  FPDF
// ============================
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
$pdf->Cell(0, 8, 'SURAT PERINTAH TUGAS', 0, 1, 'C');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 8, 'Nomor: ' . $data['nomor_spt'], 0, 1, 'C');
$pdf->Ln(4);

// === IDENTITAS ===
$pdf->SetFont('Arial', '', 11);
$pdf->MultiCell(0, 7, 'Yang bertanda tangan di bawah ini, menugaskan kepada:');
$pdf->Ln(2);

$pdf->Cell(50, 7, 'Nama', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, $data['nama_pegawai'], 0, 1);

$pdf->Cell(50, 7, 'NIP', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, $data['nip'], 0, 1);

$pdf->Cell(50, 7, 'Jabatan', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, $data['jabatan'], 0, 1);

$pdf->Ln(5);
$pdf->MultiCell(0, 7, "Untuk melaksanakan tugas perjalanan dinas dengan rincian sebagai berikut:");
$pdf->Ln(1);

$pdf->Cell(50, 7, 'Maksud Perjalanan', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->MultiCell(0, 7, $data['maksud_perjalanan']);

$pdf->Cell(50, 7, 'Tujuan', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, $data['tujuan'], 0, 1);

$pdf->Cell(50, 7, 'Tanggal Berangkat', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, date('d-m-Y', strtotime($data['tanggal_berangkat'])), 0, 1);

$pdf->Cell(50, 7, 'Tanggal Kembali', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, date('d-m-Y', strtotime($data['tanggal_kembali'])), 0, 1);

$pdf->Cell(50, 7, 'Transportasi', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, $data['transportasi'], 0, 1);

$pdf->Ln(10);

// === PENUTUP ===
$pdf->MultiCell(0, 7, "Demikian Surat Perintah Tugas ini dibuat agar dilaksanakan sebagaimana mestinya.");
$pdf->Ln(15);

// === TTD ===
$pdf->Cell(0, 7, 'Banjarmasin, ' . date('d-m-Y', strtotime($data['tanggal_spt'])), 0, 1, 'R');

// Jika ada nama kepala, pakai nama dan jabatan kepala
if (!empty($data['penandatangan'])) {
    $pdf->Cell(0, 7, $data['jabatan_kepala'], 0, 1, 'R');
    $pdf->Ln(20);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 7, $data['penandatangan'], 0, 1, 'R');
} else {
    $pdf->Cell(0, 7, '(Pejabat Penandatangan)', 0, 1, 'R');
}

// === Output PDF ===
$pdf->Output('I', 'SPT_' . $data['nomor_spt'] . '.pdf');

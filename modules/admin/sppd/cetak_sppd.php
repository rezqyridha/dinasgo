<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';
require_once FPDF_PATH . '/fpdf.php';

if (!in_array($_SESSION['role'], ['admin', 'atasan'])) {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    die('ID tidak valid.');
}

// Ambil data lengkap SPPD
$query = $conn->prepare("
    SELECT sp.*, 
           pg.nama AS nama_pegawai, pg.jabatan,
           pp.tujuan, pp.tanggal_berangkat, pp.tanggal_kembali, pp.keperluan, pp.estimasi_biaya,
           spt.nomor_spt, spt.tanggal_spt, spt.maksud_perjalanan, spt.lama_perjalanan, spt.transportasi
    FROM sppd sp
    JOIN pengajuan_perjalanan pp ON sp.id_pengajuan = pp.id
    JOIN pegawai pg ON pp.id_pegawai = pg.id
    LEFT JOIN spt ON spt.id_pengajuan = pp.id
    WHERE sp.id = ?
");
$query->bind_param("i", $id);
$query->execute();
$data = $query->get_result()->fetch_assoc();

if (!$data) {
    die("Data tidak ditemukan.");
}

// Format tanggal
function fmt($tgl)
{
    return date('d F Y', strtotime($tgl));
}

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetMargins(20, 15, 20);

// === HEADER INSTANSI ===
$pdf->Image($_SERVER['DOCUMENT_ROOT'] . '/dinasgo/assets/images/balai/PUPR.png', 20, 10, 12);
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
$pdf->Cell(0, 8, 'SURAT PERINTAH PERJALANAN DINAS (SPPD)', 0, 1, 'C');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 7, 'Nomor: ' . $data['nomor_sppd'], 0, 1, 'C');
$pdf->Ln(8);

// === NARASI UTAMA ===
$pdf->SetFont('Arial', '', 11);
$pdf->MultiCell(
    0,
    7,
    "Diberikan kepada:\n" .
        "Nama      : " . $data['nama_pegawai'] . "\n" .
        "Jabatan   : " . $data['jabatan'] . "\n\n" .
        "Untuk melaksanakan perjalanan dinas ke " . $data['tujuan'] . ", selama " . $data['lama_perjalanan'] . ", mulai tanggal " . fmt($data['tanggal_berangkat']) .
        " s.d. " . fmt($data['tanggal_kembali']) . " dalam rangka " . $data['keperluan'] . ".\n\n" .
        "Berdasarkan " . $data['nomor_spt'] . " tanggal " . fmt($data['tanggal_spt']) . ".",
    0
);

// === INFO TAMBAHAN ===
$pdf->Ln(8);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(50, 7, 'Estimasi Biaya', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, 'Rp ' . number_format($data['estimasi_biaya'], 0, ',', '.'), 0, 1);

$pdf->Cell(50, 7, 'Transportasi', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, $data['transportasi'], 0, 1);

$pdf->Cell(50, 7, 'Catatan Tambahan', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, $data['catatan'], 0, 1);

// === PENUTUP ===
$pdf->Ln(20);
$pdf->Cell(0, 7, 'Dikeluarkan di Banjarmasin pada tanggal: ' . fmt($data['tanggal_terbit']), 0, 1, 'R');
$pdf->Cell(0, 7, 'Pejabat Pemberi Perintah', 0, 1, 'R');
$pdf->Ln(20);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 7, '(Nama Lengkap Pejabat)', 0, 1, 'R');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 6, 'NIP. 1234567890', 0, 1, 'R');

$pdf->Output('I', 'SPPD_' . $data['nomor_sppd'] . '.pdf');

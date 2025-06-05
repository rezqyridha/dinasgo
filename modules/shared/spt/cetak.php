<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once BASE_PATH . '/auth/session.php';
require_once BASE_PATH . '/config/koneksi.php';

// Ambil ID SPT
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Cek data
$query = "
    SELECT s.*, 
           u.nama AS pegawai, 
           u.nip, 
           u.jabatan, 
           a.nama AS penandatangan, 
           a.jabatan AS jabatan_atasan, 
           p.tujuan, 
           p.tanggal_berangkat, 
           p.tanggal_kembali
    FROM spt s
    JOIN pengajuan_perjalanan p ON s.id_pengajuan = p.id
    JOIN user u ON p.id_pegawai = u.id
    LEFT JOIN user a ON s.ditandatangani_oleh = a.id
    WHERE s.id = $id
";
$result = $conn->query($query);
$data = $result->fetch_assoc();

if (!$data) {
    echo "<script>alert('Data tidak ditemukan'); window.close();</script>";
    exit;
}

// Format tanggal Indonesia
function formatTanggal($tanggal)
{
    return date('d-m-Y', strtotime($tanggal));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak SPT - <?= $data['nomor_spt'] ?></title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 40px;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        table {
            width: 100%;
            margin-top: 20px;
        }

        td {
            vertical-align: top;
        }

        .ttd {
            margin-top: 60px;
            width: 40%;
            float: right;
            text-align: center;
        }

        .line {
            border-top: 2px solid #000;
            margin-top: 20px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body onload="window.print()">

    <div class="center">
        <h3>PEMERINTAH KOTA BANJARMASIN</h3>
        <h4>SURAT PERINTAH TUGAS</h4>
        <p>Nomor: <strong><?= htmlspecialchars($data['nomor_spt']) ?></strong></p>
    </div>

    <div class="line"></div>

    <p>Yang bertanda tangan di bawah ini:</p>
    <table>
        <tr>
            <td width="25%">Nama</td>
            <td>: <?= $data['penandatangan'] ?? '(Belum ditandatangani)' ?></td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>: <?= $data['jabatan_atasan'] ?? '-' ?></td>
        </tr>
    </table>

    <p>Memberikan tugas kepada:</p>
    <table>
        <tr>
            <td width="25%">Nama</td>
            <td>: <?= $data['pegawai'] ?></td>
        </tr>
        <tr>
            <td>NIP</td>
            <td>: <?= $data['nip'] ?></td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>: <?= $data['jabatan'] ?></td>
        </tr>
    </table>

    <p>Untuk melaksanakan perjalanan dinas dalam rangka:</p>
    <p><strong><?= nl2br(htmlspecialchars($data['maksud_perjalanan'])) ?></strong></p>

    <p>
        Ke tujuan: <strong><?= $data['tujuan'] ?></strong><br>
        Lama perjalanan: <strong><?= $data['lama_perjalanan'] ?></strong><br>
        Dari tanggal <strong><?= formatTanggal($data['tanggal_berangkat']) ?></strong>
        s.d <strong><?= formatTanggal($data['tanggal_kembali']) ?></strong><br>
        Transportasi: <strong><?= $data['transportasi'] ?></strong>
    </p>

    <div class="ttd">
        <p>Banjarmasin, <?= formatTanggal($data['tanggal_spt']) ?></p>
        <p><?= $data['jabatan_atasan'] ?? '(Belum ditandatangani)' ?></p>
        <br><br><br><br>
        <p class="bold underline"><?= $data['penandatangan'] ?? '__________________' ?></p>
    </div>

</body>

</html>
<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Tambah Pencairan Dana';
$role = $_SESSION['role'] ?? '';
$id_user = $_SESSION['id_user'] ?? 0;

// Proteksi hanya bendahara
if ($role !== 'bendahara') {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

// Helper untuk bersihkan format rupiah
function cleanRupiah($nominal)
{
    $clean = str_replace(['Rp', ' ', '.'], '', $nominal);
    return str_replace(',00', '', $clean);
}

$error = '';
$input = [
    'id_pengajuan' => '',
    'id_rincian_biaya' => '',
    'jumlah_dana' => '',
    'tanggal_pencairan' => date('Y-m-01') // default GU tgl 1 bulan ini
];

// Ambil daftar pengajuan yang valid
$pengajuan = $conn->query("
    SELECT p.id, peg.nama, p.tujuan, p.tanggal_berangkat
    FROM pengajuan_perjalanan p
    JOIN pegawai peg ON p.id_pegawai = peg.id
    WHERE p.status = 'disetujui'
      AND EXISTS (
          SELECT 1 FROM rincian_biaya rb
          WHERE rb.id_pengajuan = p.id AND rb.status = 'disetujui'
      )
      AND NOT EXISTS (
          SELECT 1 FROM pencairan_dana pd
          WHERE pd.id_pengajuan = p.id
      )
    ORDER BY p.tanggal_berangkat DESC
");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($input as $key => $_) {
        $input[$key] = trim($_POST[$key] ?? '');
    }

    // Bersihkan format rupiah
    $raw = cleanRupiah($input['jumlah_dana']);
    $input['jumlah_dana'] = number_format((int) $raw, 0, ',', '.');

    if (in_array('', $input)) {
        $error = "Semua field wajib diisi.";
    } else {
        $stmt = $conn->prepare("INSERT INTO pencairan_dana (id_pengajuan, id_rincian_biaya, id_bendahara, jumlah_dana, tanggal_pencairan) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiss", $input['id_pengajuan'], $input['id_rincian_biaya'], $id_user, $input['jumlah_dana'], $input['tanggal_pencairan']);

        if ($stmt->execute()) {
            header("Location: " . BASE_URL . "/modules/shared/pencairan_dana/index.php?msg=added&obj=pencairan");
            exit;
        } else {
            header("Location: " . BASE_URL . "/modules/shared/pencairan_dana/index.php?msg=error&obj=pencairan");
            exit;
        }
    }
}

require_once LAYOUTS_PATH . '/head.php';
require_once LAYOUTS_PATH . '/header.php';
require_once LAYOUTS_PATH . '/topbar.php';
require_once LAYOUTS_PATH . '/sidebar.php';
?>

<div class="main-content app-content">
    <div class="container-fluid">
        <h4 class="mb-4"><?= htmlspecialchars($pageTitle) ?></h4>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="id_pengajuan" class="form-label">Pilih Pengajuan</label>
                        <select name="id_pengajuan" id="id_pengajuan" class="form-select" required onchange="fetchPengajuan(this.value)">
                            <option value="">-- Pilih Pengajuan --</option>
                            <?php while ($row = $pengajuan->fetch_assoc()): ?>
                                <option value="<?= $row['id'] ?>" <?= $input['id_pengajuan'] == $row['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['nama']) ?> - Tujuan (<?= htmlspecialchars($row['tujuan']) ?>) - <?= date('d-m-Y', strtotime($row['tanggal_berangkat'])) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div id="info_pengajuan" style="display:none;">
                        <div class="mb-3">
                            <label class="form-label">Estimasi Biaya</label>
                            <input type="text" id="estimasi_biaya" class="form-control" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Total Rincian Biaya</label>
                            <input type="text" id="rincian_total" class="form-control" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Detail Rincian</label>
                            <div id="detail_rincian" class="border p-2 bg-light rounded small"></div>
                        </div>
                    </div>

                    <input type="hidden" name="id_rincian_biaya" id="id_rincian_biaya">

                    <div class="mb-3">
                        <label for="jumlah_dana" class="form-label">Jumlah Dana Dicairkan</label>
                        <input type="text" name="jumlah_dana" id="jumlah_dana" class="form-control" value="<?= htmlspecialchars($input['jumlah_dana']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="tanggal_pencairan" class="form-label">Tanggal Pencairan</label>
                        <input type="date" name="tanggal_pencairan" id="tanggal_pencairan" class="form-control" value="<?= htmlspecialchars($input['tanggal_pencairan']) ?>" required>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary"><i class="fe fe-save me-1"></i> Simpan</button>
                        <a href="<?= BASE_URL ?>/modules/shared/pencairan_dana/index.php" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once LAYOUTS_PATH . '/footer.php';
require_once LAYOUTS_PATH . '/scripts.php';
?>

<script>
    function fetchPengajuan(id) {
        if (!id) return;

        fetch("ajax_get_info_pengajuan.php?id=" + id)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById("info_pengajuan").style.display = "block";
                    document.getElementById("estimasi_biaya").value = "Rp " + data.estimasi;
                    document.getElementById("rincian_total").value = "Rp " + data.total;
                    document.getElementById("jumlah_dana").value = formatRupiah(String(Math.round(data.total_raw)));
                    document.getElementById("detail_rincian").innerHTML = data.detail_html;
                    document.getElementById("id_rincian_biaya").value = data.id_rincian;
                }
            });
    }

    function formatRupiah(angka, prefix = 'Rp ') {
        let number_string = angka.replace(/[^,\d]/g, '').toString(),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] !== undefined ? rupiah + ',' + split[1].substring(0, 2) : rupiah;
        return prefix + rupiah;
    }

    document.getElementById('jumlah_dana').addEventListener('input', function(e) {
        let input = e.target;
        let angka = input.value.replace(/[^,\d]/g, '');
        input.value = formatRupiah(angka);
    });
</script>
<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Tambah Rincian Biaya';
$role = $_SESSION['role'];
$id_user = $_SESSION['id_user'];

if ($role !== 'admin') {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

// Ambil pengajuan yang sudah disetujui & belum punya rincian
$pengajuanList = $conn->query("
    SELECT p.id, peg.nama, p.tujuan, p.tanggal_berangkat
    FROM pengajuan_perjalanan p
    JOIN pegawai peg ON p.id_pegawai = peg.id
    WHERE p.status = 'disetujui'
      AND p.id NOT IN (SELECT id_pengajuan FROM rincian_biaya)
");

// Fungsi penomoran otomatis
function generateNomorRincian($conn)
{
    $year = date('Y');
    $prefix = 'RB-';
    $query = $conn->query("SELECT nomor_rincian FROM rincian_biaya WHERE nomor_rincian LIKE '{$prefix}___/{$year}' ORDER BY nomor_rincian DESC LIMIT 1");
    if ($row = $query->fetch_assoc()) {
        // Ambil 3 digit angka setelah 'RB-'
        $last = (int) substr($row['nomor_rincian'], 3, 3);
        $new  = $last + 1;
    } else {
        $new = 1;
    }
    return $prefix . str_pad($new, 3, '0', STR_PAD_LEFT) . '/' . $year;
}

$nomor_rincian = generateNomorRincian($conn);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pengajuan   = (int) $_POST['id_pengajuan'];
    $tanggal_rincian = $_POST['tanggal_rincian'];
    $detail         = $_POST['detail'] ?? [];

    if (!$id_pengajuan || !$tanggal_rincian || count($detail) === 0) {
        $error = 'Pengajuan, tanggal, dan minimal satu rincian wajib diisi.';
    } else {
        // Validasi isi detail
        $valid = true;
        foreach ($detail as $item) {
            if (
                empty($item['jenis_biaya']) || empty($item['jumlah']) ||
                empty($item['satuan']) || empty($item['harga_satuan'])
            ) {
                $valid = false;
                break;
            }
        }

        if (!$valid) {
            $error = 'Setiap baris rincian harus diisi lengkap.';
        } else {
            // Hitung total
            $total = 0;
            foreach ($detail as $item) {
                $jumlah = (int) $item['jumlah'];
                $harga = (float) str_replace('.', '', $item['harga_satuan']);
                $total += $jumlah * $harga;
            }

            $stmt = $conn->prepare("INSERT INTO rincian_biaya (id_pengajuan, nomor_rincian, tanggal_rincian, jumlah_total, dibuat_oleh) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issdi", $id_pengajuan, $nomor_rincian, $tanggal_rincian, $total, $id_user);
            $stmt->execute();
            $id_rincian = $stmt->insert_id;

            $stmtDetail = $conn->prepare("INSERT INTO rincian_biaya_detail (id_rincian, jenis_biaya, keterangan, jumlah, satuan, harga_satuan) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($detail as $item) {
                $stmtDetail->bind_param(
                    "issisd",
                    $id_rincian,
                    $item['jenis_biaya'],
                    $item['keterangan'],
                    $item['jumlah'],
                    $item['satuan'],
                    str_replace('.', '', $item['harga_satuan'])
                );
                $stmtDetail->execute();
            }

            header("Location: " . BASE_URL . "/modules/shared/rincian_biaya/index.php?msg=added&obj=rincian");
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
        <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
            <h4 class="mb-0">Form Tambah Rincian Biaya</h4>
            <a href="<?= BASE_URL ?>/modules/shared/rincian_biaya/index.php" class="btn btn-secondary">Kembali</a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="mb-3">
                        <label for="id_pengajuan" class="form-label">Pilih Pengajuan</label>
                        <select name="id_pengajuan" id="id_pengajuan" class="form-select" required>
                            <option value="">-- Pilih Pengajuan --</option>
                            <?php while ($row = $pengajuanList->fetch_assoc()): ?>
                                <option value="<?= $row['id'] ?>"><?= $row['nama'] ?> - <?= $row['tujuan'] ?> (<?= $row['tanggal_berangkat'] ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nomor Rincian</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($nomor_rincian) ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="tanggal_rincian" class="form-label">Tanggal</label>
                        <input type="date" name="tanggal_rincian" id="tanggal_rincian" class="form-control" required>
                    </div>
                    <hr>
                    <h5>Detail Biaya</h5>
                    <table class="table table-bordered" id="table-detail">
                        <thead class="table-light">
                            <tr>
                                <th>Jenis Pengeluaran</th>
                                <th>Keterangan Tambahan</th>
                                <th>Jumlah</th>
                                <th>Satuan</th>
                                <th>Biaya Satuan (Rp)</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="detail-body">
                            <tr>
                                <td><input type="text" name="detail[0][jenis_biaya]" class="form-control" placeholder="Contoh: Uang Harian" required></td>
                                <td><input type="text" name="detail[0][keterangan]" class="form-control" placeholder="Contoh: 2 hari di Tapin"></td>
                                <td><input type="number" name="detail[0][jumlah]" class="form-control " min="1" required></td>
                                <td><input type="text" name="detail[0][satuan]" class="form-control" placeholder="Contoh: hari" required></td>
                                <td>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="detail[0][harga_satuan]" class="form-control text-end rupiah" required>
                                    </div>
                                </td>
                                <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Hapus</button></td>
                            </tr>
                        </tbody>

                    </table>
                    <button type="button" class="btn btn-info btn-sm mt-4" onclick="addRow()">Tambah Baris</button>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary"><i class="fe fe-save me-1"></i> Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    let rowIndex = 1;

    function addRow() {
        const tbody = document.getElementById('detail-body');
        const row = document.createElement('tr');
        row.innerHTML = `
        <td><input type="text" name="detail[${rowIndex}][jenis_biaya]" class="form-control" placeholder="Contoh: Uang Harian" required></td>
        <td><input type="text" name="detail[${rowIndex}][keterangan]" class="form-control" placeholder="Contoh: 2 hari di Tapin"></td>
        <td><input type="number" name="detail[${rowIndex}][jumlah]" class="form-control" min="1" required></td>
        <td><input type="text" name="detail[${rowIndex}][satuan]" class="form-control" placeholder="Contoh: hari" required></td>
        <td>
            <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="text" name="detail[${rowIndex}][harga_satuan]" class="form-control text-end rupiah" placeholder="Contoh: 250000" required>
            </div>
        </td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Hapus</button></td>
    `;
        tbody.appendChild(row);
        rowIndex++;
    }


    function formatRupiah(el) {
        el.value = el.value.replace(/\D/g, '')
            .replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // Format angka saat user mengetik
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('rupiah')) {
            formatRupiah(e.target);
        }
    });

    // Bersihkan format saat submit
    document.querySelector('form').addEventListener('submit', function() {
        document.querySelectorAll('.rupiah').forEach(function(input) {
            input.value = input.value.replace(/\./g, ''); // Hapus titik
        });
    });

    // Revisi fungsi removeRow
    function removeRow(btn) {
        const tbody = document.getElementById('detail-body');
        if (tbody.rows.length > 1) {
            btn.closest('tr').remove();
        } else {
            notifier.show('Minimal satu baris rincian harus ada.', 'danger', 1000);
        }
    }
</script>

<?php
require_once LAYOUTS_PATH . '/footer.php';
require_once LAYOUTS_PATH . '/scripts.php';
?>
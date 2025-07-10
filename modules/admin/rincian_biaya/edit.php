<?php
require_once __DIR__ . '/../../../config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once AUTH_PATH . '/session.php';

$pageTitle = 'Edit Rincian Biaya';
$role = $_SESSION['role'] ?? '';
$id_user = $_SESSION['id_user'] ?? 0;

if ($role !== 'admin') {
    header("Location: " . BASE_URL . "/unauthorized.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: index.php?msg=invalid");
    exit;
}

// Ambil data rincian biaya
$stmt = $conn->prepare("SELECT * FROM rincian_biaya WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    header("Location: index.php?msg=notfound");
    exit;
}

// Ambil detail
$stmt = $conn->prepare("SELECT * FROM rincian_biaya_detail WHERE id_rincian = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$detail = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Ambil daftar pengajuan
$pengajuanList = $conn->query("
    SELECT p.id, peg.nama, p.tujuan, p.tanggal_berangkat
    FROM pengajuan_perjalanan p
    JOIN pegawai peg ON p.id_pegawai = peg.id
    WHERE p.status = 'disetujui' OR p.id = {$data['id_pengajuan']}
");

// === Proses Update ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pengajuan = (int) $_POST['id_pengajuan'];
    $tanggal_rincian = $_POST['tanggal_rincian'];
    $detail = $_POST['detail'] ?? [];

    if (!$id_pengajuan || !$tanggal_rincian || count($detail) === 0) {
        header("Location: edit.php?id=$id&msg=kosong");
        exit;
    }

    $valid = true;
    $total = 0;

    foreach ($detail as $item) {
        if (empty($item['jenis_biaya']) || empty($item['jumlah']) || empty($item['satuan']) || empty($item['harga_satuan'])) {
            $valid = false;
            break;
        }
        $jumlah = (int) $item['jumlah'];
        $harga = (float) str_replace('.', '', $item['harga_satuan']);
        $total += $jumlah * $harga;
    }

    if (!$valid) {
        header("Location: edit.php?id=$id&msg=incompletedetail");
        exit;
    }

    $stmt = $conn->prepare("UPDATE rincian_biaya SET id_pengajuan=?, tanggal_rincian=?, jumlah_total=?, updated_at=NOW() WHERE id=?");
    $stmt->bind_param("ssdi", $id_pengajuan, $tanggal_rincian, $total, $id);
    $stmt->execute();

    $conn->query("DELETE FROM rincian_biaya_detail WHERE id_rincian = $id");

    $stmtDetail = $conn->prepare("INSERT INTO rincian_biaya_detail (id_rincian, jenis_biaya, keterangan, jumlah, satuan, harga_satuan) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($detail as $item) {
        $stmtDetail->bind_param(
            "issisd",
            $id,
            $item['jenis_biaya'],
            $item['keterangan'],
            $item['jumlah'],
            $item['satuan'],
            str_replace('.', '', $item['harga_satuan'])
        );
        $stmtDetail->execute();
    }

    header("Location: " . BASE_URL . "/modules/shared/rincian_biaya/index.php?msg=updated");
    exit;
}

require_once LAYOUTS_PATH . '/head.php';
require_once LAYOUTS_PATH . '/header.php';
require_once LAYOUTS_PATH . '/topbar.php';
require_once LAYOUTS_PATH . '/sidebar.php';
?>

<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
            <h4 class="mb-0"><?= htmlspecialchars($pageTitle) ?></h4>
            <a href="<?= BASE_URL ?>/modules/shared/rincian_biaya/index.php" class="btn btn-secondary">Kembali</a>
        </div>

        <form method="POST">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Pengajuan</label>
                        <select name="id_pengajuan" class="form-select" disabled>
                            <option value="">-- Pilih Pengajuan --</option>
                            <?php while ($row = $pengajuanList->fetch_assoc()): ?>
                                <option value="<?= $row['id'] ?>" <?= $row['id'] == $data['id_pengajuan'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['nama']) ?> - <?= htmlspecialchars($row['tujuan']) ?> (<?= $row['tanggal_berangkat'] ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <input type="hidden" name="id_pengajuan" value="<?= $data['id_pengajuan'] ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nomor Rincian</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($data['nomor_rincian']) ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="tanggal_rincian" class="form-control" value="<?= htmlspecialchars($data['tanggal_rincian']) ?>" required>
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
                            <?php foreach ($detail as $i => $item): ?>
                                <tr>
                                    <td><input type="text" name="detail[<?= $i ?>][jenis_biaya]" class="form-control" value="<?= htmlspecialchars($item['jenis_biaya']) ?>" required></td>
                                    <td><input type="text" name="detail[<?= $i ?>][keterangan]" class="form-control" value="<?= htmlspecialchars($item['keterangan']) ?>"></td>
                                    <td><input type="number" name="detail[<?= $i ?>][jumlah]" class="form-control" value="<?= $item['jumlah'] ?>" required></td>
                                    <td><input type="text" name="detail[<?= $i ?>][satuan]" class="form-control" value="<?= htmlspecialchars($item['satuan']) ?>" required></td>
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" name="detail[<?= $i ?>][harga_satuan]" class="form-control rupiah"
                                                value="<?= number_format($item['harga_satuan'], 0, ',', '.') ?>" required>
                                        </div>
                                    </td>
                                    <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Hapus</button></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-info btn-sm mt-4" onclick="addRow()">Tambah Baris</button>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    let rowIndex = <?= count($detail) ?>;

    function addRow() {
        const tbody = document.getElementById('detail-body');
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><input type="text" name="detail[${rowIndex}][jenis_biaya]" class="form-control" required></td>
            <td><input type="text" name="detail[${rowIndex}][keterangan]" class="form-control"></td>
            <td><input type="number" name="detail[${rowIndex}][jumlah]" class="form-control" min="1" required></td>
            <td><input type="text" name="detail[${rowIndex}][satuan]" class="form-control" required></td>
            <td>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="text" name="detail[${rowIndex}][harga_satuan]" class="form-control rupiah" required>
                </div>
            </td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Hapus</button></td>
        `;
        tbody.appendChild(row);
        rowIndex++;
    }

    function removeRow(btn) {
        const tbody = document.getElementById('detail-body');
        if (tbody.rows.length > 1) {
            btn.closest('tr').remove();
        } else {
            alert('Minimal satu baris rincian harus ada.');
        }
    }

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('rupiah')) {
            e.target.value = e.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
    });
</script>

<?php
require_once LAYOUTS_PATH . '/footer.php';
require_once LAYOUTS_PATH . '/scripts.php';
?>
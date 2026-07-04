<?php
require_once 'koneksi.php';

$errors = [];
$success = false;
$selectedId = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['batik_id']) ? (int)$_POST['batik_id'] : 0);

// Ambil semua batik yang berstatus "Tersedia" untuk pilihan dropdown
$stmtAll = $pdo->query("SELECT * FROM batik WHERE status = 'Tersedia' ORDER BY nama_batik ASC");
$batikTersedia = $stmtAll->fetchAll();

const MIN_HARI = 1;
const MAX_HARI = 7;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama       = trim($_POST['nama'] ?? '');
    $noHp       = trim($_POST['no_hp'] ?? '');
    $batikId    = (int)($_POST['batik_id'] ?? 0);
    $tglPinjam  = $_POST['tanggal_pinjam'] ?? '';
    $tglKembali = $_POST['tanggal_kembali'] ?? '';

    // Validasi dasar
    if ($nama === '') $errors[] = "Nama tidak boleh kosong.";
    if ($noHp === '' || !preg_match('/^[0-9+]{9,15}$/', $noHp)) $errors[] = "Nomor HP tidak valid.";
    if ($batikId <= 0) $errors[] = "Silakan pilih batik yang ingin disewa.";
    if ($tglPinjam === '' || $tglKembali === '') $errors[] = "Tanggal pinjam dan tanggal kembali wajib diisi.";

    $jumlahHari = 0;
    $totalHarga = 0;
    $batikDipilih = null;

    if (empty($errors)) {
        $dPinjam  = DateTime::createFromFormat('Y-m-d', $tglPinjam);
        $dKembali = DateTime::createFromFormat('Y-m-d', $tglKembali);

        if (!$dPinjam || !$dKembali) {
            $errors[] = "Format tanggal tidak valid.";
        } elseif ($dKembali <= $dPinjam) {
            $errors[] = "Tanggal kembali harus setelah tanggal pinjam.";
        } else {
            $jumlahHari = (int)$dPinjam->diff($dKembali)->days;

            // Validasi lama pinjam
            if ($jumlahHari < MIN_HARI) {
                $errors[] = "Peminjaman minimal " . MIN_HARI . " hari.";
            } elseif ($jumlahHari > MAX_HARI) {
                $errors[] = "Peminjaman maksimal " . MAX_HARI . " hari.";
            }
        }

        // Ambil data batik & cek status
        if (empty($errors)) {
            $stmtBatik = $pdo->prepare("SELECT * FROM batik WHERE id = :id");
            $stmtBatik->execute([':id' => $batikId]);
            $batikDipilih = $stmtBatik->fetch();

            if (!$batikDipilih) {
                $errors[] = "Batik yang dipilih tidak ditemukan.";
            } elseif ($batikDipilih['status'] !== 'Tersedia') {
                $errors[] = "Maaf, batik ini sedang dipinjam dan tidak bisa disewa.";
            } else {
                $totalHarga = $jumlahHari * $batikDipilih['harga_per_hari'];
            }
        }
    }

    // Simpan ke database jika tidak ada error
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $stmtInsert = $pdo->prepare(
                "INSERT INTO penyewaan (nama_penyewa, no_hp, batik_id, tanggal_pinjam, tanggal_kembali, jumlah_hari, total_harga, status_sewa)
                 VALUES (:nama, :no_hp, :batik_id, :tgl_pinjam, :tgl_kembali, :jumlah_hari, :total, 'Berlangsung')"
            );
            $stmtInsert->execute([
                ':nama'        => $nama,
                ':no_hp'       => $noHp,
                ':batik_id'    => $batikId,
                ':tgl_pinjam'  => $tglPinjam,
                ':tgl_kembali' => $tglKembali,
                ':jumlah_hari' => $jumlahHari,
                ':total'       => $totalHarga,
            ]);

            // Update status batik menjadi "Sedang Dipinjam"
            $stmtUpdate = $pdo->prepare("UPDATE batik SET status = 'Sedang Dipinjam' WHERE id = :id");
            $stmtUpdate->execute([':id' => $batikId]);

            $pdo->commit();
            $success = true;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Terjadi kesalahan saat menyimpan data: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Form Penyewaan Batik</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<?php include 'partials/navbar.php'; ?>

<section class="hero-banner py-4">
    <div class="container">
        <h1 style="font-size:2rem;"><i class="fa-solid fa-cart-shopping me-2"></i>Form Penyewaan Batik</h1>
        <p class="mb-0">Lengkapi data di bawah, total harga dihitung otomatis</p>
    </div>
</section>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <?php if ($success): ?>
                <div class="alert alert-batik-success p-4 text-center">
                    <i class="fa-solid fa-circle-check fa-2x mb-2"></i>
                    <h4>Penyewaan Berhasil!</h4>
                    <p class="mb-1">Terima kasih <strong><?= htmlspecialchars($nama) ?></strong>, penyewaan batik
                        <strong><?= htmlspecialchars($batikDipilih['nama_batik']) ?></strong> selama
                        <strong><?= $jumlahHari ?> hari</strong> berhasil dicatat.</p>
                    <p class="mb-3">Total pembayaran: <strong><?= formatRupiah($totalHarga) ?></strong></p>
                    <a href="katalog.php" class="btn btn-batik-primary">Kembali ke Katalog</a>
                </div>
            <?php else: ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-batik-danger p-3">
                        <ul class="mb-0">
                            <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="form-batik">
                    <form method="POST" id="formSewa">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="nama" class="form-control" required
                                    value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">No. HP / WhatsApp</label>
                                <input type="text" name="no_hp" class="form-control" placeholder="08xxxxxxxxxx" required
                                    value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Pilih Batik</label>
                                <select name="batik_id" id="batikSelect" class="form-select" required>
                                    <option value="">-- Pilih Batik --</option>
                                    <?php foreach ($batikTersedia as $b): ?>
                                        <option value="<?= $b['id'] ?>"
                                            data-harga="<?= $b['harga_per_hari'] ?>"
                                            <?= ($selectedId == $b['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($b['nama_batik']) ?> - Ukuran <?= $b['ukuran'] ?> (<?= formatRupiah($b['harga_per_hari']) ?>/hari)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (count($batikTersedia) === 0): ?>
                                    <div class="form-text text-danger">Saat ini tidak ada batik yang tersedia untuk disewa.</div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tanggal Pinjam</label>
                                <input type="date" name="tanggal_pinjam" id="tglPinjam" class="form-control" required
                                    value="<?= htmlspecialchars($_POST['tanggal_pinjam'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Kembali</label>
                                <input type="date" name="tanggal_kembali" id="tglKembali" class="form-control" required
                                    value="<?= htmlspecialchars($_POST['tanggal_kembali'] ?? '') ?>">
                                <div class="form-text">Peminjaman minimal 1 hari, maksimal 7 hari.</div>
                            </div>
                        </div>

                        <div class="total-box mt-4">
                            <div>Jumlah Hari: <span id="outJumlahHari">0</span> hari</div>
                            <div>Total Harga: <span id="outTotal">Rp0</span></div>
                        </div>

                        <button type="submit" class="btn btn-batik-primary w-100 mt-4">
                            <i class="fa-solid fa-check me-1"></i> Konfirmasi Penyewaan
                        </button>
                    </form>
                </div>

            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Hitung otomatis jumlah hari x harga = total (real-time di sisi client)
function formatRupiahJS(angka) {
    return "Rp" + angka.toLocaleString('id-ID');
}

function hitungTotal() {
    const select = document.getElementById('batikSelect');
    const opt = select.options[select.selectedIndex];
    const harga = opt && opt.dataset.harga ? parseInt(opt.dataset.harga) : 0;

    const tglPinjam = document.getElementById('tglPinjam').value;
    const tglKembali = document.getElementById('tglKembali').value;

    let jumlahHari = 0;
    if (tglPinjam && tglKembali) {
        const d1 = new Date(tglPinjam);
        const d2 = new Date(tglKembali);
        const diffTime = d2 - d1;
        jumlahHari = Math.round(diffTime / (1000 * 60 * 60 * 24));
        if (jumlahHari < 0) jumlahHari = 0;
    }

    document.getElementById('outJumlahHari').innerText = jumlahHari;
    document.getElementById('outTotal').innerText = formatRupiahJS(jumlahHari * harga);
}

document.getElementById('batikSelect').addEventListener('change', hitungTotal);
document.getElementById('tglPinjam').addEventListener('change', hitungTotal);
document.getElementById('tglKembali').addEventListener('change', hitungTotal);

// Set tanggal minimal hari ini
const today = new Date().toISOString().split('T')[0];
document.getElementById('tglPinjam').setAttribute('min', today);
document.getElementById('tglKembali').setAttribute('min', today);

hitungTotal();
</script>
</body>
</html>
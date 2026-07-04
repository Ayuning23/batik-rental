<?php
require_once 'auth.php';
require_once '../koneksi.php';

$dariTanggal   = trim($_GET['dari'] ?? date('Y-m-01'));
$sampaiTanggal = trim($_GET['sampai'] ?? date('Y-m-d'));

// Validasi sederhana, fallback ke default kalau format salah
if (!DateTime::createFromFormat('Y-m-d', $dariTanggal)) {
    $dariTanggal = date('Y-m-01');
}
if (!DateTime::createFromFormat('Y-m-d', $sampaiTanggal)) {
    $sampaiTanggal = date('Y-m-d');
}

$sql = "SELECT p.*, b.nama_batik FROM penyewaan p
        JOIN batik b ON p.batik_id = b.id
        WHERE DATE(p.tanggal_pinjam) BETWEEN :dari AND :sampai
        ORDER BY p.tanggal_pinjam ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([':dari' => $dariTanggal, ':sampai' => $sampaiTanggal]);
$data = $stmt->fetchAll();

$totalTransaksi  = count($data);
$totalPendapatan = array_sum(array_column($data, 'total_harga'));
$totalSelesai    = count(array_filter($data, fn($r) => $r['status_sewa'] === 'Selesai'));
$totalBerlangsung = $totalTransaksi - $totalSelesai;

// ---- Export CSV ----
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="laporan_' . $dariTanggal . '_sd_' . $sampaiTanggal . '.csv"');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['Nama Penyewa', 'Batik', 'Tanggal Pinjam', 'Tanggal Kembali', 'Total Harga', 'Status']);
    foreach ($data as $r) {
        fputcsv($out, [
            $r['nama_penyewa'],
            $r['nama_batik'],
            $r['tanggal_pinjam'],
            $r['tanggal_kembali'],
            $r['total_harga'],
            $r['status_sewa'],
        ]);
    }
    fclose($out);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laporan - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="..assets/css/style.css" rel="stylesheet">
</head>
<body>

<div class="row g-0">
    <?php include 'sidebar.php'; ?>

    <div class="col-lg-10 col-md-9">
        <div class="container-fluid py-4 px-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0 text-red-dark">Laporan Pendapatan</h3>
                <a href="laporan.php?dari=<?= urlencode($dariTanggal) ?>&sampai=<?= urlencode($sampaiTanggal) ?>&export=csv"
                   class="btn btn-batik-primary">
                    <i class="fa-solid fa-file-csv me-1"></i> Export CSV
                </a>
            </div>

            <!-- Filter -->
            <div class="card rounded-xl border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Dari Tanggal</label>
                            <input type="date" name="dari" class="form-control" value="<?= htmlspecialchars($dariTanggal) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sampai Tanggal</label>
                            <input type="date" name="sampai" class="form-control" value="<?= htmlspecialchars($sampaiTanggal) ?>">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-batik-primary w-100">
                                <i class="fa-solid fa-filter me-1"></i> Terapkan Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Ringkasan -->
            <div class="row g-4 mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card stat-pink">
                        <small>Total Transaksi</small>
                        <h3><?= $totalTransaksi ?></h3>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card stat-red">
                        <small>Berlangsung</small>
                        <h3><?= $totalBerlangsung ?></h3>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card stat-green">
                        <small>Selesai</small>
                        <h3><?= $totalSelesai ?></h3>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card stat-gold">
                        <small>Total Pendapatan</small>
                        <h3 style="font-size:1.4rem;"><?= formatRupiah($totalPendapatan) ?></h3>
                    </div>
                </div>
            </div>

            <!-- Tabel Detail -->
            <div class="card rounded-xl border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3 text-red-dark">
                        Detail Transaksi (<?= htmlspecialchars($dariTanggal) ?> s/d <?= htmlspecialchars($sampaiTanggal) ?>)
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-batik align-middle">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Batik</th>
                                    <th>Tanggal Pinjam</th>
                                    <th>Tanggal Kembali</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($data) === 0): ?>
                                    <tr><td colspan="6" class="text-center text-muted py-3">Tidak ada data pada rentang tanggal ini.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($data as $r): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['nama_penyewa']) ?></td>
                                    <td><?= htmlspecialchars($r['nama_batik']) ?></td>
                                    <td><?= htmlspecialchars($r['tanggal_pinjam']) ?></td>
                                    <td><?= htmlspecialchars($r['tanggal_kembali']) ?></td>
                                    <td><?= formatRupiah($r['total_harga']) ?></td>
                                    <td>
                                        <?php if ($r['status_sewa'] === 'Berlangsung'): ?>
                                            <span class="badge-dipinjam">Berlangsung</span>
                                        <?php else: ?>
                                            <span class="badge-tersedia">Selesai</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
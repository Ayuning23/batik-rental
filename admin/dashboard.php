<?php
require_once 'auth.php'; // Diubah karena berada di folder yang sama
require_once '../koneksi.php'; // Diubah karena koneksi.php ada di luar folder admin

// Total batik
$totalBatik = $pdo->query("SELECT COUNT(*) AS jumlah FROM batik")->fetch()['jumlah'];

// Sedang dipinjam
$sedangDipinjam = $pdo->query("SELECT COUNT(*) AS jumlah FROM batik WHERE status = 'Sedang Dipinjam'")->fetch()['jumlah'];

// Peminjaman selesai
$selesai = $pdo->query("SELECT COUNT(*) AS jumlah FROM penyewaan WHERE status_sewa = 'Selesai'")->fetch()['jumlah'];

// Total pendapatan (dari semua transaksi)
$pendapatan = $pdo->query("SELECT COALESCE(SUM(total_harga), 0) AS total FROM penyewaan")->fetch()['total'];

// Riwayat terbaru (5 transaksi terakhir)
$riwayatTerbaru = $pdo->query(
    "SELECT p.*, b.nama_batik FROM penyewaan p
     JOIN batik b ON p.batik_id = b.id
     ORDER BY p.created_at DESC LIMIT 5"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin - BatikAyu</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="..assets/css/style.css" rel="stylesheet"> </head>
<body>

<div class="row g-0">
    <?php include 'sidebar.php'; ?> <div class="col-lg-10 col-md-9">
        <div class="container-fluid py-4 px-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="mb-0 text-red-dark">Dashboard</h3>
                    <small class="text-muted">Selamat datang, <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?> 👋</small>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card stat-pink">
                        <div class="d-flex justify-content-between">
                            <div>
                                <small>Total Batik</small>
                                <h3><?= $totalBatik ?></h3>
                            </div>
                            <i class="fa-solid fa-shirt stat-icon"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card stat-red">
                        <div class="d-flex justify-content-between">
                            <div>
                                <small>Sedang Dipinjam</small>
                                <h3><?= $sedangDipinjam ?></h3>
                            </div>
                            <i class="fa-solid fa-person-walking-arrow-right stat-icon"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card stat-green">
                        <div class="d-flex justify-content-between">
                            <div>
                                <small>Selesai</small>
                                <h3><?= $selesai ?></h3>
                            </div>
                            <i class="fa-solid fa-circle-check stat-icon"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card stat-gold">
                        <div class="d-flex justify-content-between">
                            <div>
                                <small>Pendapatan</small>
                                <h3 style="font-size:1.4rem;"><?= isset($pdo) && function_exists('formatRupiah') ? formatRupiah($pendapatan) : "Rp " . number_format($pendapatan, 0, ',', '.') ?></h3>
                            </div>
                            <i class="fa-solid fa-sack-dollar stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card rounded-xl border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3 text-red-dark">Peminjaman Terbaru</h5>
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
                                <?php if (count($riwayatTerbaru) === 0): ?>
                                    <tr><td colspan="6" class="text-center text-muted py-3">Belum ada data peminjaman.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($riwayatTerbaru as $r): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['nama_penyewa']) ?></td>
                                    <td><?= htmlspecialchars($r['nama_batik']) ?></td>
                                    <td><?= htmlspecialchars($r['tanggal_pinjam']) ?></td>
                                    <td><?= htmlspecialchars($r['tanggal_kembali']) ?></td>
                                    <td><?= function_exists('formatRupiah') ? formatRupiah($r['total_harga']) : "Rp " . number_format($r['total_harga'], 0, ',', '.') ?></td>
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
                    <a href="riwayat.php" class="btn btn-batik-primary btn-sm mt-2">Lihat Semua Riwayat</a> </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
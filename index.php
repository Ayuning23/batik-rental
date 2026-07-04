<?php
require_once 'koneksi.php';

// Ambil beberapa batik unggulan (yang tersedia) untuk ditampilkan di beranda
$stmt = $pdo->query("SELECT * FROM batik WHERE status = 'Tersedia' ORDER BY created_at DESC LIMIT 6");
$batikList = $stmt->fetchAll();

// Harga termurah untuk teks "mulai dari"
$stmtMin = $pdo->query("SELECT MIN(harga_per_hari) as harga_min FROM batik");
$hargaMin = $stmtMin->fetch()['harga_min'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Beranda - Sewa Batik Wanita</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<?php include 'partials/navbar.php'; ?>

<!-- Hero Banner -->
<section class="hero-banner">
    <div class="container">
        <h1>Sewa Batik Wanita, Tampil Anggun Tanpa Ribet</h1>
        <p class="mb-4">Koleksi batik pilihan untuk wisuda, pesta, hingga acara formal.<br>
        Harga mulai dari <strong><?= formatRupiah($hargaMin) ?> / hari</strong></p>
        <a href="katalog.php" class="btn btn-batik-primary me-2"><i class="fa-solid fa-shirt me-1"></i> Lihat Katalog</a>
        <a href="#tentang" class="btn btn-batik-outline">Tentang Kami</a>
    </div>
</section>

<!-- Daftar Batik Unggulan -->
<section class="container py-5">
    <h2 class="section-title">Batik Pilihan</h2>
    <p class="section-subtitle">Beberapa koleksi favorit yang siap kamu sewa hari ini</p>

    <div class="row g-4">
        <?php if (count($batikList) === 0): ?>
            <div class="col-12 text-center text-muted">Belum ada koleksi batik tersedia saat ini.</div>
        <?php endif; ?>

        <?php foreach ($batikList as $batik): ?>
        <div class="col-sm-6 col-lg-4">
            <div class="card card-batik">
                <div class="card-img-top d-flex align-items-center justify-content-center">
                    <i class="fa-solid fa-vest-patches fa-4x" style="color:#c9184a; opacity:0.4;"></i>
                </div>
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($batik['nama_batik']) ?></h5>
                    <p class="mb-1 text-muted"><i class="fa-solid fa-ruler me-1"></i> Ukuran <?= htmlspecialchars($batik['ukuran']) ?>
                        &nbsp;|&nbsp; <i class="fa-solid fa-palette me-1"></i> <?= htmlspecialchars($batik['warna']) ?></p>
                    <p class="price-tag mb-3"><?= formatRupiah($batik['harga_per_hari']) ?> <small>/ hari</small></p>
                    <a href="sewa.php?id=<?= $batik['id'] ?>" class="btn btn-batik-primary w-100">
                        <i class="fa-solid fa-cart-shopping me-1"></i> Sewa
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="text-center mt-5">
        <a href="katalog.php" class="btn btn-batik-outline" style="border-color: var(--red-dark); color: var(--red-dark);">
            Lihat Semua Koleksi <i class="fa-solid fa-arrow-right ms-1"></i>
        </a>
    </div>
</section>

<!-- Tentang -->
<section id="tentang" class="py-5" style="background: var(--soft-pink);">
    <div class="container text-center">
        <h2 class="section-title">Kenapa Sewa di Kami?</h2>
        <div class="row g-4 mt-3">
            <div class="col-md-4">
                <i class="fa-solid fa-gem fa-2x mb-3" style="color: var(--red-dark);"></i>
                <h5>Koleksi Berkualitas</h5>
                <p class="text-muted">Batik pilihan dengan bahan nyaman dan motif elegan.</p>
            </div>
            <div class="col-md-4">
                <i class="fa-solid fa-tags fa-2x mb-3" style="color: var(--red-dark);"></i>
                <h5>Harga Terjangkau</h5>
                <p class="text-muted">Sewa harian dengan harga bersahabat untuk semua acara.</p>
            </div>
            <div class="col-md-4">
                <i class="fa-solid fa-truck-fast fa-2x mb-3" style="color: var(--red-dark);"></i>
                <h5>Proses Mudah</h5>
                <p class="text-muted">Cukup isi form penyewaan, total harga dihitung otomatis.</p>
            </div>
        </div>
    </div>
</section>

<?php include 'partials/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
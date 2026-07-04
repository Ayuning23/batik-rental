<?php
require_once 'koneksi.php';

// Filter pencarian & ukuran (opsional)
$cari   = trim($_GET['cari'] ?? '');
$ukuran = trim($_GET['ukuran'] ?? '');

$sql = "SELECT * FROM batik WHERE 1=1";
$params = [];

if ($cari !== '') {
    $sql .= " AND nama_batik LIKE :cari";
    $params[':cari'] = "%$cari%";
}
if ($ukuran !== '') {
    $sql .= " AND ukuran = :ukuran";
    $params[':ukuran'] = $ukuran;
}

$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$batikList = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Katalog Batik</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<?php include 'partials/navbar.php'; ?>

<section class="hero-banner py-4">
    <div class="container">
        <h1 style="font-size:2rem;">Katalog Batik</h1>
        <p class="mb-0">Temukan batik yang sesuai untuk acaramu</p>
    </div>
</section>

<div class="container py-5">

    <!-- Form Filter -->
    <form method="GET" class="row g-3 mb-4 align-items-end justify-content-center">
        <div class="col-md-5">
            <label class="form-label fw-semibold">Cari Nama Batik</label>
            <input type="text" name="cari" class="form-control" placeholder="Contoh: Batik Encim" value="<?= htmlspecialchars($cari) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Ukuran</label>
            <select name="ukuran" class="form-select">
                <option value="">Semua Ukuran</option>
                <?php foreach (['S','M','L','XL'] as $u): ?>
                    <option value="<?= $u ?>" <?= $ukuran === $u ? 'selected' : '' ?>><?= $u ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-batik-primary w-100"><i class="fa-solid fa-magnifying-glass me-1"></i>Cari</button>
        </div>
    </form>

    <div class="row g-4">
        <?php if (count($batikList) === 0): ?>
            <div class="col-12 text-center text-muted py-5">
                <i class="fa-solid fa-shirt fa-3x mb-3" style="opacity:0.3;"></i>
                <p>Tidak ada batik yang cocok dengan pencarianmu.</p>
            </div>
        <?php endif; ?>

        <?php foreach ($batikList as $batik): ?>
        <div class="col-sm-6 col-lg-4">
            <div class="card card-batik">
                <div class="card-img-top d-flex align-items-center justify-content-center">
                    <i class="fa-solid fa-vest-patches fa-4x" style="color:#c9184a; opacity:0.4;"></i>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <h5 class="card-title mb-0"><?= htmlspecialchars($batik['nama_batik']) ?></h5>
                        <?php if ($batik['status'] === 'Tersedia'): ?>
                            <span class="badge-tersedia">Tersedia</span>
                        <?php else: ?>
                            <span class="badge-dipinjam">Dipinjam</span>
                        <?php endif; ?>
                    </div>
                    <p class="mb-1 text-muted"><i class="fa-solid fa-ruler me-1"></i> Ukuran <?= htmlspecialchars($batik['ukuran']) ?>
                        &nbsp;|&nbsp; <i class="fa-solid fa-palette me-1"></i> <?= htmlspecialchars($batik['warna']) ?></p>
                    <?php if (!empty($batik['deskripsi'])): ?>
                        <p class="small text-muted mb-2"><?= htmlspecialchars($batik['deskripsi']) ?></p>
                    <?php endif; ?>
                    <p class="price-tag mb-3"><?= formatRupiah($batik['harga_per_hari']) ?> <small>/ hari</small></p>

                    <?php if ($batik['status'] === 'Tersedia'): ?>
                        <a href="sewa.php?id=<?= $batik['id'] ?>" class="btn btn-batik-primary w-100">
                            <i class="fa-solid fa-cart-shopping me-1"></i> Sewa Sekarang
                        </a>
                    <?php else: ?>
                        <button class="btn btn-secondary w-100" disabled>Sedang Dipinjam</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'partials/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
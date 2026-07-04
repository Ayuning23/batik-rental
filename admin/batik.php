<?php
require_once 'auth.php';
require_once '../koneksi.php';

$batikList = $pdo->query("SELECT * FROM batik ORDER BY created_at DESC")->fetchAll();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Batik - Admin</title>
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
                <h3 class="mb-0 text-red-dark">Data Batik</h3>
                <a href="tambah_batik.php" class="btn btn-batik-primary">
                    <i class="fa-solid fa-plus me-1"></i> Tambah Batik
                </a>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-batik-<?= $flash['type'] ?> p-3"><?= htmlspecialchars($flash['message']) ?></div>
            <?php endif; ?>

            <div class="card rounded-xl border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-batik align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Batik</th>
                                    <th>Ukuran</th>
                                    <th>Warna</th>
                                    <th>Harga / Hari</th>
                                    <th>Status</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($batikList) === 0): ?>
                                    <tr><td colspan="7" class="text-center text-muted py-3">Belum ada data batik.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($batikList as $b): ?>
                                <tr>
                                    <td>#<?= $b['id'] ?></td>
                                    <td><?= htmlspecialchars($b['nama_batik']) ?></td>
                                    <td><?= htmlspecialchars($b['ukuran']) ?></td>
                                    <td><?= htmlspecialchars($b['warna']) ?></td>
                                    <td><?= formatRupiah($b['harga_per_hari']) ?></td>
                                    <td>
                                        <?php if ($b['status'] === 'Tersedia'): ?>
                                            <span class="badge-tersedia">Tersedia</span>
                                        <?php else: ?>
                                            <span class="badge-dipinjam">Dipinjam</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="edit_batik.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <a href="hapus_batik.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Yakin ingin menghapus batik ini?');">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
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
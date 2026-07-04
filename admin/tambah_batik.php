<?php
require_once 'auth.php'; // Berada di folder yang sama
require_once '../koneksi.php'; // Naik satu folder ke root

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_batik = $_POST['nama_batik'];
    $asal_daerah = $_POST['asal_daerah'];
    $harga_sewa = $_POST['harga_sewa'];
    $status = $_POST['status'];

    // Proses Upload Gambar
    $gambar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
        $nama_file = $_FILES['gambar']['name'];
        $tmp_name = $_FILES['gambar']['tmp_name'];
        
        // Buat nama unik untuk file gambar
        $ekstensi = pathinfo($nama_file, PATHINFO_EXTENSION);
        $nama_baru = time() . '_' . uniqid() . '.' . $ekstensi;
        
        // Target folder (naik satu folder lalu masuk ke assets/img)
        $target_dir = "../assets/img/";
        
        // Buat folder jika belum ada
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        if (move_uploaded_file($tmp_name, $target_dir . $nama_baru)) {
            $gambar = $nama_baru;
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO batik (nama_batik, asal_daerah, harga_sewa, status, gambar) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nama_batik, $asal_daerah, $harga_sewa, $status, $gambar]);
        
        // Redirect kembali ke halaman batik.php di dalam folder admin
        header("Location: batik.php");
        exit;
    } catch (PDOException $e) {
        $error = "Gagal menambah data: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Batik - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="..assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="row g-0">
    <?php include 'sidebar.php'; ?>
    <div class="col-md-10 p-4">
        <h3 class="mb-4 text-red-dark">Tambah Koleksi Batik</h3>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="card p-4 shadow-sm border-0">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Nama Batik</label>
                    <input type="text" name="nama_batik" class="form-content form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Asal Daerah</label>
                    <input type="text" name="asal_daerah" class="form-content form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Harga Sewa / Hari</label>
                    <input type="number" name="harga_sewa" class="form-content form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select form-content" required>
                        <option value="Tersedia">Tersedia</option>
                        <option value="Sedang Dipinjam">Sedang Dipinjam</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label">Foto Batik</label>
                    <input type="file" name="gambar" class="form-control form-content" accept="image/*" required>
                </div>
                <button type="submit" class="btn btn-batik-primary px-4">Simpan Data</button>
                <a href="batik.php" class="btn btn-secondary px-4 ms-2">Batal</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
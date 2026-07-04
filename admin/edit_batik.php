<?php
require_once 'auth.php'; 
require_once '../koneksi.php'; 

// Cek parameter ID
if (!isset($_GET['id'])) {
    header("Location: batik.php");
    exit;
}

$id = $_GET['id'];

// Ambil data batik yang akan diedit
$stmt = $pdo->prepare("SELECT * FROM batik WHERE id = ?");
$stmt->execute([$id]);
$batik = $stmt->fetch();

if (!$batik) {
    header("Location: batik.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_batik = $_POST['nama_batik'];
    $asal_daerah = $_POST['asal_daerah'];
    $harga_sewa = $_POST['harga_sewa'];
    $status = $_POST['status'];
    $gambar_lama = $_POST['gambar_lama'];
    
    // Default gambar memakai yang lama
    $gambar = $gambar_lama;

    // Jika admin mengupload gambar baru
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
        $nama_file = $_FILES['gambar']['name'];
        $tmp_name = $_FILES['gambar']['tmp_name'];
        
        $ekstensi = pathinfo($nama_file, PATHINFO_EXTENSION);
        $nama_baru = time() . '_' . uniqid() . '.' . $ekstensi;
        $target_dir = "../assets/img/";

        if (move_uploaded_file($tmp_name, $target_dir . $nama_baru)) {
            $gambar = $nama_baru;
            // Hapus gambar lama di server agar penyimpanan bersih
            if (!empty($gambar_lama) && file_exists($target_dir . $gambar_lama)) {
                unlink($target_dir . $gambar_lama);
            }
        }
    }

    try {
        $stmt = $pdo->prepare("UPDATE batik SET nama_batik = ?, asal_daerah = ?, harga_sewa = ?, status = ?, gambar = ? WHERE id = ?");
        $stmt->execute([$nama_batik, $asal_daerah, $harga_sewa, $status, $gambar, $id]);
        
        header("Location: batik.php");
        exit;
    } catch (PDOException $e) {
        $error = "Gagal memperbarui data: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Batik - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="..assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="row g-0">
    <?php include 'sidebar.php'; ?>
    <div class="col-md-10 p-4">
        <h3 class="mb-4 text-red-dark">Edit Data Batik</h3>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="card p-4 shadow-sm border-0">
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="gambar_lama" value="<?= htmlspecialchars($batik['gambar']) ?>">
                
                <div class="mb-3">
                    <label class="form-label">Nama Batik</label>
                    <input type="text" name="nama_batik" class="form-control form-content" value="<?= htmlspecialchars($batik['nama_batik']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Asal Daerah</label>
                    <input type="text" name="asal_daerah" class="form-control form-content" value="<?= htmlspecialchars($batik['asal_daerah']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Harga Sewa / Hari</label>
                    <input type="number" name="harga_sewa" class="form-control form-content" value="<?= htmlspecialchars($batik['harga_sewa']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select form-content" required>
                        <option value="Tersedia" <?= $batik['status'] === 'Tersedia' ? 'selected' : '' ?>>Tersedia</option>
                        <option value="Sedang Dipinjam" <?= $batik['status'] === 'Sedang Dipinjam' ? 'selected' : '' ?>>Sedang Dipinjam</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label">Foto Saat Ini</label><br>
                    <?php if (!empty($batik['gambar'])): ?>
                        <img src="../assets/img/<?= htmlspecialchars($batik['gambar']) ?>" alt="Batik" class="img-thumbnail mb-2" style="max-height: 150px;"><br>
                    <?php endif; ?>
                    <input type="file" name="gambar" class="form-control form-content" accept="image/*">
                    <small class="text-muted">*Kosongkan jika tidak ingin mengubah gambar.</small>
                </div>
                <button type="submit" class="btn btn-batik-primary px-4">Update Data</button>
                <a href="batik.php" class="btn btn-secondary px-4 ms-2">Batal</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
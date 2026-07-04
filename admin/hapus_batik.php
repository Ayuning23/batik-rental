<?php
require_once 'auth.php'; 
require_once '../koneksi.php'; 

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Ambil nama file gambar terlebih dahulu agar bisa dihapus dari folder assets
        $stmtGambar = $pdo->prepare("SELECT gambar FROM batik WHERE id = ?");
        $stmtGambar->execute([$id]);
        $batik = $stmtGambar->fetch();

        if ($batik) {
            $nama_gambar = $batik['gambar'];
            $path_gambar = "../assets/img/" . $nama_gambar;

            // Hapus file fisik gambar jika ada
            if (!empty($nama_gambar) && file_exists($path_gambar)) {
                unlink($path_gambar);
            }

            // Hapus data dari database
            $stmtDelete = $pdo->prepare("DELETE FROM batik WHERE id = ?");
            $stmtDelete->execute([$id]);
        }
    } catch (PDOException $e) {
        // Jika gagal karena constraint / foreign key relasi peminjaman, tangani di sini
        echo "<script>alert('Gagal menghapus! Kain batik kemungkinan sedang direlasikan ke data transaksi peminjaman.'); window.location.href='batik.php';</script>";
        exit;
    }
}

// Kembalikan ke halaman daftar batik
header("Location: batik.php");
exit;
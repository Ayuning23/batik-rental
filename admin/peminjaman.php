<?php
require_once 'auth.php';
require_once '../koneksi.php';

$errors = [];

// ---- Tandai peminjaman selesai (pengembalian) ----
if (isset($_GET['action']) && $_GET['action'] === 'selesai' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $stmt = $pdo->prepare("SELECT * FROM penyewaan WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $sewa = $stmt->fetch();

    if ($sewa && $sewa['status_sewa'] === 'Berlangsung') {
        $pdo->beginTransaction();
        try {
            $pdo->prepare("UPDATE penyewaan SET status_sewa = 'Selesai' WHERE id = :id")
                ->execute([':id' => $id]);
            $pdo->prepare("UPDATE batik SET status = 'Tersedia' WHERE id = :batik_id")
                ->execute([':batik_id' => $sewa['batik_id']]);
            $pdo->commit();
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Peminjaman ditandai selesai dan batik tersedia kembali.'];
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Gagal memperbarui status peminjaman.'];
        }
    } else {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Data peminjaman tidak valid atau sudah selesai.'];
    }

    header('Location: peminjaman.php');
    exit;
}

// ---- Tambah peminjaman baru ----
$old = [
    'batik_id'        => '',
    'nama_penyewa'    => '',
    'tanggal_pinjam'  => date('Y-m-d'),
    'tanggal_kembali' => date('Y-m-d', strtotime('+1 day')),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['batik_id']        = (int)($_POST['batik_id'] ?? 0);
    $old['nama_penyewa']    = trim($_POST['nama_penyewa'] ?? '');
    $old['tanggal_pinjam']  = trim($_POST['tanggal_pinjam'] ?? '');
    $old['tanggal_kembali'] = trim($_POST['tanggal_kembali'] ?? '');

    if ($old['nama_penyewa'] === '') {
        $errors[] = 'Nama penyewa wajib diisi.';
    }
    if ($old['batik_id'] <= 0) {
        $errors[] = 'Batik wajib dipilih.';
    }

    $tglPinjam  = DateTime::createFromFormat('Y-m-d', $old['tanggal_pinjam']);
    $tglKembali = DateTime::createFromFormat('Y-m-d', $old['tanggal_kembali']);

    if (!$tglPinjam || !$tglKembali) {
        $errors[] = 'Format tanggal tidak valid.';
    } elseif ($tglKembali <= $tglPinjam) {
        $errors[] = 'Tanggal kembali harus setelah tanggal pinjam.';
    }

    $batik = null;
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM batik WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $old['batik_id']]);
        $batik = $stmt->fetch();

        if (!$batik) {
            $errors[] = 'Batik tidak ditemukan.';
        } elseif ($batik['status'] !== 'Tersedia') {
            $errors[] = 'Batik yang dipilih sedang tidak tersedia.';
        }
    }

    if (empty($errors) && $batik) {
        $jumlahHari  = max(1, $tglKembali->diff($tglPinjam)->days);
        $totalHarga  = $jumlahHari * $batik['harga_per_hari'];

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO penyewaan (batik_id, nama_penyewa, tanggal_pinjam, tanggal_kembali, total_harga, status_sewa, created_at)
                 VALUES (:batik_id, :nama_penyewa, :tanggal_pinjam, :tanggal_kembali, :total_harga, 'Berlangsung', NOW())"
            );
            $stmt->execute([
                ':batik_id'        => $old['batik_id'],
                ':nama_penyewa'    => $old['nama_penyewa'],
                ':tanggal_pinjam'  => $old['tanggal_pinjam'],
                ':tanggal_kembali' => $old['tanggal_kembali'],
                ':total_harga'     => $totalHarga,
            ]);

            $pdo->prepare("UPDATE batik SET status = 'Sedang Dipinjam' WHERE id = :id")
                ->execute([':id' => $old['batik_id']]);

            $pdo->commit();
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Peminjaman baru berhasil dicatat.'];
            header('Location: peminjaman.php');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Gagal menyimpan data peminjaman. Silakan coba lagi.';
        }
    }
}

// Batik yang tersedia untuk dipilih di form
$batikTersedia = $pdo->query("SELECT * FROM batik WHERE status = 'Tersedia' ORDER BY nama_batik ASC")->fetchAll();

// Semua data peminjaman
$semuaPeminjaman = $pdo->query(
    "SELECT p.*, b.nama_batik FROM penyewaan p
     JOIN batik b ON p.batik_id = b.id
     ORDER BY p.created_at DESC"
)->fetchAll();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Peminjaman - Admin</title>
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
                <h3 class="mb-0 text-red-dark">Peminjaman</h3>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-batik-<?= $flash['type'] ?> p-3"><?= htmlspecialchars($flash['message']) ?></div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-batik-danger p-3">
                    <ul class="mb-0">
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Form Peminjaman Baru -->
            <div class="card rounded-xl border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="text-red-dark mb-3">Catat Peminjaman Baru</h5>
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Nama Penyewa</label>
                                <input type="text" name="nama_penyewa" class="form-control"
                                       value="<?= htmlspecialchars($old['nama_penyewa']) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Pilih Batik</label>
                                <select name="batik_id" class="form-select" required>
                                    <option value="">-- Batik Tersedia --</option>
                                    <?php foreach ($batikTersedia as $b): ?>
                                        <option value="<?= $b['id'] ?>" <?= (int)$old['batik_id'] === (int)$b['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($b['nama_batik']) ?> (<?= formatRupiah($b['harga_per_hari']) ?>/hari)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (count($batikTersedia) === 0): ?>
                                    <small class="text-muted">Tidak ada batik yang tersedia saat ini.</small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Tanggal Pinjam</label>
                                <input type="date" name="tanggal_pinjam" class="form-control"
                                       value="<?= htmlspecialchars($old['tanggal_pinjam']) ?>" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Tanggal Kembali</label>
                                <input type="date" name="tanggal_kembali" class="form-control"
                                       value="<?= htmlspecialchars($old['tanggal_kembali']) ?>" required>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-batik-primary">
                                <i class="fa-solid fa-plus me-1"></i> Simpan Peminjaman
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Daftar Peminjaman -->
            <div class="card rounded-xl border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3 text-red-dark">Daftar Peminjaman</h5>
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
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($semuaPeminjaman) === 0): ?>
                                    <tr><td colspan="7" class="text-center text-muted py-3">Belum ada data peminjaman.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($semuaPeminjaman as $r): ?>
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
                                    <td class="text-end">
                                        <?php if ($r['status_sewa'] === 'Berlangsung'): ?>
                                            <a href="peminjaman.php?action=selesai&id=<?= $r['id'] ?>"
                                               class="btn btn-sm btn-outline-success"
                                               onclick="return confirm('Tandai peminjaman ini sebagai selesai?');">
                                                <i class="fa-solid fa-check me-1"></i> Selesai
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
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
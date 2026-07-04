<?php
session_start();
require_once 'koneksi.php';

// 1. PERBAIKAN: Jika sudah login, langsung arahkan ke folder admin/dashboard.php
if (isset($_SESSION['admin_id'])) {
    header('Location: admin/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            
            // 2. PERBAIKAN: Setelah sukses login, arahkan masuk ke folder admin/dashboard.php
            header('Location: admin/dashboard.php');
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Admin - BatikAyu</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body style="background: linear-gradient(135deg, var(--soft-pink), var(--pink));">

<div class="login-wrapper">
    <div class="login-card">
        <div class="text-center">
            <div class="icon-circle">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <h4 class="mb-1">Login Admin</h4>
            <p class="text-muted mb-4">Masuk untuk mengelola data batik & penyewaan</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-batik-danger py-2 px-3"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required autofocus
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-batik-primary w-100">
                <i class="fa-solid fa-right-to-bracket me-1"></i> Masuk
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="index.php" class="text-decoration-none" style="color: var(--red-dark);">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Beranda
            </a>
        </div>

        <div class="text-center mt-3">
            <small class="text-muted">Default: <strong>admin</strong> / <strong>admin123</strong></small>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
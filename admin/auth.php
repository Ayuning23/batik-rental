<?php
session_start();

// Jika admin belum login, arahkan ke halaman login
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}
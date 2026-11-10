<?php
require_once __DIR__ . '/includes/auth.php';

// Hapus data session
$_SESSION = [];

// Hapus cookie session
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

// Hapus cookie Remember Me
setcookie('remember_username', '', time() - 3600, '/');

session_destroy();

// Mulai session baru hanya untuk menampilkan pesan
session_start();
$_SESSION['flash'] = ['success', 'Anda berhasil logout.'];
header('Location: index.php');
exit;

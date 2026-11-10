<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid()) {
    set_flash('danger', 'Permintaan tidak valid.');
    header('Location: index.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

if ($username === '' || $password === '') {
    set_flash('danger', 'Username dan password wajib diisi.');
    header('Location: index.php');
    exit;
}

$stmt = $koneksi->prepare('SELECT id, username, password, nama_lengkap FROM users WHERE username = ? LIMIT 1');
$stmt->bind_param('s', $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($user && password_verify($password, $user['password'])) {
    session_regenerate_id(true); // cegah session fixation

    $_SESSION['user_id']  = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['nama']     = $user['nama_lengkap'];

    // Fitur Remember Me: simpan username di cookie 30 hari
    if ($remember) {
        setcookie('remember_username', $user['username'], [
            'expires'  => time() + 60 * 60 * 24 * 30,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    } else {
        setcookie('remember_username', '', time() - 3600, '/');
    }

    header('Location: dashboard.php');
    exit;
}

set_flash('danger', 'Username atau password salah.');
header('Location: index.php');
exit;

<?php
/**
 * Session & proteksi halaman.
 * Cookie session dibuat HttpOnly agar tidak bisa dibaca JavaScript.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function sudah_login(): bool
{
    return isset($_SESSION['user_id']);
}

/** Wajib login, jika tidak redirect ke halaman login. */
function wajib_login(): void
{
    if (!sudah_login()) {
        $_SESSION['flash'] = ['danger', 'Silakan login terlebih dahulu.'];
        header('Location: index.php');
        exit;
    }
}

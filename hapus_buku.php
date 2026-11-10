<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
wajib_login();

// Hapus hanya lewat POST + token CSRF agar tidak bisa dipicu lewat link biasa
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid()) {
    set_flash('danger', 'Permintaan tidak valid.');
    header('Location: dashboard.php');
    exit;
}

$id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
if (!$id) {
    set_flash('danger', 'ID buku tidak valid.');
    header('Location: dashboard.php');
    exit;
}

$stmt = $koneksi->prepare('SELECT sampul FROM buku WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$buku = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$buku) {
    set_flash('danger', 'Data buku tidak ditemukan.');
    header('Location: dashboard.php');
    exit;
}

$stmt = $koneksi->prepare('DELETE FROM buku WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();

hapus_sampul($buku['sampul']); // unlink file gambar dari folder uploads

set_flash('success', 'Buku berhasil dihapus.');
header('Location: dashboard.php');
exit;

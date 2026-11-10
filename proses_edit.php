<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
wajib_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid()) {
    set_flash('danger', 'Permintaan tidak valid.');
    header('Location: dashboard.php');
    exit;
}

$id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
$stmt = $koneksi->prepare('SELECT sampul, kategori FROM buku WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$lama = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$id || !$lama) {
    set_flash('danger', 'Data buku tidak ditemukan.');
    header('Location: dashboard.php');
    exit;
}

$errors      = validasi_buku($_POST, [$lama['kategori']]);
$sampulBaru  = null;
$adaFile     = isset($_FILES['sampul']) && $_FILES['sampul']['error'] !== UPLOAD_ERR_NO_FILE;

if (!$errors && $adaFile) {
    [$ok, $hasil] = upload_sampul($_FILES['sampul']);
    if ($ok) {
        $sampulBaru = $hasil;
    } else {
        $errors[] = $hasil;
    }
}

if ($errors) {
    $_SESSION['old'] = $_POST;
    set_flash('danger', implode(' ', $errors));
    header('Location: edit_buku.php?id=' . $id);
    exit;
}

$judul    = trim($_POST['judul']);
$kategori = trim($_POST['kategori']);
$penulis  = trim($_POST['penulis']);
$tahun    = (int) $_POST['tahun_terbit'];
$stok     = (int) $_POST['stok'];
$sampul   = $sampulBaru ?? $lama['sampul']; // pakai file lama jika tidak diganti

try {
    $stmt = $koneksi->prepare('UPDATE buku SET judul=?, kategori=?, penulis=?, tahun_terbit=?, stok=?, sampul=? WHERE id=?');
    $stmt->bind_param('sssiisi', $judul, $kategori, $penulis, $tahun, $stok, $sampul, $id);
    $stmt->execute();

    // Hapus file lama dari server bila sampul diganti
    if ($sampulBaru !== null) {
        hapus_sampul($lama['sampul']);
    }
    set_flash('success', 'Data buku berhasil diperbarui.');
} catch (mysqli_sql_exception $ex) {
    hapus_sampul($sampulBaru);
    set_flash('danger', 'Gagal memperbarui data buku.');
}

header('Location: dashboard.php');
exit;

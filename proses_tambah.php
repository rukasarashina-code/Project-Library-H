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

$errors = validasi_buku($_POST);
$sampul = null;

// Upload sampul (opsional pada tambah, tapi divalidasi jika ada)
$adaFile = isset($_FILES['sampul']) && $_FILES['sampul']['error'] !== UPLOAD_ERR_NO_FILE;
if (!$errors && $adaFile) {
    [$ok, $hasil] = upload_sampul($_FILES['sampul']);
    if ($ok) {
        $sampul = $hasil;
    } else {
        $errors[] = $hasil;
    }
}

if ($errors) {
    $_SESSION['old'] = $_POST;
    set_flash('danger', implode(' ', $errors));
    header('Location: tambah_buku.php');
    exit;
}

$judul    = trim($_POST['judul']);
$kategori = trim($_POST['kategori']);
$penulis  = trim($_POST['penulis']);
$tahun    = (int) $_POST['tahun_terbit'];
$stok     = (int) $_POST['stok'];

try {
    $stmt = $koneksi->prepare('INSERT INTO buku (judul, kategori, penulis, tahun_terbit, stok, sampul) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('sssiis', $judul, $kategori, $penulis, $tahun, $stok, $sampul);
    $stmt->execute();
    set_flash('success', 'Buku "' . $judul . '" berhasil ditambahkan.');
} catch (mysqli_sql_exception $ex) {
    hapus_sampul($sampul); // rollback file jika query gagal
    set_flash('danger', 'Gagal menyimpan data buku.');
}

header('Location: dashboard.php');
exit;

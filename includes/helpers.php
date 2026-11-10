<?php
/**
 * Fungsi bantu: escape output, label status, flash message, CSRF, dan upload file.
 */

const UPLOAD_DIR     = __DIR__ . '/../uploads/';
const MAX_FILE       = 2 * 1024 * 1024; // 2 MB
const EXT_DIIZINKAN  = ['jpg', 'jpeg', 'png'];
const MIME_DIIZINKAN = ['image/jpeg', 'image/png'];

/** Pilihan kategori buku (dipakai pada dropdown form tambah & edit). */
const KATEGORI_BUKU = [
    'Novel', 'Cerpen & Puisi', 'Teknologi', 'Sains', 'Matematika', 'Sejarah',
    'Biografi', 'Pendidikan', 'Bisnis & Ekonomi', 'Agama', 'Pengembangan Diri',
    'Kesehatan', 'Seni & Desain', 'Komik', 'Referensi',
];

function e($teks): string
{
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
}

/** Logika bisnis: tentukan label status berdasarkan stok. */
function label_status(int $stok): array
{
    if ($stok > 0) {
        return ['Tersedia', 'success'];
    }
    return ['Dipinjam', 'danger'];
}

/* ---------- Flash message ---------- */
function set_flash(string $tipe, string $pesan): void
{
    $_SESSION['flash'] = [$tipe, $pesan];
}

function tampilkan_flash(): void
{
    if (!empty($_SESSION['flash'])) {
        [$tipe, $pesan] = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="alert alert-' . e($tipe) . ' alert-dismissible fade show" role="alert">'
           . e($pesan)
           . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
}

/* ---------- CSRF ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_input(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    return isset($_POST['csrf'], $_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}

/* ---------- Upload sampul ---------- */

/**
 * Validasi & simpan file sampul.
 * @return array [bool sukses, string namaFile|pesanError]
 */
function upload_sampul(array $file): array
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $pesan = match ($file['error']) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Ukuran file melebihi batas.',
            UPLOAD_ERR_PARTIAL => 'File hanya terunggah sebagian.',
            default => 'Terjadi kesalahan saat mengunggah file.',
        };
        return [false, $pesan];
    }

    if ($file['size'] > MAX_FILE) {
        return [false, 'Ukuran file maksimal 2 MB.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, EXT_DIIZINKAN, true)) {
        return [false, 'Ekstensi file harus .jpg, .jpeg, atau .png.'];
    }

    // Cek MIME asli (bukan hanya ekstensi)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    if (!in_array($mime, MIME_DIIZINKAN, true) || getimagesize($file['tmp_name']) === false) {
        return [false, 'File bukan gambar JPG/PNG yang valid.'];
    }

    // Siapkan folder tujuan (buat jika belum ada, pastikan bisa ditulis)
    [$dirOk, $dir] = siapkan_folder_upload();
    if (!$dirOk) {
        return [false, $dir]; // $dir berisi pesan error
    }

    $namaBaru = 'cover_' . bin2hex(random_bytes(8)) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
    $tujuan   = $dir . DIRECTORY_SEPARATOR . $namaBaru;

    // Coba move_uploaded_file; jika gagal, coba copy() sebagai cadangan
    $berhasil = @move_uploaded_file($file['tmp_name'], $tujuan);
    if (!$berhasil && is_uploaded_file($file['tmp_name'])) {
        $berhasil = @copy($file['tmp_name'], $tujuan);
    }
    if (!$berhasil) {
        $detail = error_get_last()['message'] ?? 'tidak diketahui';
        error_log('Upload sampul gagal: ' . $detail . ' | tujuan: ' . $tujuan);
        return [false, 'Gagal menyimpan file ke server. Periksa izin tulis folder "uploads" (detail: ' . $detail . ').'];
    }
    return [true, $namaBaru];
}

/**
 * Pastikan folder uploads ada dan dapat ditulis oleh web server.
 * @return array [bool ok, string path|pesanError]
 */
function siapkan_folder_upload(): array
{
    $dir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'uploads';

    if (!is_dir($dir) && !@mkdir($dir, 0777, true) && !is_dir($dir)) {
        return [false, 'Folder "uploads" tidak ada dan gagal dibuat. Buat folder "uploads" secara manual di root project.'];
    }

    $real = realpath($dir) ?: $dir;

    if (!is_writable($real)) {
        @chmod($real, 0777); // coba beri izin tulis otomatis (Linux/macOS)
        clearstatcache(true, $real);
    }
    if (!is_writable($real)) {
        return [false, 'Folder "uploads" tidak bisa ditulis oleh web server. Beri izin tulis (Linux/macOS: chmod -R 777 uploads).'];
    }
    return [true, $real];
}

/** Hapus file sampul fisik dari folder uploads. */
function hapus_sampul(?string $nama): void
{
    if (!$nama) {
        return;
    }
    $path = UPLOAD_DIR . basename($nama); // basename mencegah path traversal
    if (is_file($path)) {
        unlink($path);
    }
}

/** Validasi data teks buku. @return array daftar error */
function validasi_buku(array $d, array $kategoriTambahan = []): array
{
    $err = [];
    if (trim($d['judul'] ?? '') === '')    $err[] = 'Judul wajib diisi.';
    $kategoriValid = array_merge(KATEGORI_BUKU, $kategoriTambahan);
    if (!in_array($d['kategori'] ?? '', $kategoriValid, true)) $err[] = 'Silakan pilih kategori dari daftar.';
    if (trim($d['penulis'] ?? '') === '')  $err[] = 'Penulis wajib diisi.';
    $tahun = filter_var($d['tahun_terbit'] ?? '', FILTER_VALIDATE_INT);
    if ($tahun === false || $tahun < 1000 || $tahun > (int) date('Y') + 1) {
        $err[] = 'Tahun terbit tidak valid.';
    }
    $stok = filter_var($d['stok'] ?? '', FILTER_VALIDATE_INT);
    if ($stok === false || $stok < 0) {
        $err[] = 'Stok harus berupa angka 0 atau lebih.';
    }
    return $err;
}

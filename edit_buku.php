<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
wajib_login();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$stmt = $koneksi->prepare('SELECT * FROM buku WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$buku = $stmt->get_result()->fetch_assoc();

if (!$id || !$buku) {
    set_flash('danger', 'Data buku tidak ditemukan.');
    header('Location: dashboard.php');
    exit;
}

$old = $_SESSION['old'] ?? [];
unset($_SESSION['old']);
$v = fn($k) => e($old[$k] ?? $buku[$k]);

// Dropdown kategori; kategori lama (jika di luar daftar) tetap tersedia
$opsiKategori = KATEGORI_BUKU;
if (!in_array($buku['kategori'], $opsiKategori, true)) {
    $opsiKategori[] = $buku['kategori'];
}
$kategoriTerpilih = $old['kategori'] ?? $buku['kategori'];

$judul_halaman = 'Edit Buku';
require_once __DIR__ . '/includes/header.php'; // output HTML dimulai setelah semua redirect
?>
<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card p-4 fade-up">
      <h5 class="fw-bold mb-3"><i class="bi bi-pencil-square me-2"></i>Edit Buku</h5>
      <?php tampilkan_flash(); ?>
      <form action="proses_edit.php" method="POST" enctype="multipart/form-data">
        <?= csrf_input() ?>
        <input type="hidden" name="id" value="<?= (int) $buku['id'] ?>">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Judul Buku</label>
            <input type="text" name="judul" class="form-control" value="<?= $v('judul') ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Kategori</label>
            <select name="kategori" class="form-select" required>
              <option value="" disabled <?= $kategoriTerpilih === '' ? 'selected' : '' ?>>-- Pilih kategori --</option>
              <?php foreach ($opsiKategori as $kat): ?>
                <option value="<?= e($kat) ?>" <?= $kat === $kategoriTerpilih ? 'selected' : '' ?>><?= e($kat) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Penulis</label>
            <input type="text" name="penulis" class="form-control" value="<?= $v('penulis') ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Tahun Terbit</label>
            <input type="number" name="tahun_terbit" class="form-control" min="1000" max="<?= date('Y') + 1 ?>" value="<?= $v('tahun_terbit') ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Stok <small class="text-muted">(0 = Dipinjam)</small></label>
            <input type="number" name="stok" class="form-control" min="0" value="<?= $v('stok') ?>" required>
          </div>
          <div class="col-12">
            <label class="form-label">Sampul Buku <small class="text-muted">(kosongkan jika tidak diganti; JPG/PNG maks. 2 MB)</small></label>
            <?php if ($buku['sampul'] && is_file(UPLOAD_DIR . basename($buku['sampul']))): ?>
              <div class="mb-2"><img src="uploads/<?= e($buku['sampul']) ?>" class="cover-preview" alt="Sampul saat ini"></div>
            <?php endif; ?>
            <input type="file" name="sampul" id="sampul" class="form-control" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
            <img id="preview" class="cover-preview mt-3 d-none" alt="Preview sampul baru">
          </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4">
          <a href="dashboard.php" class="btn btn-light">Batal</a>
          <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Perbarui</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
$judul_halaman = 'Tambah Buku';
require_once __DIR__ . '/includes/header.php';
$old = $_SESSION['old'] ?? [];
unset($_SESSION['old']);
$opsiKategori = KATEGORI_BUKU;
$kategoriTerpilih = $old['kategori'] ?? '';
?>
<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card p-4 fade-up">
      <h5 class="fw-bold mb-3"><i class="bi bi-plus-circle me-2"></i>Tambah Buku Baru</h5>
      <?php tampilkan_flash(); ?>
      <form action="proses_tambah.php" method="POST" enctype="multipart/form-data">
        <?= csrf_input() ?>
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Judul Buku</label>
            <input type="text" name="judul" class="form-control" value="<?= e($old['judul'] ?? '') ?>" required>
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
            <input type="text" name="penulis" class="form-control" value="<?= e($old['penulis'] ?? '') ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Tahun Terbit</label>
            <input type="number" name="tahun_terbit" class="form-control" min="1000" max="<?= date('Y') + 1 ?>" value="<?= e($old['tahun_terbit'] ?? date('Y')) ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Stok <small class="text-muted">(0 = Dipinjam)</small></label>
            <input type="number" name="stok" class="form-control" min="0" value="<?= e($old['stok'] ?? 1) ?>" required>
          </div>
          <div class="col-12">
            <label class="form-label">Sampul Buku <small class="text-muted">(JPG/PNG, maks. 2 MB)</small></label>
            <input type="file" name="sampul" id="sampul" class="form-control" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
            <img id="preview" class="cover-preview mt-3 d-none" alt="Preview sampul">
          </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4">
          <a href="dashboard.php" class="btn btn-light">Batal</a>
          <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
require_once __DIR__ . '/config/koneksi.php';
$judul_halaman = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

// Statistik
$stat = $koneksi->query(
    'SELECT COUNT(*) AS total, COALESCE(SUM(stok > 0),0) AS tersedia, COALESCE(SUM(stok = 0),0) AS dipinjam FROM buku'
)->fetch_assoc();

// Pencarian (GET)
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = $koneksi->prepare('SELECT * FROM buku WHERE judul LIKE ? OR penulis LIKE ? OR kategori LIKE ? ORDER BY id DESC');
    $stmt->bind_param('sss', $like, $like, $like);
    $stmt->execute();
    $hasil = $stmt->get_result();
} else {
    $hasil = $koneksi->query('SELECT * FROM buku ORDER BY id DESC');
}
?>

<?php tampilkan_flash(); ?>

<div class="row g-3 mb-4 fade-up">
  <div class="col-md-4">
    <div class="card stat-card p-3"><div class="d-flex align-items-center gap-3">
      <div class="icon bg-primary-subtle text-primary"><i class="bi bi-collection"></i></div>
      <div><div class="text-muted fw-semibold">Total Buku</div><div class="fs-2 fw-bold" data-count="<?= (int) $stat['total'] ?>"><?= (int) $stat['total'] ?></div></div>
    </div></div>
  </div>
  <div class="col-md-4">
    <div class="card stat-card p-3"><div class="d-flex align-items-center gap-3">
      <div class="icon bg-success-subtle text-success"><i class="bi bi-check-circle"></i></div>
      <div><div class="text-muted fw-semibold">Tersedia</div><div class="fs-2 fw-bold" data-count="<?= (int) $stat['tersedia'] ?>"><?= (int) $stat['tersedia'] ?></div></div>
    </div></div>
  </div>
  <div class="col-md-4">
    <div class="card stat-card p-3"><div class="d-flex align-items-center gap-3">
      <div class="icon bg-danger-subtle text-danger"><i class="bi bi-arrow-left-right"></i></div>
      <div><div class="text-muted fw-semibold">Dipinjam</div><div class="fs-2 fw-bold" data-count="<?= (int) $stat['dipinjam'] ?>"><?= (int) $stat['dipinjam'] ?></div></div>
    </div></div>
  </div>
</div>

<div class="card p-3 p-md-4 fade-up d2">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-journal-bookmark me-2"></i>Daftar Buku</h5>
    <div class="d-flex gap-2">
      <form method="GET" class="d-flex gap-2">
        <input type="search" name="q" class="form-control" placeholder="Cari judul/penulis/kategori" value="<?= e($q) ?>">
        <button class="btn btn-outline-primary"><i class="bi bi-search"></i></button>
      </form>
      <a href="tambah_buku.php" class="btn btn-primary text-nowrap"><i class="bi bi-plus-lg me-1"></i>Tambah Buku</a>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>No</th><th>Sampul</th><th>Judul</th><th>Kategori</th><th>Penulis</th>
          <th>Tahun</th><th>Stok</th><th>Status</th><th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($hasil->num_rows === 0): ?>
        <tr><td colspan="9" class="text-center text-muted py-5">Belum ada data buku.</td></tr>
      <?php endif; ?>
      <?php $no = 1; while ($b = $hasil->fetch_assoc()):
          [$label, $warna] = label_status((int) $b['stok']); ?>
        <tr>
          <td><?= $no++ ?></td>
          <td>
            <?php if ($b['sampul'] && is_file(UPLOAD_DIR . basename($b['sampul']))): ?>
              <img src="uploads/<?= e($b['sampul']) ?>" class="cover-thumb" alt="Sampul <?= e($b['judul']) ?>">
            <?php else: ?>
              <div class="cover-placeholder"><i class="bi bi-image"></i></div>
            <?php endif; ?>
          </td>
          <td class="fw-semibold"><?= e($b['judul']) ?></td>
          <td><span class="badge badge-kat"><?= e($b['kategori']) ?></span></td>
          <td><?= e($b['penulis']) ?></td>
          <td><?= e($b['tahun_terbit']) ?></td>
          <td><?= (int) $b['stok'] ?></td>
          <td><span class="badge text-bg-<?= $warna ?>"><?= $label ?></span></td>
          <td class="text-end text-nowrap">
            <a href="edit_buku.php?id=<?= (int) $b['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square"></i></a>
            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalHapus"
                    data-id="<?= (int) $b['id'] ?>" data-judul="<?= e($b['judul']) ?>"><i class="bi bi-trash"></i></button>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal konfirmasi hapus -->
<div class="modal fade" id="modalHapus" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:430px">
    <form class="modal-content delete-modal text-center" action="hapus_buku.php" method="POST">
      <?= csrf_input() ?>
      <input type="hidden" name="id" id="hapusId">
      <div class="top-bar"></div>
      <div class="modal-body p-4 p-md-5">
        <div class="warn-icon mx-auto mb-4"><i class="bi bi-exclamation-lg"></i></div>
        <h4 class="fw-bold mb-2">Hapus Buku?</h4>
        <p class="text-muted mb-1">Anda akan menghapus buku</p>
        <p class="fw-bold fs-5 mb-2" id="hapusJudul"></p>
        <p class="text-muted small mb-4">Data dan file sampul akan dihapus permanen dan tidak bisa dikembalikan.</p>
        <div class="d-flex gap-2 justify-content-center">
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"><i class="bi bi-x-lg me-1"></i>Batal</button>
          <button type="submit" class="btn btn-danger px-4"><i class="bi bi-trash3-fill me-1"></i>Ya, Hapus</button>
        </div>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

// Sudah login? langsung ke dashboard
if (sudah_login()) {
    header('Location: dashboard.php');
    exit;
}

// Isi otomatis username dari cookie "Remember Me"
$usernameCookie = $_COOKIE['remember_username'] ?? '';
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login | Shelfwise Library</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="login-wrap">
  <div class="card login-card p-4 p-md-5 fade-up">
    <div class="text-center mb-4">
      <div class="display-4 text-primary"><i class="bi bi-book-half"></i></div>
      <h3 class="mb-0">Shelfwise Library</h3>
      <p class="text-muted small">Masuk untuk mengelola koleksi buku</p>
    </div>

    <?php tampilkan_flash(); ?>

    <form action="proses_login.php" method="POST" autocomplete="on">
      <?= csrf_input() ?>
      <div class="mb-3">
        <label class="form-label" for="username">Username</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-person"></i></span>
          <input type="text" class="form-control" id="username" name="username"
                 value="<?= e($usernameCookie) ?>" required autofocus>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label" for="password">Password</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock"></i></span>
          <input type="password" class="form-control" id="password" name="password" required>
          <button type="button" class="btn btn-outline-secondary" id="togglePassword" aria-label="Tampilkan password">
            <i class="bi bi-eye" id="eyeIcon"></i>
          </button>
        </div>
      </div>
      <div class="form-check mb-4">
        <input class="form-check-input" type="checkbox" name="remember" id="remember"
               <?= $usernameCookie !== '' ? 'checked' : '' ?>>
        <label class="form-check-label" for="remember">Remember Me</label>
      </div>
      <button type="submit" class="btn btn-primary w-100 py-2"><i class="bi bi-box-arrow-in-right me-1"></i>Login</button>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Tampilkan / sembunyikan password
  const pw = document.getElementById('password');
  const eye = document.getElementById('eyeIcon');
  document.getElementById('togglePassword').addEventListener('click', () => {
    const tampil = pw.type === 'password';
    pw.type = tampil ? 'text' : 'password';
    eye.className = tampil ? 'bi bi-eye-slash' : 'bi bi-eye';
  });
</script>
</body>
</html>

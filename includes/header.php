<?php
/** Layout atas untuk halaman setelah login. Butuh variabel $judul_halaman (opsional). */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';
wajib_login();
$judul_halaman = $judul_halaman ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($judul_halaman) ?> | Shelfwise Library</title>
  <script>
    // Terapkan tema tersimpan sebelum render agar tidak berkedip
    try { document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('sw-theme') || 'light'); } catch (e) {}
  </script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
<nav class="navbar navbar-dark sw-navbar sticky-top">
  <div class="container sw-nav-inner">
    <a class="navbar-brand" href="dashboard.php"><i class="bi bi-book-half me-2"></i><span>Shelfwise Library</span></a>
    <!-- Satu baris menyamping: Administrator → Switch Dark Mode → Logout -->
    <div class="nav-actions">
      <span class="user"><i class="bi bi-person-circle"></i><span class="user-text">Administrator</span></span>
      <span class="divider"></span>
      <label class="theme-switch" title="Mode gelap / terang">
        <input type="checkbox" id="themeSwitch" aria-label="Aktifkan mode gelap">
        <span class="track">
          <i class="bi bi-sun-fill sun"></i><i class="bi bi-moon-stars-fill moon"></i>
          <span class="knob"></span>
        </span>
      </label>
      <span class="divider"></span>
      <a href="logout.php" class="btn btn-sm btn-outline-light btn-logout"><i class="bi bi-box-arrow-right"></i><span class="logout-text">Logout</span></a>
    </div>
  </div>
</nav>
<main class="container py-4 flex-grow-1">

/* ---------- Tema gelap / terang ---------- */
(function () {
  const root = document.documentElement;

  function terapkanTema(tema) {
    root.setAttribute('data-bs-theme', tema);
    root.style.colorScheme = tema;
    const sw = document.getElementById('themeSwitch');
    if (sw) sw.checked = (tema === 'dark');
  }

  // Tema awal: dari localStorage (jika ada), default terang
  let awal = 'light';
  try { awal = localStorage.getItem('sw-theme') || 'light'; } catch (e) {}
  terapkanTema(awal);

  // Delegasi event agar selalu berfungsi
  document.addEventListener('change', function (ev) {
    if (ev.target && ev.target.id === 'themeSwitch') {
      const tema = ev.target.checked ? 'dark' : 'light';
      terapkanTema(tema);
      try { localStorage.setItem('sw-theme', tema); } catch (e) {}
    }
  });
})();

document.addEventListener('DOMContentLoaded', () => {
  /* ---------- Preview sampul + validasi ukuran ---------- */
  const input = document.getElementById('sampul');
  const preview = document.getElementById('preview');
  if (input && preview) {
    input.addEventListener('change', () => {
      const f = input.files[0];
      if (!f) return;
      if (f.size > 2 * 1024 * 1024) {
        alert('Ukuran file maksimal 2 MB.');
        input.value = '';
        return;
      }
      preview.src = URL.createObjectURL(f);
      preview.classList.remove('d-none');
    });
  }

  /* ---------- Modal konfirmasi hapus ---------- */
  const modal = document.getElementById('modalHapus');
  if (modal) {
    modal.addEventListener('show.bs.modal', (ev) => {
      const btn = ev.relatedTarget;
      document.getElementById('hapusId').value = btn.dataset.id;
      document.getElementById('hapusJudul').textContent = btn.dataset.judul;
    });
  }

  /* ---------- Hitung naik angka statistik ---------- */
  document.querySelectorAll('[data-count]').forEach((el) => {
    const target = parseInt(el.dataset.count, 10) || 0;
    const t0 = performance.now(), dur = 800;
    const step = (t) => {
      const p = Math.min((t - t0) / dur, 1);
      el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3)));
      if (p < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  });

  /* ---------- Delay bertahap baris tabel ---------- */
  document.querySelectorAll('.table tbody tr').forEach((tr, i) => {
    tr.style.animationDelay = Math.min(i * 40, 400) + 'ms';
  });
});

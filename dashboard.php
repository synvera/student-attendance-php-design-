<?php
session_start();
include 'config/koneksi.php';
require_once 'config/app_settings.php';
app_settings_initialize($koneksi);
app_require_role(['sekretaris', 'wali_kelas']);
$app_settings = app_settings_get($koneksi);
$is_secretary = ($_SESSION['role'] ?? 'sekretaris') === 'sekretaris';

date_default_timezone_set('Asia/Jakarta');
$tanggal_hari_ini = date('Y-m-d');
$tanggal_tampil = date('l, d F Y');
$nama_hari = [
    'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu',
];
$nama_bulan = [
    'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
    'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
    'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
    'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember',
];
$tanggal_tampil = strtr($tanggal_tampil, $nama_hari + $nama_bulan);

$query = "SELECT tabel_siswa.nis, tabel_siswa.nama_siswa, tabel_absensi.status, tabel_absensi.keterangan
          FROM tabel_siswa
          LEFT JOIN tabel_absensi
          ON tabel_siswa.id_siswa = tabel_absensi.id_siswa
          AND tabel_absensi.tanggal = '$tanggal_hari_ini'
          ORDER BY tabel_siswa.nama_siswa ASC";
$hasil = mysqli_query($koneksi, $query);
$daftar_siswa = [];
$ringkasan = array_fill_keys($app_settings['attendance_statuses'], 0);
$ringkasan['Belum diisi'] = 0;

while ($siswa = mysqli_fetch_assoc($hasil)) {
    $status = $siswa['status'] ?: 'Belum diisi';
                    if (!isset($ringkasan[$status])) $ringkasan[$status] = 0;
                    $ringkasan[$status]++;
    $daftar_siswa[] = $siswa;
}
$total_siswa = count($daftar_siswa);
$persentase_hadir = $total_siswa > 0 ? round((($ringkasan['Hadir'] ?? 0) / $total_siswa) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#171918">
    <title>Dashboard | AbsenKu</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar" id="sidebar">
            <a class="brand" href="dashboard.php" aria-label="AbsenKu, dashboard">
                <span class="brand-mark">A</span>
                <span>Absen<span class="brand-light">Ku</span></span>
            </a>

            <div class="sidebar-label">MENU UTAMA</div>
            <nav class="side-nav" aria-label="Navigasi utama">
                <a class="nav-link is-active" href="dashboard.php" aria-current="page">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                    <span>Dashboard</span>
                </a>
                <?php if ($is_secretary): ?>
                <a class="nav-link" href="data_siswa.php">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M3 20v-1.5A4.5 4.5 0 0 1 7.5 14h3a4.5 4.5 0 0 1 4.5 4.5V20M17 11a3 3 0 1 0 0-6M17 14h.5a3.5 3.5 0 0 1 3.5 3.5V20"/></svg>
                    <span>Data Siswa</span>
                </a>
                <a class="nav-link" href="input_absen.php">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="5" width="16" height="16" rx="2"/><path d="M8 3v4m8-4v4M4 10h16m-11 5 2 2 4-4"/></svg>
                    <span>Input Absensi</span>
                </a>
                <?php endif; ?>
                <a class="nav-link" href="laporan_siswa.php">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h9l4 4v14H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path d="M14 3v5h5M8 12h8M8 16h8"/></svg>
                    <span>Laporan</span>
                </a>
                <?php if ($is_secretary): ?>
                <a class="nav-link" href="settings.php">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="m19.4 15 .1.1a1.7 1.7 0 0 1-2.4 2.4l-.1-.1a1.7 1.7 0 0 0-2.9 1.2v.2a1.7 1.7 0 0 1-3.4 0v-.2a1.7 1.7 0 0 0-2.9-1.2l-.1.1a1.7 1.7 0 0 1-2.4-2.4l.1-.1a1.7 1.7 0 0 0-1.2-2.9H4a1.7 1.7 0 0 1 0-3.4h.2a1.7 1.7 0 0 0 1.2-2.9l-.1-.1a1.7 1.7 0 0 1 2.4-2.4l.1.1a1.7 1.7 0 0 0 2.9-1.2V2a1.7 1.7 0 0 1 3.4 0v.2a1.7 1.7 0 0 0 2.9 1.2l.1-.1a1.7 1.7 0 0 1 2.4 2.4l-.1.1a1.7 1.7 0 0 0 1.2 2.9h.2a1.7 1.7 0 0 1 0 3.4h-.2a1.7 1.7 0 0 0-1.2 2.9Z"/></svg>
                    <span>Settings</span>
                </a>
                <?php endif; ?>
            </nav>

            <div class="sidebar-bottom">
                    <div class="school-card">
                    <span class="school-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 9 9-5 9 5-9 5-9-5Z"/><path d="M7 11.3V16c3 2.7 7 2.7 10 0v-4.7M21 9v6"/></svg></span>
                        <span><strong><?php echo htmlspecialchars($app_settings['class_name'], ENT_QUOTES, 'UTF-8'); ?></strong><small><?php echo htmlspecialchars($app_settings['academic_year'] . ' · Semester ' . $app_settings['semester'], ENT_QUOTES, 'UTF-8'); ?></small></span>
                </div>
                <a class="nav-link logout-link" href="proses/logout.php">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17l5-5-5-5m5 5H3"/><path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/></svg>
                    <span>Keluar</span>
                </a>
            </div>
        </aside>

        <div class="main-column">
            <header class="topbar">
                <button class="menu-toggle" id="menuToggle" type="button" aria-label="Buka menu" aria-expanded="false" aria-controls="sidebar">
                    <span></span><span></span><span></span>
                </button>
                <div class="breadcrumb"><span>AbsenKu</span><span class="breadcrumb-divider">/</span><strong>Dashboard</strong></div>
                <div class="topbar-right">
                    <span class="today-indicator"><span class="live-dot"></span> Absensi hari ini</span>
                    <span class="user-avatar" aria-hidden="true"><?php echo htmlspecialchars(strtoupper(substr($_SESSION['email_sekretaris'], 0, 1)), ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            </header>

            <main class="content">
                <section class="page-heading">
                    <div>
                        <p class="eyebrow">RINGKASAN KEHADIRAN</p>
                        <h1>Selamat datang kembali.</h1>
                        <p class="heading-note">Pantau kehadiran siswa dan aktivitas kelas hari ini.</p>
                    </div>
                    <div class="date-chip">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg>
                        <span><?php echo htmlspecialchars($tanggal_tampil, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                </section>

                <section class="stats-grid" aria-label="Ringkasan kehadiran">
                    <article class="stat-card stat-total">
                        <div class="stat-top"><span class="stat-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M3 20v-1.5A4.5 4.5 0 0 1 7.5 14h3a4.5 4.5 0 0 1 4.5 4.5V20M17 11a3 3 0 1 0 0-6M17 14h.5a3.5 3.5 0 0 1 3.5 3.5V20"/></svg></span><span class="stat-kicker">TOTAL SISWA</span></div>
                        <div class="stat-value"><?php echo $total_siswa; ?></div>
                        <div class="stat-caption">Terdaftar di kelas</div>
                        <div class="stat-corner" aria-hidden="true">01</div>
                    </article>
                    <article class="stat-card stat-present">
                        <div class="stat-top"><span class="stat-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/><circle cx="12" cy="12" r="9"/></svg></span><span class="stat-kicker">HADIR</span></div>
                        <div class="stat-value"><?php echo $ringkasan['Hadir'] ?? 0; ?></div>
                        <div class="stat-caption"><?php echo $persentase_hadir; ?>% dari total siswa</div>
                        <div class="stat-corner" aria-hidden="true">02</div>
                    </article>
                    <article class="stat-card stat-excused">
                        <div class="stat-top"><span class="stat-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 8v4l2.5 2.5"/><circle cx="12" cy="12" r="9"/></svg></span><span class="stat-kicker">IZIN &amp; SAKIT</span></div>
                        <div class="stat-value"><?php echo ($ringkasan['Izin'] ?? 0) + ($ringkasan['Sakit'] ?? 0); ?></div>
                        <div class="stat-caption">Izin <?php echo $ringkasan['Izin'] ?? 0; ?> <span class="caption-separator">/</span> Sakit <?php echo $ringkasan['Sakit'] ?? 0; ?></div>
                        <div class="stat-corner" aria-hidden="true">03</div>
                    </article>
                    <article class="stat-card stat-absent">
                        <div class="stat-top"><span class="stat-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 9 6 6m0-6-6 6"/><circle cx="12" cy="12" r="9"/></svg></span><span class="stat-kicker">ALPA</span></div>
                        <div class="stat-value"><?php echo $ringkasan['Alpa'] ?? 0; ?></div>
                        <div class="stat-caption">Belum hadir tanpa keterangan</div>
                        <div class="stat-corner" aria-hidden="true">04</div>
                    </article>
                    <article class="stat-card stat-excused">
                        <div class="stat-top"><span class="stat-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v9l6 3"/><circle cx="12" cy="12" r="9"/></svg></span><span class="stat-kicker">TERLAMBAT</span></div>
                        <div class="stat-value"><?php echo $ringkasan['Terlambat'] ?? 0; ?></div>
                        <div class="stat-caption">Masuk setelah batas waktu</div>
                        <div class="stat-corner" aria-hidden="true">05</div>
                    </article>
                    <article class="stat-card stat-total">
                        <div class="stat-top"><span class="stat-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5m0 4h.01"/></svg></span><span class="stat-kicker">BELUM DIISI</span></div>
                        <div class="stat-value"><?php echo $ringkasan['Belum diisi']; ?></div>
                        <div class="stat-caption">Belum ada catatan absensi</div>
                        <div class="stat-corner" aria-hidden="true">06</div>
                    </article>
                </section>

                <section class="attendance-section" aria-labelledby="attendance-title">
                    <div class="section-heading">
                        <div><p class="eyebrow">DATA KELAS</p><h2 id="attendance-title">Kehadiran siswa</h2></div>
                            <?php if ($is_secretary): ?><a class="text-link" href="input_absen.php">Kelola absensi <span aria-hidden="true">↗</span></a><?php endif; ?>
                    </div>

                    <div class="table-toolbar">
                        <label class="search-box">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m16 16 4 4"/></svg>
                            <input id="studentSearch" type="search" placeholder="Cari nama atau NIS..." autocomplete="off" aria-label="Cari nama atau NIS">
                            <kbd>/</kbd>
                        </label>
                        <label class="filter-box"><span class="filter-label">Status</span>
                            <select id="statusFilter" aria-label="Filter berdasarkan status">
                                <option value="all">Semua status</option><option value="Hadir">Hadir</option><option value="Sakit">Sakit</option><option value="Izin">Izin</option><option value="Alpa">Alpa</option><option value="Belum diisi">Belum diisi</option>
                            </select>
                        </label>
                    </div>

                    <div class="table-scroll">
                        <table class="attendance-table">
                            <thead><tr><th class="number-col">NO</th><th>NIS</th><th>NAMA SISWA</th><th>STATUS KEHADIRAN</th><th>KETERANGAN</th></tr></thead>
                            <tbody id="attendanceRows">
                                <?php foreach ($daftar_siswa as $index => $siswa):
                                    $status = $siswa['status'] ?: 'Belum diisi';
                                    $status_class = strtolower(str_replace(' ', '-', $status));
                                ?>
                                <tr data-status="<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>">
                                    <td class="row-number"><?php echo str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT); ?></td>
                                    <td class="student-nis"><?php echo htmlspecialchars($siswa['nis'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><span class="student-name"><?php echo htmlspecialchars($siswa['nama_siswa'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                    <td><span class="status-badge status-<?php echo htmlspecialchars($status_class, ENT_QUOTES, 'UTF-8'); ?>"><span></span><?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                    <td class="student-note"><?php echo htmlspecialchars($siswa['keterangan'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="empty-state" id="emptyState" <?php echo $total_siswa > 0 ? 'hidden' : ''; ?>>
                            <span class="empty-mark">—</span>
                            <strong><?php echo $total_siswa > 0 ? 'Tidak ada siswa yang cocok' : 'Belum ada data siswa'; ?></strong>
                            <p><?php echo $total_siswa > 0 ? 'Coba ubah kata kunci atau filter status.' : 'Tambahkan data siswa untuk mulai mengelola kehadiran.'; ?></p>
                            <?php if ($total_siswa === 0): ?><a href="data_siswa.php">Tambah siswa <span aria-hidden="true">↗</span></a><?php endif; ?>
                        </div>
                    </div>
                    <div class="table-footer"><span id="resultCount">Menampilkan <?php echo $total_siswa; ?> siswa</span><span class="footer-note"><span class="live-dot"></span> Data diperbarui hari ini</span></div>
                </section>
                <footer class="page-footer"><span>ABSENKU <span aria-hidden="true">·</span> PANEL KEHADIRAN</span><span><?php echo date('Y'); ?></span></footer>
            </main>
        </div>
    </div>
    <div class="sidebar-scrim" id="sidebarScrim"></div>
    <script src="assets/js/dashboard.js"></script>
</body>
</html>
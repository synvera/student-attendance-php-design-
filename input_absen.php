<?php
session_start();
include 'config/koneksi.php';
require_once 'config/app_settings.php';
app_settings_initialize($koneksi);
app_require_role(['sekretaris']);
date_default_timezone_set('Asia/Jakarta');

$settings = app_settings_get($koneksi);
$pesan = '';
$tanggal_hari_ini = date('Y-m-d');
$cutoff_terlewati = date('H:i:s') > $settings['attendance_cutoff'];
$csrf_token = app_csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    if (!app_verify_csrf()) {
        $pesan = 'Sesi formulir tidak valid. Muat ulang halaman lalu coba kembali.';
    } else {
        foreach ($_POST['status'] as $id_siswa => $status) {
            $id_siswa = (int) $id_siswa;
            if (!in_array($status, $settings['attendance_statuses'], true)) continue;
            if ($status === 'Hadir' && $cutoff_terlewati) {
                $status = in_array('Terlambat', $settings['attendance_statuses'], true) ? 'Terlambat' : 'Alpa';
            }
            $status_sql = mysqli_real_escape_string($koneksi, $status);
            $cek = mysqli_query($koneksi, "SELECT id_absen, keterangan FROM tabel_absensi WHERE id_siswa = '$id_siswa' AND tanggal = '$tanggal_hari_ini' LIMIT 1");
            $catatan_lama = mysqli_fetch_assoc($cek);
            $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan'][$id_siswa] ?? '');

            if (!$catatan_lama) {
                mysqli_query($koneksi, "INSERT INTO tabel_absensi (tanggal, id_siswa, status, keterangan) VALUES ('$tanggal_hari_ini', '$id_siswa', '$status_sql', '$keterangan')");
            } elseif (strpos((string) $catatan_lama['keterangan'], 'AUTO: melewati batas absensi') === 0) {
                mysqli_query($koneksi, "UPDATE tabel_absensi SET status = '$status_sql', keterangan = '$keterangan' WHERE id_absen = '" . (int) $catatan_lama['id_absen'] . "'");
            }
        }
        $pesan = 'Data absensi berhasil disimpan.';
    }
}

if ($cutoff_terlewati) {
    mysqli_query($koneksi, "INSERT INTO tabel_absensi (tanggal, id_siswa, status, keterangan)
        SELECT '$tanggal_hari_ini', siswa.id_siswa, 'Alpa', 'AUTO: melewati batas absensi'
        FROM tabel_siswa AS siswa
        LEFT JOIN tabel_absensi AS absensi ON absensi.id_siswa = siswa.id_siswa AND absensi.tanggal = '$tanggal_hari_ini'
        WHERE absensi.id_absen IS NULL");
}

$query = "SELECT tabel_siswa.*, tabel_absensi.status AS status_hari_ini, tabel_absensi.keterangan AS keterangan_hari_ini
          FROM tabel_siswa
          LEFT JOIN tabel_absensi
          ON tabel_siswa.id_siswa = tabel_absensi.id_siswa AND tabel_absensi.tanggal = '$tanggal_hari_ini'
          ORDER BY tabel_siswa.nama_siswa ASC";
$ambil = mysqli_query($koneksi, $query);
$daftar_siswa = [];
$jumlah_sudah_absen = 0;
while ($siswa = mysqli_fetch_assoc($ambil)) {
    $daftar_siswa[] = $siswa;
    if ($siswa['status_hari_ini'] !== null) $jumlah_sudah_absen++;
}
$jumlah_belum_absen = count($daftar_siswa) - $jumlah_sudah_absen;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#171918">
    <title>Input Absensi | AbsenKu</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/halaman.css">
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar" id="sidebar">
            <a class="brand" href="dashboard.php" aria-label="AbsenKu, dashboard"><span class="brand-mark">A</span><span>Absen<span class="brand-light">Ku</span></span></a>
            <div class="sidebar-label">MENU UTAMA</div>
            <nav class="side-nav" aria-label="Navigasi utama">
                <a class="nav-link" href="dashboard.php"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg><span>Dashboard</span></a>
                <a class="nav-link" href="data_siswa.php"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M3 20v-1.5A4.5 4.5 0 0 1 7.5 14h3a4.5 4.5 0 0 1 4.5 4.5V20M17 11a3 3 0 1 0 0-6M17 14h.5a3.5 3.5 0 0 1 3.5 3.5V20"/></svg><span>Data Siswa</span></a>
                <a class="nav-link is-active" href="input_absen.php" aria-current="page"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="5" width="16" height="16" rx="2"/><path d="M8 3v4m8-4v4M4 10h16m-11 5 2 2 4-4"/></svg><span>Input Absensi</span></a>
                <a class="nav-link" href="laporan_siswa.php"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h9l4 4v14H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path d="M14 3v5h5M8 12h8M8 16h8"/></svg><span>Laporan</span></a>
                <a class="nav-link" href="settings.php"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="m19.4 15 .1.1a1.7 1.7 0 0 1-2.4 2.4l-.1-.1a1.7 1.7 0 0 0-2.9 1.2v.2a1.7 1.7 0 0 1-3.4 0v-.2a1.7 1.7 0 0 0-2.9-1.2l-.1.1a1.7 1.7 0 0 1-2.4-2.4l.1-.1a1.7 1.7 0 0 0-1.2-2.9H4a1.7 1.7 0 0 1 0-3.4h.2a1.7 1.7 0 0 0 1.2-2.9l-.1-.1a1.7 1.7 0 0 1 2.4-2.4l.1.1a1.7 1.7 0 0 0 2.9-1.2V2a1.7 1.7 0 0 1 3.4 0v.2a1.7 1.7 0 0 0 2.9 1.2l.1-.1a1.7 1.7 0 0 1 2.4 2.4l-.1.1a1.7 1.7 0 0 0 1.2 2.9h.2a1.7 1.7 0 0 1 0 3.4h-.2a1.7 1.7 0 0 0-1.2 2.9Z"/></svg><span>Settings</span></a>
            </nav>
            <div class="sidebar-bottom"><div class="school-card"><span class="school-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 9 9-5 9 5-9 5-9-5Z"/><path d="M7 11.3V16c3 2.7 7 2.7 10 0v-4.7M21 9v6"/></svg></span><span><strong>Ruang Kelas</strong><small>Panel sekretaris</small></span></div><a class="nav-link logout-link" href="proses/logout.php"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17l5-5-5-5m5 5H3"/><path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/></svg><span>Keluar</span></a></div>
        </aside>
        <div class="main-column">
            <header class="topbar"><button class="menu-toggle" id="menuToggle" type="button" aria-label="Buka menu" aria-expanded="false" aria-controls="sidebar"><span></span><span></span><span></span></button><div class="breadcrumb"><span>AbsenKu</span><span class="breadcrumb-divider">/</span><strong>Input Absensi</strong></div><div class="topbar-right"><span class="today-indicator"><span class="live-dot"></span> Absensi hari ini</span><span class="user-avatar" aria-hidden="true"><?php echo htmlspecialchars(strtoupper(substr($_SESSION['email_sekretaris'], 0, 1)), ENT_QUOTES, 'UTF-8'); ?></span></div></header>
            <main class="content">
                <section class="page-heading"><div><p class="eyebrow">PENCATATAN KELAS · <?php echo htmlspecialchars($settings['class_name'], ENT_QUOTES, 'UTF-8'); ?></p><h1>Input absensi.</h1><p class="heading-note">Catat kehadiran siswa untuk <?php echo htmlspecialchars($settings['academic_year'] . ' · Semester ' . $settings['semester'], ENT_QUOTES, 'UTF-8'); ?>.</p></div><a class="back-link" href="dashboard.php"><span aria-hidden="true">←</span> Dashboard</a></section>
                <?php if ($pesan !== ''): ?><div class="notice" role="status"><span class="notice-mark">✓</span><?php echo htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                <section class="attendance-section form-section">
                    <div class="section-heading"><div><p class="eyebrow">ABSENSI HARI INI</p><h2>Daftar siswa</h2></div><span class="progress-count"><strong><?php echo $jumlah_sudah_absen; ?></strong> / <?php echo count($daftar_siswa); ?> tercatat</span></div>
                    <form method="POST" action="" class="attendance-form">
                        <div class="form-toolbar"><label class="date-field"><span>Tanggal absensi</span><input type="date" value="<?php echo htmlspecialchars($tanggal_hari_ini, ENT_QUOTES, 'UTF-8'); ?>" disabled><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>"></label><span class="pending-label"><span class="pending-dot"></span><?php echo $jumlah_belum_absen; ?> belum diabsen · Batas <?php echo htmlspecialchars(substr($settings['attendance_cutoff'], 0, 5), ENT_QUOTES, 'UTF-8'); ?> WIB</span></div>
                        <div class="table-scroll"><table class="attendance-table input-table"><thead><tr><th class="number-col">NO</th><th>NIS</th><th>NAMA SISWA</th><th>STATUS KEHADIRAN</th><th>KETERANGAN</th></tr></thead><tbody>
                            <?php foreach ($daftar_siswa as $index => $siswa): $id = (int) $siswa['id_siswa']; ?>
                            <tr>
                                <td class="row-number"><?php echo str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT); ?></td>
                                <td class="student-nis"><?php echo htmlspecialchars($siswa['nis'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><span class="student-name"><?php echo htmlspecialchars($siswa['nama_siswa'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td><?php if ($siswa['status_hari_ini'] !== null && strpos((string) $siswa['keterangan_hari_ini'], 'AUTO: melewati batas absensi') !== 0): ?><span class="status-badge status-<?php echo strtolower(htmlspecialchars($siswa['status_hari_ini'], ENT_QUOTES, 'UTF-8')); ?>"><span></span><?php echo htmlspecialchars($siswa['status_hari_ini'], ENT_QUOTES, 'UTF-8'); ?></span><span class="saved-label">Tercatat</span><?php else: ?><div class="status-options" role="group" aria-label="Status <?php echo htmlspecialchars($siswa['nama_siswa'], ENT_QUOTES, 'UTF-8'); ?>"><?php foreach ($settings['attendance_statuses'] as $status): ?><label class="status-choice"><input type="radio" name="status[<?php echo $id; ?>]" value="<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($status === ($siswa['status_hari_ini'] ?? 'Hadir')) ? 'checked' : ''; ?>><span><?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></span></label><?php endforeach; ?></div><?php if (strpos((string) $siswa['keterangan_hari_ini'], 'AUTO: melewati batas absensi') === 0): ?><span class="saved-label">Alpa otomatis · dapat dikoreksi</span><?php endif; ?><?php endif; ?></td>
                                <td><?php if ($siswa['status_hari_ini'] !== null && strpos((string) $siswa['keterangan_hari_ini'], 'AUTO: melewati batas absensi') !== 0): ?><span class="muted-dash">—</span><?php else: ?><input class="note-input" type="text" name="keterangan[<?php echo $id; ?>]" placeholder="Opsional" aria-label="Keterangan untuk <?php echo htmlspecialchars($siswa['nama_siswa'], ENT_QUOTES, 'UTF-8'); ?>"><?php endif; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody></table></div>
                        <?php if (count($daftar_siswa) === 0): ?><div class="empty-state"><span class="empty-mark">—</span><strong>Belum ada data siswa</strong><p>Tambahkan siswa sebelum mengisi absensi.</p><a href="data_siswa.php">Buka data siswa <span aria-hidden="true">↗</span></a></div><?php endif; ?>
                        <div class="form-actions"><span class="form-hint">Hadir setelah batas akan menjadi Terlambat. Siswa tanpa catatan setelah batas otomatis berstatus Alpa.</span><button class="primary-button" type="submit" <?php echo $jumlah_belum_absen === 0 && !$cutoff_terlewati ? 'disabled' : ''; ?>>Simpan absensi <span aria-hidden="true">↗</span></button></div>
                    </form>
                </section>
                <footer class="page-footer"><span>ABSENKU <span aria-hidden="true">·</span> PANEL KEHADIRAN</span><span><?php echo date('Y'); ?></span></footer>
            </main>
        </div>
    </div>
    <div class="sidebar-scrim" id="sidebarScrim"></div>
    <script src="assets/js/halaman.js"></script>
</body>
</html>
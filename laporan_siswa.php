<?php
session_start();
if (!isset($_SESSION['email_sekretaris'])) {
    header('Location: index.php');
    exit();
}

include 'config/koneksi.php';
require_once 'config/app_settings.php';
app_settings_initialize($koneksi);
app_require_role(['sekretaris', 'wali_kelas']);
$settings = app_settings_get($koneksi);
$signature_path = $settings['secretary_signature_path'] ?? '';
$signature_exists = preg_match('#^assets/img/signatures/[a-f0-9]{32}\.(png|jpg)$#', $signature_path)
    && is_file(__DIR__ . '/' . $signature_path);
$is_secretary = ($_SESSION['role'] ?? 'sekretaris') === 'sekretaris';
$statuses = $settings['attendance_statuses'];
$academic_year_parts = explode('/', $settings['academic_year']);
$academic_start = (int) ($academic_year_parts[0] ?? date('Y'));
$academic_end = (int) ($academic_year_parts[1] ?? ($academic_start + 1));
if ($settings['semester'] === 'Ganjil') {
    $period_start = sprintf('%04d-07-01', $academic_start);
    $period_end = sprintf('%04d-12-31', $academic_start);
} else {
    $period_start = sprintf('%04d-01-01', $academic_end);
    $period_end = sprintf('%04d-06-30', $academic_end);
}
$report_month = $_GET['bulan'] ?? date('Y-m');
$month_date = DateTime::createFromFormat('!Y-m', $report_month);
if (!$month_date || $month_date->format('Y-m') !== $report_month) {
    $report_month = date('Y-m');
    $month_date = DateTime::createFromFormat('!Y-m', $report_month);
}
$month_start = $month_date->format('Y-m-01');
$month_end = $month_date->format('Y-m-t');
if ($month_end < $period_start || $month_start > $period_end) {
    $report_month = substr($period_start, 0, 7);
    $month_date = DateTime::createFromFormat('!Y-m', $report_month);
    $month_start = $month_date->format('Y-m-01');
    $month_end = $month_date->format('Y-m-t');
}
$report_start = max($month_start, $period_start);
$report_end = min($month_end, $period_end);
$bulan_indonesia = ['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'];
$report_month_label = $bulan_indonesia[$month_date->format('m')] . ' ' . $month_date->format('Y');
$select_status = [];
foreach ($statuses as $index => $status) {
    $safe_status = mysqli_real_escape_string($koneksi, $status);
    $select_status[] = "SUM(CASE WHEN tabel_absensi.status = '$safe_status' THEN 1 ELSE 0 END) AS status_count_$index";
}
$query = "SELECT tabel_siswa.nis, tabel_siswa.nama_siswa, " . implode(', ', $select_status) . "
          FROM tabel_siswa
          LEFT JOIN tabel_absensi ON tabel_siswa.id_siswa = tabel_absensi.id_siswa
          AND tabel_absensi.tanggal BETWEEN '$report_start' AND '$report_end'
          GROUP BY tabel_siswa.id_siswa
          ORDER BY tabel_siswa.nama_siswa ASC";
$ambil = mysqli_query($koneksi, $query);
$daftar_siswa = [];
$ringkasan = array_fill_keys($statuses, 0);
while ($data = mysqli_fetch_assoc($ambil)) {
    $daftar_siswa[] = $data;
    foreach ($statuses as $index => $status) $ringkasan[$status] += (int) $data['status_count_' . $index];
}
$jumlah_catatan = array_sum($ringkasan);

if (($_GET['export'] ?? '') === 'excel') {
    $safe_cell = static function ($value) {
        $value = (string) $value;
        return preg_match('/^[=+@\-]/', $value) ? "'" . $value : $value;
    };
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="rekap-absensi-' . $report_month . '.csv"');
    echo "\xEF\xBB\xBF";
    $output = fopen('php://output', 'w');
    fputcsv($output, array_map($safe_cell, array_merge(['NIS', 'Nama Siswa'], $statuses)));
    foreach ($daftar_siswa as $data) {
        $row = [$data['nis'], $data['nama_siswa']];
        foreach ($statuses as $index => $status) $row[] = (int) $data['status_count_' . $index];
        fputcsv($output, array_map($safe_cell, $row));
    }
    fclose($output);
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#171918">
    <title>Laporan Absensi | AbsenKu</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/halaman.css">
    <link rel="stylesheet" href="assets/css/settings.css">
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar no-print" id="sidebar">
            <a class="brand" href="dashboard.php" aria-label="AbsenKu, dashboard"><span class="brand-mark">A</span><span>Absen<span class="brand-light">Ku</span></span></a>
            <div class="sidebar-label">MENU UTAMA</div>
            <nav class="side-nav" aria-label="Navigasi utama">
                <a class="nav-link" href="dashboard.php"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg><span>Dashboard</span></a>
                <?php if ($is_secretary): ?>
                <a class="nav-link" href="data_siswa.php"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M3 20v-1.5A4.5 4.5 0 0 1 7.5 14h3a4.5 4.5 0 0 1 4.5 4.5V20M17 11a3 3 0 1 0 0-6M17 14h.5a3.5 3.5 0 0 1 3.5 3.5V20"/></svg><span>Data Siswa</span></a>
                <a class="nav-link" href="input_absen.php"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="5" width="16" height="16" rx="2"/><path d="M8 3v4m8-4v4M4 10h16m-11 5 2 2 4-4"/></svg><span>Input Absensi</span></a>
                <?php endif; ?>
                <a class="nav-link is-active" href="laporan_siswa.php" aria-current="page"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h9l4 4v14H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path d="M14 3v5h5M8 12h8M8 16h8"/></svg><span>Laporan</span></a>
                <?php if ($is_secretary): ?><a class="nav-link" href="settings.php"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="m19.4 15 .1.1a1.7 1.7 0 0 1-2.4 2.4l-.1-.1a1.7 1.7 0 0 0-2.9 1.2v.2a1.7 1.7 0 0 1-3.4 0v-.2a1.7 1.7 0 0 0-2.9-1.2l-.1.1a1.7 1.7 0 0 1-2.4-2.4l.1-.1a1.7 1.7 0 0 0-1.2-2.9H4a1.7 1.7 0 0 1 0-3.4h.2a1.7 1.7 0 0 0 1.2-2.9l-.1-.1a1.7 1.7 0 0 1 2.4-2.4l.1.1a1.7 1.7 0 0 0 2.9-1.2V2a1.7 1.7 0 0 1 3.4 0v.2a1.7 1.7 0 0 0 2.9 1.2l.1-.1a1.7 1.7 0 0 1 2.4 2.4l-.1.1a1.7 1.7 0 0 0 1.2 2.9h.2a1.7 1.7 0 0 1 0 3.4h-.2a1.7 1.7 0 0 0-1.2 2.9Z"/></svg><span>Settings</span></a><?php endif; ?>
            </nav>
            <div class="sidebar-bottom"><div class="school-card"><span class="school-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 9 9-5 9 5-9 5-9-5Z"/><path d="M7 11.3V16c3 2.7 7 2.7 10 0v-4.7M21 9v6"/></svg></span><span><strong>Ruang Kelas</strong><small>Panel sekretaris</small></span></div><a class="nav-link logout-link" href="proses/logout.php"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17l5-5-5-5m5 5H3"/><path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/></svg><span>Keluar</span></a></div>
        </aside>
        <div class="main-column">
            <header class="topbar no-print"><button class="menu-toggle" id="menuToggle" type="button" aria-label="Buka menu" aria-expanded="false" aria-controls="sidebar"><span></span><span></span><span></span></button><div class="breadcrumb"><span>AbsenKu</span><span class="breadcrumb-divider">/</span><strong>Laporan</strong></div><div class="topbar-right"><span class="today-indicator"><span class="live-dot"></span> Rekap keseluruhan</span><span class="user-avatar" aria-hidden="true"><?php echo htmlspecialchars(strtoupper(substr($_SESSION['email_sekretaris'], 0, 1)), ENT_QUOTES, 'UTF-8'); ?></span></div></header>
            <main class="content report-content">
                <section class="page-heading no-print"><div><p class="eyebrow">ARSIP KEHADIRAN · <?php echo htmlspecialchars($settings['class_name'], ENT_QUOTES, 'UTF-8'); ?></p><h1>Laporan siswa.</h1><p class="heading-note">Tahun ajaran <?php echo htmlspecialchars($settings['academic_year'], ENT_QUOTES, 'UTF-8'); ?> · Semester <?php echo htmlspecialchars($settings['semester'], ENT_QUOTES, 'UTF-8'); ?></p></div><div class="report-actions"><a class="secondary-button" href="laporan_siswa.php?export=excel&amp;bulan=<?php echo htmlspecialchars($report_month, ENT_QUOTES, 'UTF-8'); ?>">Unduh Excel <span aria-hidden="true">↓</span></a><button class="primary-button print-button" type="button" onclick="window.print()"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 8V3h10v5M7 17H5a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M7 14h10v7H7z"/></svg> Cetak / Simpan PDF</button></div></section>
                <section class="report-summary no-print" aria-label="Ringkasan semua catatan">
                    <div><span>Total catatan</span><strong><?php echo $jumlah_catatan; ?></strong></div>
                    <?php foreach ($statuses as $status): ?><div><span><?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></span><strong><?php echo $ringkasan[$status]; ?></strong></div><?php endforeach; ?>
                </section>
                <section class="attendance-section report-section">
                    <div class="section-heading"><div><p class="eyebrow">REKAPITULASI</p><h2>Riwayat per siswa</h2></div><span class="section-index"><?php echo count($daftar_siswa); ?> SISWA</span></div>
                    <div class="report-meta-print"><strong><?php echo htmlspecialchars($settings['class_name'], ENT_QUOTES, 'UTF-8'); ?></strong><span>Rekap bulan <?php echo htmlspecialchars($report_month_label, ENT_QUOTES, 'UTF-8'); ?> · Tahun ajaran <?php echo htmlspecialchars($settings['academic_year'], ENT_QUOTES, 'UTF-8'); ?> · Semester <?php echo htmlspecialchars($settings['semester'], ENT_QUOTES, 'UTF-8'); ?></span></div>
                    <div class="table-toolbar no-print"><label class="search-box"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m16 16 4 4"/></svg><input id="pageSearch" type="search" placeholder="Cari nama atau NIS..." autocomplete="off" aria-label="Cari laporan siswa"><kbd>/</kbd></label><form class="month-filter" method="get"><label><span>Bulan</span><input type="month" name="bulan" min="<?php echo substr($period_start, 0, 7); ?>" max="<?php echo substr($period_end, 0, 7); ?>" value="<?php echo htmlspecialchars($report_month, ENT_QUOTES, 'UTF-8'); ?>"></label><button type="submit">Terapkan</button></form></div>
                    <div class="table-scroll"><table class="attendance-table report-table"><thead><tr><th class="number-col">NO</th><th>NIS</th><th>NAMA SISWA</th><?php foreach ($statuses as $status): ?><th class="numeric-cell"><?php echo htmlspecialchars(mb_strtoupper($status), ENT_QUOTES, 'UTF-8'); ?></th><?php endforeach; ?></tr></thead><tbody id="pageRows">
                        <?php foreach ($daftar_siswa as $index => $data): ?>
                        <tr><td class="row-number"><?php echo str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT); ?></td><td class="student-nis"><?php echo htmlspecialchars($data['nis'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td><td><span class="student-name"><?php echo htmlspecialchars($data['nama_siswa'], ENT_QUOTES, 'UTF-8'); ?></span></td><?php foreach ($statuses as $status_index => $status): ?><td class="numeric-cell"><strong><?php echo (int) $data['status_count_' . $status_index]; ?></strong></td><?php endforeach; ?></tr>
                        <?php endforeach; ?>
                    </tbody><tfoot><tr class="signature-table-row"><td colspan="<?php echo 3 + count($statuses); ?>"><div class="report-signatures report-signatures-secretary"><div><span><?php echo htmlspecialchars($settings['class_name'], ENT_QUOTES, 'UTF-8'); ?>, <?php echo date('d/m/Y'); ?></span><strong>Sekretaris</strong><?php if ($signature_exists): ?><img class="report-signature-image" src="<?php echo htmlspecialchars($signature_path, ENT_QUOTES, 'UTF-8'); ?>" alt="Tanda tangan sekretaris"><?php else: ?><span class="signature-line"></span><?php endif; ?><strong><?php echo htmlspecialchars($settings['principal_name'] ?: '........................................', ENT_QUOTES, 'UTF-8'); ?></strong><small>NISN <?php echo htmlspecialchars($settings['secretary_nisn'] ?: '—', ENT_QUOTES, 'UTF-8'); ?></small></div></div></td></tr></tfoot></table><div class="empty-state" id="emptyState" <?php echo count($daftar_siswa) > 0 ? 'hidden' : ''; ?>><span class="empty-mark">—</span><strong><?php echo count($daftar_siswa) > 0 ? 'Siswa tidak ditemukan' : 'Belum ada laporan'; ?></strong><p><?php echo count($daftar_siswa) > 0 ? 'Coba kata kunci yang berbeda.' : 'Data rekap akan muncul setelah siswa ditambahkan.'; ?></p></div></div>
                    <div class="table-footer no-print"><span id="resultCount">Menampilkan <?php echo count($daftar_siswa); ?> siswa</span><span class="footer-note">Diperbarui <?php echo date('d/m/Y'); ?></span></div>
                </section>
                <footer class="page-footer no-print"><span>ABSENKU <span aria-hidden="true">·</span> PANEL KEHADIRAN</span><span><?php echo date('Y'); ?></span></footer>
            </main>
        </div>
    </div>
    <div class="sidebar-scrim no-print" id="sidebarScrim"></div>
    <script src="assets/js/halaman.js"></script>
</body>
</html>
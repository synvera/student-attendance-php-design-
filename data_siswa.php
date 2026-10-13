<?php
session_start();
include 'config/koneksi.php';
require_once 'config/app_settings.php';
app_settings_initialize($koneksi);
app_require_role(['sekretaris']);
$settings = app_settings_get($koneksi);
$pesan = '';
$edit_siswa = null;
$csrf_token = app_csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !app_verify_csrf()) {
    $pesan = 'Sesi formulir tidak valid. Muat ulang halaman lalu coba kembali.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && app_verify_csrf() && isset($_POST['update_siswa'])) {
    $id_update = (int) ($_POST['id_siswa'] ?? 0);
    $nis_update = mysqli_real_escape_string($koneksi, trim($_POST['nis'] ?? ''));
    $nama_update = mysqli_real_escape_string($koneksi, trim($_POST['nama_siswa'] ?? ''));
    if ($id_update > 0 && $nis_update !== '' && $nama_update !== '') {
        mysqli_query($koneksi, "UPDATE tabel_siswa SET nis = '$nis_update', nama_siswa = '$nama_update' WHERE id_siswa = '$id_update'");
        header('Location: data_siswa.php?updated=1');
        exit();
    }
}

// Jika tombol "Tambah Siswa" ditekan
if ($_SERVER["REQUEST_METHOD"] == "POST" && app_verify_csrf() && isset($_POST['tambah_siswa'])) {
    $nis = mysqli_real_escape_string($koneksi, $_POST['nis']);
    $nama_siswa = mysqli_real_escape_string($koneksi, $_POST['nama_siswa']);

    // Simpan ke database
    $query_insert = "INSERT INTO tabel_siswa (nis, nama_siswa) VALUES ('$nis', '$nama_siswa')";
    
    if (mysqli_query($koneksi, $query_insert)) {
            $pesan = "Berhasil menambahkan $nama_siswa ke dalam data kelas.";
    } else {
        $pesan = "Gagal menambahkan data.";
    }
}

if (isset($_GET['updated'])) $pesan = 'Data siswa berhasil diperbarui.';
if (isset($_GET['edit'])) {
    $id_edit = (int) $_GET['edit'];
    $result_edit = mysqli_query($koneksi, "SELECT id_siswa, nis, nama_siswa FROM tabel_siswa WHERE id_siswa = '$id_edit'");
    $edit_siswa = $result_edit ? mysqli_fetch_assoc($result_edit) : null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && app_verify_csrf() && isset($_POST['hapus_siswa'])) {
    $id_hapus = (int) ($_POST['id_siswa'] ?? 0);
    mysqli_begin_transaction($koneksi);
    $delete_attendance = mysqli_query($koneksi, "DELETE FROM tabel_absensi WHERE id_siswa = '$id_hapus'");
    $delete_student = $delete_attendance ? mysqli_query($koneksi, "DELETE FROM tabel_siswa WHERE id_siswa = '$id_hapus'") : false;
    if ($delete_student) {
        mysqli_commit($koneksi);
        header('Location: data_siswa.php?deleted=1');
        exit();
    }
    mysqli_rollback($koneksi);
    $pesan = 'Data siswa gagal dihapus.';
}

if (isset($_GET['deleted'])) $pesan = 'Data siswa dan riwayat absensinya berhasil dihapus.';
    $daftar_siswa = mysqli_query($koneksi, "SELECT * FROM tabel_siswa ORDER BY nama_siswa ASC");
    $total_siswa = mysqli_num_rows($daftar_siswa);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#171918">
    <title>Data Siswa | AbsenKu</title>
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
                <a class="nav-link is-active" href="data_siswa.php" aria-current="page"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M3 20v-1.5A4.5 4.5 0 0 1 7.5 14h3a4.5 4.5 0 0 1 4.5 4.5V20M17 11a3 3 0 1 0 0-6M17 14h.5a3.5 3.5 0 0 1 3.5 3.5V20"/></svg><span>Data Siswa</span></a>
                <a class="nav-link" href="input_absen.php"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="5" width="16" height="16" rx="2"/><path d="M8 3v4m8-4v4M4 10h16m-11 5 2 2 4-4"/></svg><span>Input Absensi</span></a>
                <a class="nav-link" href="laporan_siswa.php"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h9l4 4v14H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path d="M14 3v5h5M8 12h8M8 16h8"/></svg><span>Laporan</span></a>
                <a class="nav-link" href="settings.php"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="m19.4 15 .1.1a1.7 1.7 0 0 1-2.4 2.4l-.1-.1a1.7 1.7 0 0 0-2.9 1.2v.2a1.7 1.7 0 0 1-3.4 0v-.2a1.7 1.7 0 0 0-2.9-1.2l-.1.1a1.7 1.7 0 0 1-2.4-2.4l.1-.1a1.7 1.7 0 0 0-1.2-2.9H4a1.7 1.7 0 0 1 0-3.4h.2a1.7 1.7 0 0 0 1.2-2.9l-.1-.1a1.7 1.7 0 0 1 2.4-2.4l.1.1a1.7 1.7 0 0 0 2.9-1.2V2a1.7 1.7 0 0 1 3.4 0v.2a1.7 1.7 0 0 0 2.9 1.2l.1-.1a1.7 1.7 0 0 1 2.4 2.4l-.1.1a1.7 1.7 0 0 0 1.2 2.9h.2a1.7 1.7 0 0 1 0 3.4h-.2a1.7 1.7 0 0 0-1.2 2.9Z"/></svg><span>Settings</span></a>
            </nav>
            <div class="sidebar-bottom"><div class="school-card"><span class="school-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 9 9-5 9 5-9 5-9-5Z"/><path d="M7 11.3V16c3 2.7 7 2.7 10 0v-4.7M21 9v6"/></svg></span><span><strong>Ruang Kelas</strong><small>Panel sekretaris</small></span></div><a class="nav-link logout-link" href="proses/logout.php"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17l5-5-5-5m5 5H3"/><path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/></svg><span>Keluar</span></a></div>
        </aside>
        <div class="main-column">
            <header class="topbar"><button class="menu-toggle" id="menuToggle" type="button" aria-label="Buka menu" aria-expanded="false" aria-controls="sidebar"><span></span><span></span><span></span></button><div class="breadcrumb"><span>AbsenKu</span><span class="breadcrumb-divider">/</span><strong>Data Siswa</strong></div><div class="topbar-right"><span class="today-indicator"><span class="live-dot"></span> Kelola kelas</span><span class="user-avatar" aria-hidden="true"><?php echo htmlspecialchars(strtoupper(substr($_SESSION['email_sekretaris'], 0, 1)), ENT_QUOTES, 'UTF-8'); ?></span></div></header>
            <main class="content">
                <section class="page-heading"><div><p class="eyebrow">MANAJEMEN KELAS · <?php echo htmlspecialchars($settings['class_name'], ENT_QUOTES, 'UTF-8'); ?></p><h1>Data siswa.</h1><p class="heading-note">Kelola siswa · Tahun ajaran <?php echo htmlspecialchars($settings['academic_year'], ENT_QUOTES, 'UTF-8'); ?> · Semester <?php echo htmlspecialchars($settings['semester'], ENT_QUOTES, 'UTF-8'); ?>.</p></div><span class="count-chip"><strong><?php echo $total_siswa; ?></strong> siswa terdaftar</span></section>
                <?php if ($pesan !== ''): ?><div class="notice <?php echo strpos($pesan, 'Gagal') === 0 ? 'notice-error' : ''; ?>" role="status"><span class="notice-mark"><?php echo strpos($pesan, 'Gagal') === 0 ? '!' : '✓'; ?></span><?php echo htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                <section class="attendance-section add-student-section">
                    <div class="section-heading"><div><p class="eyebrow">PROFIL KELAS</p><h2><?php echo $edit_siswa ? 'Edit data siswa' : 'Tambah siswa'; ?></h2></div><span class="section-index">01 / DATA SISWA</span></div>
                    <form method="POST" action="" class="student-form">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php if ($edit_siswa): ?><input type="hidden" name="id_siswa" value="<?php echo (int) $edit_siswa['id_siswa']; ?>"><?php endif; ?>
                        <label><span>Nomor induk siswa</span><input type="text" name="nis" placeholder="Contoh: 2425123456" autocomplete="off" value="<?php echo htmlspecialchars($edit_siswa['nis'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required></label>
                        <label><span>Nama lengkap</span><input type="text" name="nama_siswa" placeholder="Masukkan nama siswa" autocomplete="name" value="<?php echo htmlspecialchars($edit_siswa['nama_siswa'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required></label>
                        <?php if ($edit_siswa): ?><button type="submit" name="update_siswa" class="primary-button">Simpan perubahan <span aria-hidden="true">✓</span></button><a class="cancel-edit-link" href="data_siswa.php">Batal</a><?php else: ?><button type="submit" name="tambah_siswa" class="primary-button">Tambah siswa <span aria-hidden="true">+</span></button><?php endif; ?>
                    </form>
                </section>
                <section class="attendance-section student-list-section">
                    <div class="section-heading"><div><p class="eyebrow">DAFTAR TERDAFTAR</p><h2>Semua siswa</h2></div><span class="section-index"><strong><?php echo $total_siswa; ?></strong> TOTAL</span></div>
                    <div class="table-toolbar"><label class="search-box"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m16 16 4 4"/></svg><input id="pageSearch" type="search" placeholder="Cari nama atau NIS..." autocomplete="off" aria-label="Cari siswa"><kbd>/</kbd></label></div>
                    <div class="table-scroll"><table class="attendance-table"><thead><tr><th class="number-col">NO</th><th>NIS</th><th>NAMA SISWA</th><th class="actions-column">AKSI</th></tr></thead><tbody id="pageRows">
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($daftar_siswa)): ?>
                        <tr><td class="row-number"><?php echo str_pad((string) $no++, 2, '0', STR_PAD_LEFT); ?></td><td class="student-nis"><?php echo htmlspecialchars($row['nis'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td><td><span class="student-name"><?php echo htmlspecialchars($row['nama_siswa'], ENT_QUOTES, 'UTF-8'); ?></span></td><td><div class="student-actions"><a href="data_siswa.php?edit=<?php echo (int) $row['id_siswa']; ?>" class="edit-link">Edit</a><form method="post" onsubmit="return confirm(<?php echo htmlspecialchars(json_encode('Yakin ingin menghapus ' . $row['nama_siswa'] . ' beserta riwayat absensinya?'), ENT_QUOTES, 'UTF-8'); ?>)"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="id_siswa" value="<?php echo (int) $row['id_siswa']; ?>"><button class="delete-link" type="submit" name="hapus_siswa">Hapus</button></form></div></td></tr>
                        <?php endwhile; ?>
                    </tbody></table>
                    <div class="empty-state" id="emptyState" <?php echo $total_siswa > 0 ? 'hidden' : ''; ?>><span class="empty-mark">—</span><strong><?php echo $total_siswa > 0 ? 'Siswa tidak ditemukan' : 'Belum ada data siswa'; ?></strong><p><?php echo $total_siswa > 0 ? 'Coba kata kunci yang berbeda.' : 'Tambahkan siswa menggunakan formulir di atas.'; ?></p></div></div>
                    <div class="table-footer"><span id="resultCount">Menampilkan <?php echo $total_siswa; ?> siswa</span><span class="footer-note">Urut berdasarkan nama</span></div>
                </section>
                <footer class="page-footer"><span>ABSENKU <span aria-hidden="true">·</span> PANEL KEHADIRAN</span><span><?php echo date('Y'); ?></span></footer>
            </main>
        </div>
    </div>
    <div class="sidebar-scrim" id="sidebarScrim"></div>
    <script src="assets/js/halaman.js"></script>
</body>
</html>
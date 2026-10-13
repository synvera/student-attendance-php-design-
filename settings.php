<?php
session_start();
include 'config/koneksi.php';
require_once 'config/app_settings.php';
app_settings_initialize($koneksi);
app_require_role(['sekretaris']);

$email_sekretaris = $_SESSION['email_sekretaris'];
$inisial = strtoupper(substr($email_sekretaris, 0, 1));
$settings = app_settings_get($koneksi);
$pesan = '';
$jenis_pesan = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (!app_verify_csrf()) {
		$pesan = 'Sesi formulir tidak valid. Muat ulang halaman lalu coba kembali.';
		$jenis_pesan = 'error';
	} else {
		$aksi = $_POST['action'] ?? '';
		if ($aksi === 'upload_signature') {
			$file = $_FILES['secretary_signature'] ?? null;
			if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
				$pesan = 'Pilih file tanda tangan PNG atau JPG yang valid.';
				$jenis_pesan = 'error';
			} elseif ($file['size'] > 2 * 1024 * 1024) {
				$pesan = 'Ukuran gambar maksimal 2 MB.';
				$jenis_pesan = 'error';
			} else {
				$image_info = @getimagesize($file['tmp_name']);
				$file_info = new finfo(FILEINFO_MIME_TYPE);
				$mime_type = $file_info->file($file['tmp_name']);
				$allowed_images = ['image/png' => 'png', 'image/jpeg' => 'jpg'];
				if (!$image_info || !isset($allowed_images[$mime_type]) || $image_info['mime'] !== $mime_type) {
					$pesan = 'File harus berupa gambar PNG atau JPG yang valid.';
					$jenis_pesan = 'error';
				} else {
					$upload_directory = __DIR__ . '/assets/img/signatures';
					if (!is_dir($upload_directory) && !mkdir($upload_directory, 0750, true) && !is_dir($upload_directory)) {
						$pesan = 'Folder tanda tangan tidak dapat dibuat.';
						$jenis_pesan = 'error';
					} else {
						$file_name = bin2hex(random_bytes(16)) . '.' . $allowed_images[$mime_type];
						$new_path = 'assets/img/signatures/' . $file_name;
						if (move_uploaded_file($file['tmp_name'], $upload_directory . '/' . $file_name)) {
							$update_signature = mysqli_prepare($koneksi, 'UPDATE app_settings SET secretary_signature_path = ? WHERE id = 1');
							mysqli_stmt_bind_param($update_signature, 's', $new_path);
							$save_ok = mysqli_stmt_execute($update_signature);
							mysqli_stmt_close($update_signature);
							if ($save_ok) {
													$old_path = $settings['secretary_signature_path'] ?? '';
													if (preg_match('#^assets/img/signatures/[a-f0-9]{32}\.(png|jpg)$#', $old_path) && is_file(__DIR__ . '/' . $old_path)) unlink(__DIR__ . '/' . $old_path);
								$pesan = 'Tanda tangan sekretaris berhasil diperbarui.';
							} else {
								unlink($upload_directory . '/' . $file_name);
								$pesan = 'Tanda tangan gagal disimpan ke pengaturan.';
								$jenis_pesan = 'error';
							}
						} else {
							$pesan = 'File tanda tangan gagal diunggah.';
							$jenis_pesan = 'error';
						}
					}
				}
			}
		} elseif ($aksi === 'remove_signature') {
			$old_path = $settings['secretary_signature_path'] ?? '';
			$remove_signature = mysqli_query($koneksi, "UPDATE app_settings SET secretary_signature_path = '' WHERE id = 1");
			if ($remove_signature) {
				if (preg_match('#^assets/img/signatures/[a-f0-9]{32}\.(png|jpg)$#', $old_path) && is_file(__DIR__ . '/' . $old_path)) unlink(__DIR__ . '/' . $old_path);
				$pesan = 'Gambar tanda tangan dihapus. Laporan akan memakai garis tanda tangan.';
			} else {
				$pesan = 'Tanda tangan gagal dihapus.';
				$jenis_pesan = 'error';
			}
		} elseif ($aksi === 'save_class') {
			$class_name = trim($_POST['class_name'] ?? '');
			$homeroom_teacher = trim($_POST['homeroom_teacher'] ?? '');
			$homeroom_nip = trim($_POST['homeroom_nip'] ?? '');
			$academic_year = trim($_POST['academic_year'] ?? '');
			$semester = $_POST['semester'] ?? 'Ganjil';
			$cutoff_time = $_POST['attendance_cutoff'] ?? '07:15';
			$principal_name = trim($_POST['principal_name'] ?? '');
			$secretary_nisn = trim($_POST['secretary_nisn'] ?? '');
			$status_lines = preg_split('/\R/', $_POST['attendance_statuses'] ?? 'Hadir' . PHP_EOL . 'Sakit' . PHP_EOL . 'Izin' . PHP_EOL . 'Alpa');
			$statuses = array_values(array_unique(array_filter(array_map('trim', $status_lines))));
			$time_is_valid = preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $cutoff_time);
			$year_is_valid = preg_match('/^\d{4}\/\d{4}$/', $academic_year);

			if ($class_name === '' || !$year_is_valid || !in_array($semester, ['Ganjil', 'Genap'], true) || !$time_is_valid || count($statuses) < 2 || count($statuses) > 10 || !in_array('Hadir', $statuses, true) || !in_array('Alpa', $statuses, true)) {
				$pesan = 'Periksa kembali nama kelas, periode, tenggat, dan status. Hadir serta Alpa wajib tersedia; jumlah status maksimal 10.';
				$jenis_pesan = 'error';
			} else {
				foreach ([$class_name, $homeroom_teacher, $academic_year, $principal_name] as $value) {
					if (mb_strlen($value) > 150) $pesan = 'Teks pengaturan terlalu panjang.';
				}
				if (mb_strlen($homeroom_nip) > 40) $pesan = 'NIP wali kelas maksimal 40 karakter.';
				if ($secretary_nisn !== '' && !preg_match('/^[0-9]{10}$/', $secretary_nisn)) $pesan = 'NISN sekretaris harus terdiri dari 10 angka.';
				if ($pesan === '') {
					foreach ($statuses as $status) {
						if (mb_strlen($status) > 40) $pesan = 'Nama status maksimal 40 karakter.';
					}
				}
				if ($pesan === '') {
					$used_statuses = mysqli_query($koneksi, 'SELECT DISTINCT status FROM tabel_absensi');
					while ($used = mysqli_fetch_assoc($used_statuses)) {
						if (!in_array($used['status'], $statuses, true)) {
							$pesan = 'Status ' . $used['status'] . ' sudah dipakai pada riwayat absensi dan tidak dapat dihapus.';
							break;
						}
					}
					mysqli_free_result($used_statuses);
				}
				if ($pesan === '') {
					$statuses_json = json_encode($statuses, JSON_UNESCAPED_UNICODE);
					$save = mysqli_prepare($koneksi, 'UPDATE app_settings SET class_name = ?, homeroom_teacher = ?, homeroom_nip = ?, academic_year = ?, semester = ?, attendance_cutoff = ?, principal_name = ?, secretary_nisn = ?, attendance_statuses = ? WHERE id = 1');
					mysqli_stmt_bind_param($save, 'sssssssss', $class_name, $homeroom_teacher, $homeroom_nip, $academic_year, $semester, $cutoff_time, $principal_name, $secretary_nisn, $statuses_json);
					mysqli_stmt_execute($save);
					mysqli_stmt_close($save);
					$pesan = 'Pengaturan kelas berhasil disimpan.';
				} else {
					$jenis_pesan = 'error';
				}
			}
		} elseif ($aksi === 'add_user') {
			$name = trim($_POST['display_name'] ?? '');
			$new_email = strtolower(trim($_POST['email'] ?? ''));
			$password = $_POST['password'] ?? '';
			$role = $_POST['role'] ?? '';
			if ($name === '' || mb_strlen($name) > 150 || !filter_var($new_email, FILTER_VALIDATE_EMAIL) || strlen($new_email) > 100 || strlen($password) < 8 || !in_array($role, ['sekretaris', 'wali_kelas'], true)) {
				$pesan = 'Nama, email, role, atau password tidak valid. Password minimal 8 karakter.';
				$jenis_pesan = 'error';
			} else {
				$password_hash = password_hash($password, PASSWORD_DEFAULT);
				$add_user = mysqli_prepare($koneksi, 'INSERT INTO app_users (email, display_name, password_hash, role) VALUES (?, ?, ?, ?)');
				mysqli_stmt_bind_param($add_user, 'ssss', $new_email, $name, $password_hash, $role);
				if (mysqli_stmt_execute($add_user)) {
					$pesan = 'Akun baru berhasil ditambahkan.';
				} else {
					$pesan = mysqli_stmt_errno($add_user) === 1062 ? 'Email tersebut sudah terdaftar.' : 'Akun gagal ditambahkan.';
					$jenis_pesan = 'error';
				}
				mysqli_stmt_close($add_user);
			}
		} elseif ($aksi === 'remove_user') {
			$remove_id = (int) ($_POST['user_id'] ?? 0);
			$lookup = mysqli_prepare($koneksi, 'SELECT email, role FROM app_users WHERE id = ?');
			mysqli_stmt_bind_param($lookup, 'i', $remove_id);
			mysqli_stmt_execute($lookup);
			$target_user = mysqli_fetch_assoc(mysqli_stmt_get_result($lookup));
			mysqli_stmt_close($lookup);
			if (!$target_user || $target_user['email'] === $email_sekretaris) {
				$pesan = 'Akun aktif Anda tidak dapat dihapus.';
				$jenis_pesan = 'error';
			} elseif ($target_user['role'] === 'sekretaris') {
				$secretary_count = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM app_users WHERE role = 'sekretaris' AND is_active = 1");
				if ((int) mysqli_fetch_assoc($secretary_count)['total'] <= 1) {
					$pesan = 'Minimal harus ada satu akun sekretaris aktif.';
					$jenis_pesan = 'error';
				}
				mysqli_free_result($secretary_count);
			}
			if ($pesan === '') {
				$delete_user = mysqli_prepare($koneksi, 'DELETE FROM app_users WHERE id = ?');
				mysqli_stmt_bind_param($delete_user, 'i', $remove_id);
				mysqli_stmt_execute($delete_user);
				mysqli_stmt_close($delete_user);
				$pesan = 'Akun berhasil dihapus.';
			}
		} elseif ($aksi === 'reset_attendance') {
			if (($_POST['reset_confirmation'] ?? '') !== 'HAPUS RIWAYAT') {
				$pesan = 'Ketik HAPUS RIWAYAT dengan tepat untuk mengosongkan catatan absensi.';
				$jenis_pesan = 'error';
			} else {
				mysqli_begin_transaction($koneksi);
				$reset_ok = mysqli_query($koneksi, 'DELETE FROM tabel_absensi');
				if ($reset_ok) {
					mysqli_commit($koneksi);
					$pesan = 'Riwayat absensi berhasil dikosongkan. Data siswa tetap tersimpan.';
				} else {
					mysqli_rollback($koneksi);
					$pesan = 'Riwayat absensi gagal dikosongkan.';
					$jenis_pesan = 'error';
				}
			}
		}
	}
}

$settings = app_settings_get($koneksi);
$users = mysqli_query($koneksi, 'SELECT id, email, display_name, role, is_active FROM app_users ORDER BY role, display_name');
$attendance_total_result = mysqli_query($koneksi, 'SELECT COUNT(*) AS total FROM tabel_absensi');
$attendance_total = (int) mysqli_fetch_assoc($attendance_total_result)['total'];
mysqli_free_result($attendance_total_result);
$signature_path = $settings['secretary_signature_path'] ?? '';
$signature_exists = preg_match('#^assets/img/signatures/[a-f0-9]{32}\.(png|jpg)$#', $signature_path)
	&& is_file(__DIR__ . '/' . $signature_path);
$csrf_token = app_csrf_token();
?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="theme-color" content="#171918">
	<title>Settings | AbsenKu</title>
	<link rel="stylesheet" href="assets/css/dashboard.css">
	<link rel="stylesheet" href="assets/css/halaman.css">
	<link rel="stylesheet" href="assets/css/settings.css">
</head>
<body>
	<div class="app-shell">
		<aside class="sidebar" id="sidebar">
			<a class="brand" href="dashboard.php" aria-label="AbsenKu, dashboard"><span class="brand-mark">A</span><span>Absen<span class="brand-light">Ku</span></span></a>
			<div class="sidebar-label">MENU UTAMA</div>
			<nav class="side-nav" aria-label="Navigasi utama">
				<a class="nav-link" href="dashboard.php"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg><span>Dashboard</span></a>
				<a class="nav-link" href="data_siswa.php"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M3 20v-1.5A4.5 4.5 0 0 1 7.5 14h3a4.5 4.5 0 0 1 4.5 4.5V20M17 11a3 3 0 1 0 0-6M17 14h.5a3.5 3.5 0 0 1 3.5 3.5V20"/></svg><span>Data Siswa</span></a>
				<a class="nav-link" href="input_absen.php"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="5" width="16" height="16" rx="2"/><path d="M8 3v4m8-4v4M4 10h16m-11 5 2 2 4-4"/></svg><span>Input Absensi</span></a>
				<a class="nav-link" href="laporan_siswa.php"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h9l4 4v14H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path d="M14 3v5h5M8 12h8M8 16h8"/></svg><span>Laporan</span></a>
				<a class="nav-link is-active" href="settings.php" aria-current="page"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="m19.4 15 .1.1a1.7 1.7 0 0 1-2.4 2.4l-.1-.1a1.7 1.7 0 0 0-2.9 1.2v.2a1.7 1.7 0 0 1-3.4 0v-.2a1.7 1.7 0 0 0-2.9-1.2l-.1.1a1.7 1.7 0 0 1-2.4-2.4l.1-.1a1.7 1.7 0 0 0-1.2-2.9H4a1.7 1.7 0 0 1 0-3.4h.2a1.7 1.7 0 0 0 1.2-2.9l-.1-.1a1.7 1.7 0 0 1 2.4-2.4l.1.1a1.7 1.7 0 0 0 2.9-1.2V2a1.7 1.7 0 0 1 3.4 0v.2a1.7 1.7 0 0 0 2.9 1.2l.1-.1a1.7 1.7 0 0 1 2.4 2.4l-.1.1a1.7 1.7 0 0 0 1.2 2.9h.2a1.7 1.7 0 0 1 0 3.4h-.2a1.7 1.7 0 0 0-1.2 2.9Z"/></svg><span>Settings</span></a>
			</nav>
			<div class="sidebar-bottom"><div class="school-card"><span class="school-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 9 9-5 9 5-9 5-9-5Z"/><path d="M7 11.3V16c3 2.7 7 2.7 10 0v-4.7M21 9v6"/></svg></span><span><strong>Ruang Kelas</strong><small>Panel sekretaris</small></span></div><a class="nav-link logout-link" href="proses/logout.php"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17l5-5-5-5m5 5H3"/><path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/></svg><span>Keluar</span></a></div>
		</aside>
		<div class="main-column">
			<header class="topbar"><button class="menu-toggle" id="menuToggle" type="button" aria-label="Buka menu" aria-expanded="false" aria-controls="sidebar"><span></span><span></span><span></span></button><div class="breadcrumb"><span>AbsenKu</span><span class="breadcrumb-divider">/</span><strong>Settings</strong></div><div class="topbar-right"><span class="today-indicator"><span class="live-dot"></span> Preferensi aplikasi</span><span class="user-avatar" aria-hidden="true"><?php echo htmlspecialchars($inisial, ENT_QUOTES, 'UTF-8'); ?></span></div></header>
			<main class="content">
				<section class="page-heading"><div><p class="eyebrow">PREFERENSI APLIKASI</p><h1>Settings.</h1><p class="heading-note">Atur identitas kelas, kehadiran, akun, dan format laporan.</p></div></section>
				<?php if ($pesan !== ''): ?><div class="notice <?php echo $jenis_pesan === 'error' ? 'notice-error' : ''; ?>" role="status"><span class="notice-mark"><?php echo $jenis_pesan === 'error' ? '!' : '✓'; ?></span><?php echo htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
				<div class="settings-grid settings-functional-grid">
					<div class="settings-column">
						<section class="settings-panel">
							<div class="section-heading"><div><p class="eyebrow">IDENTITAS KELAS &amp; PERIODE</p><h2>Informasi akademik</h2></div></div>
							<form method="post" class="settings-form">
								<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="action" value="save_class">
								<div class="settings-fields">
									<label><span>Nama kelas</span><input name="class_name" maxlength="100" value="<?php echo htmlspecialchars($settings['class_name'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Contoh: XII RPL" required></label>
									<label><span>Wali kelas</span><input name="homeroom_teacher" maxlength="150" value="<?php echo htmlspecialchars($settings['homeroom_teacher'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Nama wali kelas"></label>
									<label><span>Tahun ajaran</span><input name="academic_year" pattern="[0-9]{4}/[0-9]{4}" value="<?php echo htmlspecialchars($settings['academic_year'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="2026/2027" required></label>
									<label><span>Semester</span><select name="semester"><option value="Ganjil" <?php echo $settings['semester'] === 'Ganjil' ? 'selected' : ''; ?>>Ganjil</option><option value="Genap" <?php echo $settings['semester'] === 'Genap' ? 'selected' : ''; ?>>Genap</option></select></label>
									<label><span>Batas absensi (WIB)</span><input type="time" name="attendance_cutoff" value="<?php echo htmlspecialchars(substr($settings['attendance_cutoff'], 0, 5), ENT_QUOTES, 'UTF-8'); ?>" required></label>
									<label class="settings-field-wide"><span>Status kehadiran <small>satu status per baris; Hadir dan Alpa wajib</small></span><textarea name="attendance_statuses" rows="5" maxlength="500" required><?php echo htmlspecialchars(implode(PHP_EOL, $settings['attendance_statuses']), ENT_QUOTES, 'UTF-8'); ?></textarea></label>
										<label><span>Nama Sekretaris</span><input name="principal_name" maxlength="150" value="<?php echo htmlspecialchars($settings['principal_name'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Nama sekretaris"></label>
										<label><span>NISN Sekretaris</span><input name="secretary_nisn" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" value="<?php echo htmlspecialchars($settings['secretary_nisn'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="10 digit NISN"></label>
								</div>
								<div class="settings-form-footer"><span>Perubahan langsung digunakan untuk absensi dan laporan.</span><button class="primary-button" type="submit">Simpan pengaturan <span aria-hidden="true">↗</span></button></div>
							</form>
						</section>
						<section class="settings-panel">
							<div class="section-heading"><div><p class="eyebrow">FORMAT CETAK</p><h2>Tanda tangan laporan</h2></div></div>
							<div class="settings-body"><div class="setting-row"><div class="setting-copy"><strong><?php echo htmlspecialchars($settings['principal_name'] ?: 'Sekretaris belum diisi', ENT_QUOTES, 'UTF-8'); ?></strong><small>Sekretaris · NISN <?php echo htmlspecialchars($settings['secretary_nisn'] ?: '—', ENT_QUOTES, 'UTF-8'); ?></small></div></div>
								<div class="signature-upload-panel">
									<div class="signature-upload-preview"><?php if ($signature_exists): ?><img src="<?php echo htmlspecialchars($signature_path, ENT_QUOTES, 'UTF-8'); ?>?v=<?php echo (int) filemtime(__DIR__ . '/' . $signature_path); ?>" alt="Tanda tangan sekretaris saat ini"><?php else: ?><span>Belum ada gambar tanda tangan</span><?php endif; ?></div>
									<form method="post" enctype="multipart/form-data" class="signature-upload-form"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="action" value="upload_signature"><label><span>Gambar tanda tangan sekretaris</span><input type="file" name="secretary_signature" accept="image/png,image/jpeg" required><small>PNG/JPG, maksimal 2 MB. Gunakan gambar dengan latar transparan atau putih.</small></label><button class="primary-button" type="submit">Unggah tanda tangan <span aria-hidden="true">↑</span></button></form>
									<?php if ($signature_exists): ?><form method="post" onsubmit="return confirm('Hapus gambar tanda tangan sekretaris?')"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="action" value="remove_signature"><button class="remove-signature-button" type="submit">Hapus gambar</button></form><?php endif; ?>
								</div>
								<p class="settings-note">Laporan menggunakan tanda tangan sekretaris saja. Nama wali kelas tetap dicatat sebagai identitas kelas.</p>
							</div>
						</section>
					</div>
					<div class="settings-column">
						<section class="settings-panel">
							<div class="section-heading"><div><p class="eyebrow">HAK AKSES</p><h2>Akun pengguna</h2></div><span class="section-index">SEKRETARIS / WALI KELAS</span></div>
							<form method="post" class="settings-form compact-settings-form">
								<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="action" value="add_user">
								<div class="settings-fields single-column-fields">
									<label><span>Nama pengguna</span><input name="display_name" maxlength="150" required></label>
									<label><span>Email login</span><input type="email" name="email" maxlength="100" required></label>
									<label><span>Password awal <small>minimal 8 karakter</small></span><input type="password" name="password" minlength="8" autocomplete="new-password" required></label>
									<label><span>Role</span><select name="role"><option value="sekretaris">Sekretaris kelas</option><option value="wali_kelas">Wali kelas / Guru</option></select></label>
								</div>
								<div class="settings-form-footer"><span>Wali kelas hanya dapat melihat dashboard dan laporan.</span><button class="primary-button" type="submit">Tambah akun <span aria-hidden="true">+</span></button></div>
							</form>
							<div class="user-list">
								<?php while ($user = mysqli_fetch_assoc($users)): ?>
								<div class="user-row"><span class="user-row-avatar"><?php echo htmlspecialchars(strtoupper(substr($user['display_name'], 0, 1)), ENT_QUOTES, 'UTF-8'); ?></span><span class="user-row-info"><strong><?php echo htmlspecialchars($user['display_name'], ENT_QUOTES, 'UTF-8'); ?></strong><small><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo $user['role'] === 'wali_kelas' ? 'Wali kelas / Guru' : 'Sekretaris kelas'; ?></small></span><?php if ($user['email'] !== $email_sekretaris): ?><form method="post" onsubmit="return confirm('Hapus akun ini?')"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="action" value="remove_user"><input type="hidden" name="user_id" value="<?php echo (int) $user['id']; ?>"><button class="remove-user-button" type="submit" aria-label="Hapus akun <?php echo htmlspecialchars($user['display_name'], ENT_QUOTES, 'UTF-8'); ?>">Hapus</button></form><?php else: ?><span class="current-user-label">Anda</span><?php endif; ?></div>
								<?php endwhile; ?>
							</div>
						</section>
						<section class="settings-panel reset-panel">
							<div class="section-heading"><div><p class="eyebrow">PINDAH SEMESTER</p><h2>Reset riwayat absensi</h2></div></div>
							<div class="reset-copy"><p>Ada <strong><?php echo $attendance_total; ?></strong> catatan absensi. Proses ini menghapus semua riwayat kehadiran, tetapi tidak menghapus data siswa atau akun.</p><a href="data_siswa.php">Kelola data master siswa <span aria-hidden="true">↗</span></a></div>
							<form method="post" class="reset-form" onsubmit="return confirm('Ini akan menghapus seluruh riwayat absensi. Lanjutkan?')"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="action" value="reset_attendance"><label><span>Ketik HAPUS RIWAYAT untuk konfirmasi</span><input name="reset_confirmation" autocomplete="off" required></label><button class="danger-button" type="submit">Hapus riwayat</button></form>
						</section>
					</div>
				</div>
				<footer class="page-footer"><span>ABSENKU <span aria-hidden="true">·</span> PANEL KEHADIRAN</span><span><?php echo date('Y'); ?></span></footer>
			</main>
		</div>
	</div>
	<div class="sidebar-scrim" id="sidebarScrim"></div>
	<script src="assets/js/halaman.js"></script>
	<script src="assets/js/settings.js"></script>
</body>
</html>

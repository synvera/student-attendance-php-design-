<?php
function app_settings_initialize($koneksi)
{
    date_default_timezone_set('Asia/Jakarta');
    mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS app_settings (
        id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
        class_name VARCHAR(100) NOT NULL DEFAULT 'XII RPL',
        homeroom_teacher VARCHAR(150) NOT NULL DEFAULT '',
        homeroom_nip VARCHAR(40) NOT NULL DEFAULT '',
        academic_year VARCHAR(20) NOT NULL DEFAULT '',
        semester VARCHAR(20) NOT NULL DEFAULT 'Ganjil',
        attendance_cutoff TIME NOT NULL DEFAULT '07:15:00',
        principal_name VARCHAR(150) NOT NULL DEFAULT '',
        principal_nip VARCHAR(40) NOT NULL DEFAULT '',
        secretary_nisn VARCHAR(20) NOT NULL DEFAULT '',
        secretary_signature_path VARCHAR(255) NOT NULL DEFAULT '',
        attendance_statuses TEXT NOT NULL,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS app_users (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) NOT NULL UNIQUE,
        display_name VARCHAR(150) NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('sekretaris', 'wali_kelas') NOT NULL DEFAULT 'sekretaris',
        legacy_code VARCHAR(100) DEFAULT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $homeroom_nip_column = mysqli_query($koneksi, "SHOW COLUMNS FROM app_settings LIKE 'homeroom_nip'");
    if ($homeroom_nip_column && mysqli_num_rows($homeroom_nip_column) === 0) {
        mysqli_query($koneksi, "ALTER TABLE app_settings ADD homeroom_nip VARCHAR(40) NOT NULL DEFAULT '' AFTER homeroom_teacher");
    }
    if ($homeroom_nip_column) mysqli_free_result($homeroom_nip_column);

    $secretary_nisn_column = mysqli_query($koneksi, "SHOW COLUMNS FROM app_settings LIKE 'secretary_nisn'");
    if ($secretary_nisn_column && mysqli_num_rows($secretary_nisn_column) === 0) {
        mysqli_query($koneksi, "ALTER TABLE app_settings ADD secretary_nisn VARCHAR(20) NOT NULL DEFAULT '' AFTER principal_nip");
    }
    if ($secretary_nisn_column) mysqli_free_result($secretary_nisn_column);

    $signature_path_column = mysqli_query($koneksi, "SHOW COLUMNS FROM app_settings LIKE 'secretary_signature_path'");
    if ($signature_path_column && mysqli_num_rows($signature_path_column) === 0) {
        mysqli_query($koneksi, "ALTER TABLE app_settings ADD secretary_signature_path VARCHAR(255) NOT NULL DEFAULT '' AFTER secretary_nisn");
    }
    if ($signature_path_column) mysqli_free_result($signature_path_column);

    $default_statuses = json_encode(['Hadir', 'Sakit', 'Izin', 'Alpa', 'Terlambat'], JSON_UNESCAPED_UNICODE);
    $default_year = date('n') >= 7 ? date('Y') . '/' . (date('Y') + 1) : (date('Y') - 1) . '/' . date('Y');
    $insert_settings = mysqli_prepare($koneksi, 'INSERT IGNORE INTO app_settings (id, academic_year, attendance_statuses) VALUES (1, ?, ?)');
    mysqli_stmt_bind_param($insert_settings, 'ss', $default_year, $default_statuses);
    mysqli_stmt_execute($insert_settings);
    mysqli_stmt_close($insert_settings);

    $saved_statuses = mysqli_query($koneksi, 'SELECT attendance_statuses FROM app_settings WHERE id = 1');
    if ($saved_statuses) {
        $saved_row = mysqli_fetch_assoc($saved_statuses);
        $configured_statuses = json_decode($saved_row['attendance_statuses'] ?? '', true);
        if ($configured_statuses === ['Hadir', 'Sakit', 'Izin', 'Alpa']) {
            $configured_statuses[] = 'Terlambat';
            $upgraded_statuses = json_encode($configured_statuses, JSON_UNESCAPED_UNICODE);
            $upgrade = mysqli_prepare($koneksi, 'UPDATE app_settings SET attendance_statuses = ? WHERE id = 1');
            mysqli_stmt_bind_param($upgrade, 's', $upgraded_statuses);
            mysqli_stmt_execute($upgrade);
            mysqli_stmt_close($upgrade);
        }
        mysqli_free_result($saved_statuses);
    }

    $columns = mysqli_query($koneksi, "SHOW COLUMNS FROM tabel_absensi LIKE 'status'");
    if ($columns && ($column = mysqli_fetch_assoc($columns)) && stripos($column['Type'], 'enum(') === 0) {
        mysqli_query($koneksi, "ALTER TABLE tabel_absensi MODIFY status VARCHAR(40) NOT NULL");
    }

    $user_count = mysqli_query($koneksi, 'SELECT COUNT(*) AS total FROM app_users');
    $users_empty = $user_count && (int) mysqli_fetch_assoc($user_count)['total'] === 0;
    if ($user_count) mysqli_free_result($user_count);
    $legacy_accounts = $users_empty ? mysqli_query($koneksi, 'SELECT email, password, kode_khusus FROM tabel_sekretaris') : false;
    if ($legacy_accounts) {
        while ($account = mysqli_fetch_assoc($legacy_accounts)) {
            $email = $account['email'];
            $name = strstr($email, '@', true) ?: $email;
            $hash = password_hash($account['password'], PASSWORD_DEFAULT);
            $legacy_code = $account['kode_khusus'];
            $role = 'sekretaris';
            $insert_user = mysqli_prepare($koneksi, 'INSERT IGNORE INTO app_users (email, display_name, password_hash, role, legacy_code) VALUES (?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($insert_user, 'sssss', $email, $name, $hash, $role, $legacy_code);
            mysqli_stmt_execute($insert_user);
            mysqli_stmt_close($insert_user);
        }
        mysqli_free_result($legacy_accounts);
    }
}

function app_settings_get($koneksi)
{
    $result = mysqli_query($koneksi, 'SELECT * FROM app_settings WHERE id = 1');
    $settings = mysqli_fetch_assoc($result);
    $settings['attendance_statuses'] = json_decode($settings['attendance_statuses'], true) ?: ['Hadir', 'Sakit', 'Izin', 'Alpa'];
    return $settings;
}

function app_require_role($roles)
{
    if (!isset($_SESSION['email_sekretaris'])) {
        header('Location: index.php');
        exit();
    }

    $koneksi = $GLOBALS['koneksi'] ?? null;
    if ($koneksi) {
        $user_check = mysqli_prepare($koneksi, 'SELECT role, display_name FROM app_users WHERE email = ? AND is_active = 1 LIMIT 1');
        mysqli_stmt_bind_param($user_check, 's', $_SESSION['email_sekretaris']);
        mysqli_stmt_execute($user_check);
        $current_user = mysqli_fetch_assoc(mysqli_stmt_get_result($user_check));
        mysqli_stmt_close($user_check);
        if (!$current_user) {
            session_unset();
            session_destroy();
            header('Location: index.php');
            exit();
        }
        $_SESSION['role'] = $current_user['role'];
        $_SESSION['display_name'] = $current_user['display_name'];
    }

    if (!in_array($_SESSION['role'] ?? 'sekretaris', (array) $roles, true)) {
        header('Location: dashboard.php');
        exit();
    }
}

function app_csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function app_verify_csrf()
{
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}
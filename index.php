<?php
session_start(); //fungsi session adalah untuk mengingat bahwa akun ini sudah login sebelumnya
include 'config/koneksi.php'; //untuk mengambil data koneksi dari file koneksi.php
require_once 'config/app_settings.php';
app_settings_initialize($koneksi);

if (isset($_SESSION['email_sekretaris']) || app_restore_remembered_session($koneksi)) {
    header("Location: dashboard.php");
    exit();
}

//buat jika terjadi error

$error = '';

//kita tulis logika disini saja, tanpa membuat proses-login.php 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //disini fungsinya untuk mengambil hasil inputan yang ada di input 
    $email_input = mysqli_real_escape_string($koneksi, $_POST['email']);
    $pw_input = $_POST['password'];
    $kodek_input = $_POST['kode'] ?? '';
    $login = mysqli_prepare($koneksi, 'SELECT id, email, display_name, password_hash, role, legacy_code FROM app_users WHERE email = ? AND is_active = 1 LIMIT 1');
    mysqli_stmt_bind_param($login, 's', $email_input);
    mysqli_stmt_execute($login);
    $akun = mysqli_fetch_assoc(mysqli_stmt_get_result($login));
    mysqli_stmt_close($login);

    $password_valid = $akun && password_verify($pw_input, $akun['password_hash']);
    $code_valid = $akun && ($akun['legacy_code'] === null || hash_equals($akun['legacy_code'], $kodek_input));
    if ($password_valid && $code_valid) {
        session_regenerate_id(true);
        $_SESSION['email_sekretaris'] = $akun['email'];
        $_SESSION['display_name'] = $akun['display_name'];
        $_SESSION['role'] = $akun['role'];
        if (isset($_POST['remember_me']) && $_POST['remember_me'] === '1') {
            app_remember_user($koneksi, (int) $akun['id']);
        } else {
            app_revoke_remember_token($koneksi);
        }
        header("Location: dashboard.php");
        exit();
    } else {
        //jika tidak ada, maka muncul peringatan
        $error = "Akses ditolak! Email atau Password atau kode khusus anda salah";
    }
}
?>

<!-- //lanjut kita buat halamannya ygy -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AbsenKu | Login Page</title>
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>
<div class="login-page">
    <div class="login-img">
        <img src="assets/img/sekretaris.png" alt="">
    </div>
    <form method="post">
        <h2>Portal Sekretaris</h2>
        <p>Login terlebih dahulu untuk menginput kehadiran siswa/i didalam kelas</p>
        <label for="email">Email</label> <br>
        <input type="email" name="email" required> <br> <br>
        <label for="password">Password</label> <br>
        <input type="password" name="password" required> <br> <br>
        <label for="kode">Kode Khusus (akun lama)</label> <br>
        <input type="password" name="kode"> <br> <br>
        <label class="remember-row"><input type="checkbox" name="remember_me" value="1"><span>Ingat saya</span></label>

        <?php if($error != '') { echo "<p>$error</p><br>";} ?>
        <button type="submit">Masuk</button>
    </form>
</div>
</body>
</html>
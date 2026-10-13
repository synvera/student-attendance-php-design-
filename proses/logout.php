<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/app_settings.php';
app_revoke_remember_token($koneksi);
session_unset();
session_destroy();
header("Location: ../index.php");
exit();
?>
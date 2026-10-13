<?php

$host = "localhost";
$uss = "kahfi"; //untuk uss ini adalah variabel, jadi bebas untuk mengisi apa. Namun tujuan nya 1 (menentukan user yang digunakan)
$pw = "534412mkh"; //kebetulan mariadb saya menggunakan password untuk menggunakannya, maka password ikut dimasukan, jika tidak maka cukup '' saja
$db = "absensi_pure_html_php"; //ini adalah nama database yang telah dibuat

$koneksi = mysqli_connect($host, $uss, $pw, $db); //hubungkan semua variable untuk bisa berkomunikasi dengan database yang telah dibuat

if (!$koneksi){
    die("Jaringan terputus, hubungi tim developer untuk memperbaikinya");
}

//ini adalah kondisi jika, jika $koneksi tidak terhubung dengan baik maka akan memunculkan pesan 
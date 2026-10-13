<?php
//memulai session untuk mengenali sesi yang sedang berjalan
session_start();
//mengkosongkan data session saja yang tersimpan
session_unset();
//menghapus/menghancurkan session secara keseluruhan
session_destroy();
//setelah selesai, maka diarah kan ke login page 
header("Location: ../index.php");
exit();
?>
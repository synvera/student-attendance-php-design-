<div align="center">

### 🎵 SPOTIFY · DEVELOPER SESSION
**TOP TRACKS** yang menemani developer pertama merangkai Tahap awal AbsensiKu

<a href="https://open.spotify.com/search/About%20You"><img src="https://img.shields.io/badge/01%20%C2%B7%20About%20You-Cari%20di%20Spotify-1DB954?style=for-the-badge&logo=spotify&logoColor=white" /></a>
<a href="https://open.spotify.com/search/The%20Cure"><img src="https://img.shields.io/badge/02%20%C2%B7%20The%20Cure-Cari%20di%20Spotify-674B59?style=for-the-badge&logo=spotify&logoColor=white" /></a>
<a href="https://open.spotify.com/search/Heaven"><img src="https://img.shields.io/badge/03%20%C2%B7%20Heaven-Cari%20di%20Spotify-535353?style=for-the-badge&logo=spotify&logoColor=white" /></a>
<a href="https://open.spotify.com/search/Abadi"><img src="https://img.shields.io/badge/04%20%C2%B7%20Abadi-Cari%20di%20Spotify-585252?style=for-the-badge&logo=spotify&logoColor=white" /></a>
<a href="https://open.spotify.com/search/Tunggulah%20Aku%20Di%20Jakarta"><img src="https://img.shields.io/badge/05%20%C2%B7%20Tunggulah%20Aku%20Di%20Jakarta-Cari%20di%20Spotify-9F0000?style=for-the-badge&logo=spotify&logoColor=white" /></a>
<a href="https://open.spotify.com/search/Jatuh%20Suka"><img src="https://img.shields.io/badge/06%20%C2%B7%20Jatuh%20Suka-Cari%20di%20Spotify-0020C8?style=for-the-badge&logo=spotify&logoColor=white" /></a>

</div>
# AbsensiKu

Aplikasi absensi siswa berbasis PHP dan MySQL/MariaDB. Aplikasi menyediakan dashboard, pengelolaan data siswa, pencatatan kehadiran, rekap semester, dan pengaturan kelas dalam antarmuka monokrom yang responsif.

> **Status proyek:** versi awal/prototipe. Baca bagian [Catatan untuk tim](#catatan-untuk-tim-sebelum-digunakan) sebelum menyiapkan lingkungan bersama atau memakai data nyata.

> **Disclaimer:** Proyek ini merupakan versi pertama yang masih dalam tahap awal dan belum melalui revisi menyeluruh. Fitur, alur kerja, dan tampilan yang ada disediakan sebagai bahan diskusi bersama tim untuk evaluasi dan pengembangan selanjutnya, bukan sebagai versi final atau siap produksi.

## Daftar Isi

- [Fitur dan alur kerja](#fitur-dan-alur-kerja)
- [Screenshot halaman](#screenshot-halaman)
- [Teknologi dan struktur file](#teknologi-dan-struktur-file)
- [Menjalankan secara lokal](#menjalankan-secara-lokal)
- [Panduan penggunaan](#panduan-penggunaan)
- [Catatan untuk tim sebelum digunakan](#catatan-untuk-tim-sebelum-digunakan)

## Fitur dan alur kerja

1. **Login multi-role** melalui `index.php`. Sekretaris mengelola absensi dan data; wali kelas hanya dapat melihat dashboard dan laporan. Akun sekretaris lama tetap dapat login dengan kode khususnya.
2. **Identitas/periode kelas** di `settings.php`: atur kelas, wali kelas, sekretaris, NIP, tahun ajaran, semester, dan daftar status yang berlaku.
3. **Batas absensi** di `settings.php`: siswa tanpa catatan setelah tenggat otomatis diberi Alpa. Sekretaris dapat mengoreksi catatan otomatis; pilihan Hadir setelah tenggat disimpan sebagai Terlambat jika status itu diaktifkan.
4. **Data siswa** di `data_siswa.php`: tambah, ubah NIS/nama, cari, atau hapus siswa beserta riwayat absensinya.
5. **Laporan semester** di `laporan_siswa.php`: rekap dibatasi ke tahun ajaran dan semester aktif, dapat diunduh sebagai CSV yang bisa dibuka di Excel, atau dicetak/disimpan sebagai PDF dengan tanda tangan wali kelas dan sekretaris.
6. **Pindah semester** di `settings.php`: reset hanya menghapus riwayat absensi, mempertahankan siswa dan akun. Tindakan ini memerlukan frasa konfirmasi `HAPUS RIWAYAT`.

Pada kunjungan pertama setelah pembaruan, aplikasi membuat tabel konfigurasi/akun, memigrasikan akun sekretaris lama, dan memperluas kolom status absensi secara otomatis. Cadangkan database terlebih dahulu sebelum memakai reset riwayat.

Alur kerja yang disarankan: siapkan daftar siswa terlebih dahulu, input absensi, tinjau dashboard, lalu cetak rekap saat diperlukan.

## Screenshot halaman

Beberapa design dari bantuan `Gemini AI` yang bisa memungkinkan untuk menjadi base design/basic design/awal dari segala design yang ingin dikembangkan

# Login page

File: `docs/screenshots/login.png`

![Screenshot halaman login](docs/screenshots/login.png)

### UPDATE TAMPILAN

File: `docs/screenshots/update/login-page-update.png`
![Screenshot halaman login](docs/screenshots/update/login-page-update.png)

# Dashboard

File: `docs/screenshots/dashboard.png`

![Screenshot halaman dashboard](docs/screenshots/dashboard.png)

### UPDATE TAMPILAN
File: `docs/screenshots/update/dashboard-update.png`
![Screenshot halaman login](docs/screenshots/update/dashboard-update.png)
# Input siswa

File: `docs/screenshots/input-siswa.png`

![Screenshot halaman input siswa](docs/screenshots/input-siswa.png)

### UPDATE TAMPILAN
File: `docs/screenshots/update/input-siswa-update.png`
![Screenshot halaman login](docs/screenshots/update/input-siswa-update.png)

# Input absensi

File: `docs/screenshots/input-absensi.png`

![Screenshot halaman input absensi](docs/screenshots/input-absensi.png)
File: `docs/screenshots/update/input-absensi-update.png`
![Screenshot halaman login](docs/screenshots/update/input-absensi-update.png)

# Cetak laporan

File: `docs/screenshots/cetak-laporan.png`

![Screenshot halaman cetak laporan](docs/screenshots/cetak-laporan.png)

### UPDATE TAMPILAN

File: `docs/screenshots/update/laporan-update.png`
![Screenshot halaman login](docs/screenshots/update/laporan-update.png)

# Setting

File: `docs/screenshots/update/setting-page.png`

![Screenshots Page Settings](docs/screenshots/update/setting-page.png)
Untuk tampilan yang konsisten, ambil gambar setelah halaman selesai dimuat dan gunakan ukuran/zoom browser yang serupa untuk setiap screenshot. Pastikan screenshot tidak menampilkan password, kode khusus, atau data pribadi siswa yang tidak boleh dibagikan.

## Teknologi dan struktur file

Teknologi utama yang dipakai pada project saat ini adalah:

- **PHP 8.x** untuk logika aplikasi, autentikasi sesi, pengelolaan data, dan rendering halaman.
- **MySQL/MariaDB** sebagai basis data utama untuk akun pengguna, pengaturan sistem, data siswa, dan riwayat absensi.
- **MySQLi** untuk koneksi database dan query yang aman menggunakan prepared statement.
- **HTML5 + CSS3 + JavaScript vanilla** untuk struktur halaman, tampilan antarmuka, dan interaksi frontend dasar.
- **Custom PHP session auth** untuk login multi-role, pengaturan aplikasi, serta proteksi halaman berdasarkan role pengguna.
- **Asset lokal** seperti CSS, JS, gambar, dan tanda tangan digital yang disimpan di folder `assets/`.

Berikut struktur direktori yang lebih sesuai dengan kondisi proyek saat ini:

```text
.
|-- index.php                     # Halaman login sekretaris / redirect ke dashboard
|-- dashboard.php                 # Dashboard ringkasan absensi dan overview hari ini
|-- data_siswa.php                # Manajemen data siswa
|-- input_absen.php               # Form input absensi harian
|-- laporan_siswa.php             # Rekap laporan dan export/cetak laporan
|-- settings.php                  # Pengaturan aplikasi, user, kelas, status absensi, tanda tangan
|-- readme.md                    # Dokumentasi proyek
|-- assets/
|   |-- css/
|   |   |-- dashboard.css
|   |   |-- halaman.css
|   |   |-- index.css
|   |   `-- settings.css
|   |-- js/
|   |   |-- dashboard.js
|   |   |-- halaman.js
|   |   `-- settings.js
|   |-- img/
|   |   |-- sekretaris.png
|-- config/
|   |-- koneksi.php              # Konfigurasi koneksi database
|   |-- app_settings.php         # Utility pengaturan aplikasi dan validasi role
|   |-- database.sql             # Skema database dan data awal
|   `-- install-php.md           # Panduan instalasi PHP
|-- docs/
|   |-- screenshots/             # Screenshot fitur aplikasi
|   |-- music/                  # Media pendukung dokumentasi/project
|   `-- update/                  # Versi tampilan update UI
|-- proses/
|   `-- logout.php               # Logout dan penghancuran sesi
`-- .git/                       # Metadata repositori Git
``` 

Catatan: proyek ini masih berbasis aplikasi PHP native tanpa framework (misalnya Laravel atau CodeIgniter), tetapi sudah mulai mengadopsi struktur yang lebih rapi dan lebih siap untuk pengembangan berkelanjutan. Struktur ini lebih sesuai untuk project yang sedang berkembang dibandingkan pendekatan single-file atau layout yang terlalu sederhana.

## Menjalankan secara lokal

### Prasyarat

- PHP CLI terpasang. Tim disarankan memakai PHP 8.x dengan versi minor yang sama agar lingkungan pengembangan konsisten; proyek ini belum memiliki matriks versi PHP yang diuji secara resmi.
- Ekstensi `mysqli` aktif pada PHP yang digunakan oleh server web.
- MySQL atau MariaDB berjalan.
- Browser modern.

Sebelum memulai, buka terminal di komputer masing-masing dan cek PHP:

```bash
php -v
php --ri mysqli
```

![Screenshots cek php](docs/screenshots/cek-php-underline.png)
Perintah pertama menampilkan versi PHP CLI, sedangkan perintah kedua memastikan ekstensi `mysqli` tersedia. Jika perintah `php` tidak ditemukan, instal PHP CLI sesuai sistem operasi atau gunakan paket pengembangan lokal seperti XAMPP/Laragon, lalu buka terminal baru dan ulangi pemeriksaan. Jika `mysqli` tidak ditemukan, aktifkan atau instal ekstensi tersebut untuk versi PHP yang dipakai, kemudian cek kembali.

Pastikan juga server web memakai instalasi PHP yang sama dengan hasil `php -v`; PHP CLI dan PHP yang terhubung ke Apache dapat memiliki versi atau ekstensi berbeda. Belum ada skrip Composer atau pengelola dependensi PHP yang harus dijalankan pada tahap proyek ini.

### Nyalakan MySQL dengan Laragon

1. Buka Laragon.
2. Pilih **Start All** untuk menyalakan layanan lokal. Jika hanya perlu database, gunakan menu MySQL untuk memulai layanan MySQL/MariaDB saja; nama menu dapat berbeda antarversi Laragon.
3. Pastikan indikator MySQL menunjukkan layanan berjalan. Jika gagal, periksa pesan Laragon dan pastikan port MySQL (umumnya `3306`) tidak sedang dipakai layanan lain.
4. Buka **Menu > Laragon > Terminal** agar perintah MySQL menggunakan program dan konfigurasi yang disediakan Laragon.

### Buat database dengan Laragon dan phpMyAdmin (disarankan)

Metode yang disarankan untuk tim adalah menyalakan MySQL dari Laragon, lalu menjalankan SQL melalui phpMyAdmin. Gunakan isi `config/database.sql` sebagai sumber skema dan data awal. File tersebut berisi perintah untuk membuat database `absensi_pure_html_php`, memilih database itu, membuat tabel sekretaris/siswa/absensi, dan memasukkan akun contoh. Jadi, database tidak perlu dibuat manual terlebih dahulu.

Sebelum menjalankannya, buka `config/database.sql` dan koreksi dua masalah yang ada saat ini: ubah komentar `//` menjadi komentar SQL yang valid (misalnya `-- komentar`) dan ubah kolom `pasword` pada perintah `INSERT` menjadi `password`. Sampai dua hal ini dibetulkan, file belum bisa dijalankan dengan sukses. Pastikan juga nilai akun contoh hanya digunakan untuk pengembangan lokal.

Ikuti langkah berikut:

1. Di Laragon, pilih **Start All** dan pastikan MySQL/MariaDB berjalan.
2. Buka phpMyAdmin melalui menu Laragon (**Menu > MySQL > phpMyAdmin**, nama menu bisa berbeda antarversi) atau kunjungi `http://localhost/phpmyadmin` jika phpMyAdmin sudah tersedia.
3. Login menggunakan akun MySQL lokal, biasanya `root`; pada instalasi Laragon tertentu password-nya kosong.
4. Buka tab **SQL**. Tidak perlu membuat atau memilih database terlebih dahulu karena file SQL sudah berisi perintah `CREATE DATABASE` dan `USE`.
5. Buka `config/database.sql` di editor, salin seluruh isinya setelah koreksi di atas, lalu tempelkan ke kolom query pada tab **SQL**.
6. Klik **Go/Kirim** untuk menjalankan query. Pastikan phpMyAdmin menampilkan pesan sukses dan database `absensi_pure_html_php` muncul pada daftar database.

Skrip membuat database `absensi_pure_html_php`, tabel `tabel_sekretaris`, `tabel_siswa`, dan `tabel_absensi`, serta satu akun sekretaris contoh. Jalankan pada database baru satu kali; skrip belum dirancang untuk diimpor berulang kali.

### Alternatif: jalankan melalui MySQL CLI

Jika tidak menggunakan phpMyAdmin, buka **Menu > Laragon > Terminal** dan periksa klien MySQL:

```bash
mysql --version
mysql -u root -p
```

Masukkan password MySQL saat diminta. Jika akun `root` Laragon tidak menggunakan password, tekan Enter pada prompt password. Untuk menjalankan file yang sama, buka terminal sistem operasi di direktori utama proyek, yaitu direktori yang berisi folder `config/`:

```bash
mysql -u root -p < config/database.sql
```

Masukkan password MySQL saat diminta. Jika akun `root` Laragon tidak menggunakan password, gunakan perintah tanpa `-p`:

```bash
mysql -u root < config/database.sql
```

Alternatifnya, setelah masuk ke prompt MySQL, jalankan file yang sama dengan perintah `SOURCE` dan path absolut proyek:

```sql
SOURCE C:/path/ke/proyek/config/database.sql;
```

Pada Windows, gunakan garis miring `/` pada path seperti contoh. Pada Linux/macOS, gunakan path absolut yang sesuai, misalnya `SOURCE /home/user/proyek/config/database.sql;`. Jalankan proses ini satu kali pada database baru; skrip belum dirancang untuk diimpor berulang kali.

Terakhir, sesuaikan host, user database, password database, dan nama database di `config/koneksi.php` dengan konfigurasi Laragon. Jangan menaruh kredensial pribadi pada repositori bersama. Untuk memastikan PHP tersambung ke database, jalankan aplikasi dan akses halaman yang membaca database.

Akun contoh yang tercantum di skrip SQL adalah `sekretaris@gmail.com`; password contoh dan kode khusus hanya untuk pengembangan lokal. Ganti sebelum aplikasi dipakai bersama, dan jangan gunakan kredensial contoh untuk data nyata.

### Jalankan server PHP

Dari direktori utama proyek:

```bash
php -S localhost:8000
```

Buka <a href="http://localhost:8000" target="_blank">http://localhost:8000</a> di browser. Halaman login aplikasi adalah `index.php`.

## Panduan penggunaan

### 1. Masuk sebagai sekretaris

Buka `index.php`, lalu isi email, password, dan kode khusus yang cocok dengan data pada tabel `tabel_sekretaris`. Jika berhasil, aplikasi mengarahkan pengguna ke dashboard.

### 2. Tambah atau kelola siswa

Buka **Input data siswa**, isi NIS dan nama lengkap, lalu pilih **Tambah Siswa**. Daftar di bawah formulir menampilkan siswa yang tersimpan. Gunakan **Hapus** hanya setelah memastikan pilihan siswa benar; tindakan ini menghapus data siswa dari database.

### 3. Catat kehadiran

Buka **Input Absensi**, tentukan tanggal dan pilih satu status untuk setiap siswa yang belum tercatat. Keterangan seperti alasan izin atau informasi surat dokter bersifat opsional. Pilih **Simpan Data Absensi** setelah semua pilihan siap.

Pada implementasi saat ini, satu siswa yang sudah memiliki catatan untuk tanggal yang dibaca halaman tidak dapat dipilih ulang dari formulir. Periksa tanggal dan data sebelum menyimpan.

### 4. Tinjau dashboard dan cetak rekap

Dashboard menampilkan catatan untuk tanggal hari ini. Di halaman **Laporan Absensi**, tinjau jumlah Sakit, Izin, dan Alpa per siswa. Pilih **Download Rekap**, lalu pilih printer atau **Save as PDF** pada dialog browser. Tautan dan tombol pada halaman tidak ikut tercetak.

## Catatan untuk tim sebelum digunakan

Proyek ini adalah versi pertama dengan fondasi dasar yang telah dibuat oleh developer pertama: login, pengelolaan data siswa, pencatatan absensi, dashboard, dan rekap sederhana. Tugas tim berikutnya adalah meninjau fondasi tersebut, menyepakati arah pengembangan, lalu menambahkan perbaikan atau fitur secara bertahap. Usulan di bawah merupakan bahan diskusi, bukan daftar pekerjaan yang wajib langsung dikerjakan.

### Agenda diskusi

1. **Arsitektur database**
	- Apakah struktur tabel siswa, sekretaris, dan absensi sudah sesuai dengan alur kerja yang disepakati?
	- Aturan data apa yang perlu ditetapkan, misalnya NIS harus unik, satu siswa hanya memiliki satu catatan per tanggal, serta hubungan dan penghapusan data absensi ketika data siswa berubah?
	- Apakah ke depannya perlu mendukung kelas/rombel, tahun ajaran, lebih dari satu petugas, atau riwayat perubahan absensi?
	- Sebelum database digunakan bersama, tinjau dan uji `config/database.sql`. Saat ini masih ada komentar `//` yang bukan sintaks komentar SQL dan typo `pasword` pada perintah `INSERT`; keduanya perlu dibereskan. Pertimbangkan juga batasan unik dan aturan integritas data setelah kebutuhan disepakati.

2. **Arah desain dan struktur website**
	- Desain visual/CSS belum dimulai. Pengembangan desain sebaiknya menunggu persetujuan guru agar warna, tata letak, identitas, dan gaya tampilan sesuai arahan.
	- Sambil menunggu persetujuan, tim dapat membahas struktur navigasi, urutan alur kerja, tampilan tabel/formulir, serta kebutuhan penggunaan di desktop dan ponsel tanpa menetapkan desain final.
	- Setelah desain disetujui, sepakati komponen atau aturan tampilan bersama agar halaman login, dashboard, formulir, dan laporan terasa konsisten.

3. **Fitur yang mungkin ditambahkan**
	- Penyuntingan data siswa dan koreksi absensi dengan konfirmasi serta riwayat perubahan.
	- Filter laporan berdasarkan tanggal, kelas, atau rentang waktu, dan pilihan ekspor yang dibutuhkan tim.
	- Ringkasan jumlah hadir/sakit/izin/alpa dan indikator siswa yang belum diabsen.
	- Validasi input, pembatasan akses semua halaman internal, serta pengelolaan kredensial yang lebih aman.
	- Fitur lain berdasarkan masukan guru dan kebutuhan pengguna.

	Prioritaskan fitur berdasarkan manfaat dan waktu yang tersedia. Implementasikan hanya jika disepakati tim dan memungkinkan dari sisi kebutuhan, keamanan, serta kemampuan teknis; tidak semua usulan harus masuk ke versi berikutnya.

4. **Uji alur yang sudah ada**
	- Uji pembuatan database pada database baru dan pastikan koneksi pada `config/koneksi.php` sesuai lingkungan lokal.
	- Periksa alur login/logout dan pastikan semua halaman internal menerapkan aturan akses yang disepakati.
	- Uji absensi untuk tanggal hari ini maupun tanggal lain; saat ini formulir mengirim tanggal pilihan, tetapi daftar statusnya masih membaca tanggal hari ini.
	- Simulasikan alur dari menambah siswa, mencatat absensi, meninjau dashboard, hingga mencetak laporan. Catat perilaku yang membingungkan atau hasil yang tidak sesuai.

### Cara kerja tim

Bahas dan sepakati kebutuhan bersama sebelum mengubah struktur database atau memulai desain final. Catat keputusan, penanggung jawab, dan hal yang masih perlu persetujuan guru. Setelah keputusan jelas, kerjakan perubahan kecil secara bertahap, uji alur terkait, lalu perbarui dokumentasi. Jaga komunikasi agar asumsi yang berbeda tidak berkembang menjadi kesalahpahaman.

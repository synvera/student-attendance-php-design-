# Panduan Instalasi PHP

> Dokumentasi ini berisi langkah-langkah instalasi PHP pada sistem operasi utama, yaitu Windows, macOS, dan Linux (Ubuntu/Debian). Pilih metode yang paling sesuai dengan kebutuhan Anda.

## Daftar Isi

- [Prasyarat](#prasyarat)
- [Instalasi di Windows](#instalasi-di-windows)
  - [Metode A: XAMPP](#metode-a-menggunakan-xampp-disarankan)
  - [Metode B: Instalasi Manual](#metode-b-instalasi-manual)
- [Instalasi di macOS](#instalasi-di-macos)
- [Instalasi di Linux (Ubuntu/Debian)](#instalasi-di-linux-ubuntu--debian)
- [Verifikasi Instalasi](#verifikasi-instalasi)
- [Membuat File PHP Pertama](#membuat-file-php-pertama)
- [Tips Tambahan](#tips-tambahan)

---

## Prasyarat

Sebelum memulai, pastikan Anda sudah memiliki:

- Akses ke perangkat yang akan diinstal PHP
- Koneksi internet untuk mengunduh paket installer
- Hak akses administrator atau sudo jika diperlukan
- Versi PHP yang sesuai kebutuhan, disarankan PHP 8.x

---

## Instalasi di Windows

Untuk pengguna Windows, ada dua cara utama untuk menginstal PHP:

1. Menggunakan paket all-in-one seperti XAMPP (disarankan untuk pemula)
2. Instalasi manual dengan menyiapkan PHP secara terpisah

### Metode A: Menggunakan XAMPP (Disarankan)

XAMPP adalah paket yang sudah mencakup Apache, MySQL/MariaDB, dan PHP dalam satu instalasi.

#### Langkah-langkah

1. Unduh installer XAMPP dari situs resmi:
   https://www.apachefriends.org/download.html
2. Pilih versi XAMPP yang sesuai kebutuhan Anda, dan disarankan menggunakan versi PHP terbaru (misalnya PHP 8.x).
3. Jalankan file installer yang sudah diunduh, contohnya:
   `xampp-windows-x64-8.2.4-0-VS16-installer.exe`
4. Ikuti instruksi di layar dan klik `Next` secara berurutan.
5. Biasakan menggunakan pengaturan default agar proses lebih cepat dan minim error.
6. Pilih lokasi instalasi, biasanya di `C:\xampp`.
7. Selesaikan proses instalasi.
8. Buka `XAMPP Control Panel` dan klik `Start` pada modul `Apache` untuk menjalankan server lokal.

> XAMPP sangat cocok untuk pengguna yang ingin langsung menjalankan PHP dan web server tanpa konfigurasi rumit.

### Metode B: Instalasi Manual

Metode ini cocok jika Anda hanya membutuhkan PHP CLI tanpa web server Apache.

#### Langkah-langkah

1. Buka halaman unduhan PHP untuk Windows:
   https://windows.php.net/download/
2. Unduh versi `Thread Safe` dalam format ZIP. Untuk Windows 64-bit, pilih versi `x64`.
3. Ekstrak file ZIP ke folder baru, misalnya:
   `C:\php`
4. Tambahkan folder PHP ke dalam `Environment Variables` agar perintah `php` dapat dikenali dari Command Prompt.
5. Caranya:
   - Tekan tombol Windows
   - Ketik `Environment Variables`
   - Pilih `Edit the system environment variables`
   - Klik `Environment Variables...`
   - Pada bagian `System variables`, cari variabel `Path`
   - Klik `Edit...`
   - Klik `New`
   - Masukkan `C:\php`
   - Klik `OK` pada semua jendela

---

## Instalasi di macOS

Cara paling mudah untuk menginstal PHP di macOS adalah menggunakan Homebrew.

### Langkah-langkah

1. Buka aplikasi Terminal.
2. Jika Homebrew belum terinstal, jalankan perintah berikut:

```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

3. Setelah Homebrew terinstal, perbarui database paket:

```bash
brew update
```

4. Instal PHP:

```bash
brew install php
```

5. Tunggu proses selesai. Homebrew akan mengunduh dan mengatur dependensi secara otomatis.

> Homebrew sangat direkomendasikan karena proses instalasi lebih cepat dan lebih mudah dikelola.

---

## Instalasi di Linux (Ubuntu / Debian)

Pada sistem berbasis Debian/Ubuntu, instalasi PHP dapat dilakukan dengan APT.

### Langkah-langkah

1. Buka Terminal.
2. Perbarui daftar paket sistem:

```bash
sudo apt update
```

3. Instal PHP dan modul umum yang sering digunakan:

```bash
sudo apt install php libapache2-mod-php php-mysql php-cli php-common php-json php-opcache php-readline
```

4. Tekan `Y` lalu `Enter` jika terminal meminta konfirmasi penggunaan ruang disk.
5. Tunggu proses instalasi selesai.

---

## Verifikasi Instalasi

Setelah instalasi selesai, lakukan verifikasi agar PHP benar-benar terpasang dengan baik.

### Perintah verifikasi

Buka Command Prompt di Windows atau Terminal di macOS/Linux, lalu jalankan:

```bash
php -v
```

### Output yang diharapkan

Jika instalasi berhasil, Anda akan melihat informasi versi PHP, seperti:

```bash
PHP 8.2.4 (cli) (built: Mar 14 2023 16:03:22) (NTS Visual C++ 2019 x64)
Copyright (c) The PHP Group
Zend Engine v4.2.4, Copyright (c) Zend Technologies
```

Jika perintah tersebut berhasil dijalankan, berarti PHP sudah terinstal dan siap digunakan.

---

## Membuat File PHP Pertama

Setelah PHP berhasil terinstal, Anda dapat mulai membuat project PHP pertama.

### Langkah-langkah

1. Buat file baru dengan nama `index.php`
2. Isi file tersebut dengan kode berikut:

```php
<?php
echo "Halo, PHP sudah berjalan!";
?>
```

### Jika menggunakan XAMPP

Simpan file di folder:

```text
C:\xampp\htdocs\
```

Lalu buka browser dan akses:

```text
http://localhost/index.php
```

### Jika menggunakan CLI

Buka terminal di lokasi file tersebut, lalu jalankan:

```bash
php index.php
```

---

## Tips Tambahan

- Gunakan versi PHP 8.x agar kompatibel dengan framework modern.
- Untuk pengembangan web, XAMPP atau Laravel Valet sangat membantu.
- Selalu jalankan `php -v` setelah instalasi agar memastikan konfigurasi berjalan dengan baik.
- Jika Anda mengalami error, pastikan `PATH` sudah benar dan PHP tersedia di environment sistem.

---

## Kesimpulan

Instalasi PHP tergantung pada sistem operasi yang digunakan, namun prinsipnya sama: unduh, instal, konfigurasikan, lalu verifikasi dengan `php -v`. Setelah itu, Anda dapat mulai mengembangkan aplikasi PHP dengan lebih percaya diri.

Jika Anda ingin, dokumen ini juga dapat dilanjutkan dengan panduan:

- Instalasi MySQL
- Konfigurasi Apache untuk PHP
- Membuat project PHP pertama di XAMPP
- Menjalankan Laravel atau CodeIgniter
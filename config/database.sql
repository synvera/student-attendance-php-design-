CREATE DATABASE absensi_pure_html_php; //buat terlebih dahulu nama/databasenya 
use absensi_pure_html_php; //gunakan databasenya

CREATE TABLE tabel_sekretaris (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    kode_khusus VARCHAR(50) NOT NULL
);
// buat tabel untuk login sekretarisnyaa

INSERT INTO tabel_sekretaris (email, pasword, kode_khusus)
VALUES ('sekretaris@gmail.com', 'sekretaris123', '534412mkh');

//fungsinya untuk mengisi data didalam tabel sekretaris

//kita buat tabel baru untuk siswa dan absennya

CREATE TABLE tabel_siswa (
    id_siswa INT AUTO_INCREMENT PRIMARY KEY,
    nis VARCHAR(20),
    nama_siswa VARCHAR(100) NOT NULL
);

//tambahkan full data dummy siswa kelas untuk testing
INSERT INTO tabel_siswa (nis, nama_siswa) VALUES
('1001', 'Akmal Fahreza'),
('1002', 'Allif Maulana Robbil Izza'),
('1003', 'Ananda Try Anugrah'),
('1004', 'Avichena Al-Khawarizmi'),
('1005', 'El Zhar Al Ghifari'),
('1006', 'Iman Yazi Suhmar Darkun'),
('1007', 'Izzan Afresetya'),
('1008', 'Listyo Shobri Wicaksono'),
('1009', 'Maher Mucktar Balaka'),
('1010', 'Mochammad Khahfi'),
('1011', 'Muhammad Rizky Apriadi'),
('1012', 'Muhammad Fakhri'),
('1013', 'Muhammad Rizq Maulana'),
('1014', 'Muhammad Rossy Fadliyanto'),
('1015', 'Noval Aqiransah Ridho Mustofa'),
('1016', 'Putra Darmawan Budi'),
('1017', 'Raffida Fathiyya Ramadhania'),
('1018', 'Rafi Izhar Rivaldi'),
('1019', 'Regan Ali Ramadhan'),
('1020', 'Reyhan Echa Pratama'),
('1021', 'Rizna Azzahra Fahrudin'),
('1022', 'Tegar Permana Putra');

CREATE TABLE tabel_absensi (
    id_absen INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE NOT NULL,
    id_siswa INT NOT NULL,
    status ENUM('Hadir', 'Sakit', 'Izin', 'Alpa') NOT NULL,
    keterangan VARCHAR(255), -- Opsional, misal surat dokter
    FOREIGN KEY (id_siswa) REFERENCES tabel_siswa(id_siswa)
);
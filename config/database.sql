-- Tinggal copy dibagian sql 

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


INSERT INTO tabel_siswa (nis, nama_siswa) VALUES
('2425103847', 'Akmal Fahreza'),
('2425938471', 'Allif Maulana Robbil Izza'),
('2425482910', 'Ananda Try Anugrah'),
('2425718293', 'Avichena Al-Khawarizmi'),
('2425591028', 'El Zhar Al Ghifari'),
('2425304958', 'Iman Yazi Suhmar Darkun'),
('2425847561', 'Izzan Afresetya'),
('2425192837', 'Maher Mucktar Balaka'),
('2425657483', 'Mochammad Khahfi'),
('2425203948', 'Muhammad Rizky Apriadi'),
('2425839201', 'Muhammad Fakhri'),
('2425475869', 'Muhammad Rizq Maulana'),
('2425910283', 'Muhammad Rossy Fadliyanto'),
('2425384756', 'Noval Aqiransah Ridho Mustofa'),
('2425746581', 'Putra Darmawan Budi'),
('2425182736', 'Raffida Fathiyya Ramadhania'),
('2425501928', 'Rafi Izhar Rivaldi'),
('2425928374', 'Regan Ali Ramadhan'),
('2425610293', 'Reyhan Echa Pratama'),
('2425374859', 'Rizna Azzahra Fahrudin'),
('2425829104', 'Tegar Permana Putra');

CREATE TABLE tabel_absensi (
    id_absen INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE NOT NULL,
    id_siswa INT NOT NULL,
    status ENUM('Hadir', 'Sakit', 'Izin', 'Alpa') NOT NULL,
    keterangan VARCHAR(255), -- Opsional, misal surat dokter
    FOREIGN KEY (id_siswa) REFERENCES tabel_siswa(id_siswa)
);
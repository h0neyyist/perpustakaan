CREATE DATABASE IF NOT EXISTS perpustakaan;
USE perpustakaan;
-- Tabel buku
CREATE TABLE buku (
 id_buku INT PRIMARY KEY AUTO_INCREMENT,
 judul VARCHAR(100) NOT NULL,
 pengarang VARCHAR(100) NOT NULL,
 penerbit VARCHAR(100),
 tahun_terbit YEAR,
 stok INT DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- Tabel anggota
CREATE TABLE anggota (id_anggota INT PRIMARY KEY AUTO_INCREMENT,
 nomor_anggota VARCHAR(20) UNIQUE NOT NULL,
 nama VARCHAR(100) NOT NULL,
 alamat TEXT,
 no_telepon VARCHAR(15),
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- Tabel peminjaman
CREATE TABLE peminjaman (
 id_peminjaman INT PRIMARY KEY AUTO_INCREMENT,
 id_buku INT NOT NULL,
 id_anggota INT NOT NULL,
 tanggal_pinjam DATE NOT NULL,
 tanggal_jatuh_tempo DATE NOT NULL,
 tanggal_kembali DATE NULL,
 status ENUM('dipinjam', 'dikembalikan') DEFAULT 'dipinjam',
 FOREIGN KEY (id_buku) REFERENCES buku(id_buku) ON DELETE CASCADE,
 FOREIGN KEY (id_anggota) REFERENCES anggota(id_anggota) ON DELETE CASCADE
);
-- Sample data
INSERT INTO buku (judul, pengarang, penerbit, tahun_terbit, stok) VALUES
('Pemrograman PHP', 'Andi Wijaya', 'Gramedia', 2023, 5),
('Belajar MySQL', 'Budi Santoso', 'Erlangga', 2022, 3),
('Framework Bootstrap', 'Citra Dewi', 'Informatika', 2024, 4);
INSERT INTO anggota (nomor_anggota, nama, alamat, no_telepon) VALUES
('A001', 'Ahmad Fauzi', 'Jl. Merdeka No.1', '08123456789'),
('A002', 'Budi Raharjo', 'Jl. Sudirman No.2', '08198765432'),
('A003', 'Citra Lestari', 'Jl. Thamrin No.3', '08561234567');
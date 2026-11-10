-- Shelfwise Library - Ekspor Database
-- Ahmad Rafiq Hakim | NPM 2513020081

CREATE DATABASE IF NOT EXISTS db_perpustakaan
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_perpustakaan;

DROP TABLE IF EXISTS buku;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  nama_lengkap VARCHAR(100) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE buku (
  id INT AUTO_INCREMENT PRIMARY KEY,
  judul VARCHAR(150) NOT NULL,
  kategori VARCHAR(50) NOT NULL,
  penulis VARCHAR(100) NOT NULL,
  tahun_terbit YEAR NOT NULL,
  stok INT NOT NULL DEFAULT 0,
  sampul VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Akun default: username "admin" | password "password"
INSERT INTO users (username, password, nama_lengkap) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ahmad Rafiq Hakim');

INSERT INTO buku (judul, kategori, penulis, tahun_terbit, stok) VALUES
('Laskar Pelangi', 'Novel', 'Andrea Hirata', 2005, 3),
('Bumi Manusia', 'Novel', 'Pramoedya Ananta Toer', 1980, 0),
('Pemrograman Web dengan PHP', 'Teknologi', 'Abdul Kadir', 2018, 5),
('Atomic Habits', 'Pengembangan Diri', 'James Clear', 2018, 2);

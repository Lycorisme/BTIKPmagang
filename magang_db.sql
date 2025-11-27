-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 27, 2025 at 03:47 AM
-- Server version: 5.7.39
-- PHP Version: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `magang_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `jurnal`
--

CREATE TABLE `jurnal` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `mentor_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `aktivitas` text,
  `file_penunjang` varchar(255) DEFAULT NULL,
  `feedback` text,
  `nilai` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `jurnal`
--

INSERT INTO `jurnal` (`id`, `user_id`, `mentor_id`, `tanggal`, `aktivitas`, `file_penunjang`, `feedback`, `nilai`) VALUES
(1, 5, 2, '2025-02-01', 'Hari pertama magang. Melakukan setup development environment, install XAMPP, PHP, Composer, dan Laravel. Membaca dokumentasi Laravel dan memahami struktur MVC. Membuat project Laravel pertama dan menjalankan migration database.', NULL, 'Bagus untuk hari pertama. Lanjutkan dengan mempelajari routing dan controller di Laravel.', 85),
(2, 5, 2, '2025-02-02', 'Mempelajari Laravel Routing dan Controller. Membuat beberapa route sederhana dan controller untuk handling request. Mencoba blade templating engine untuk membuat view. Praktik membuat CRUD sederhana untuk data mahasiswa.', NULL, 'Progress sangat baik. Sudah memahami konsep MVC dengan baik. Lanjut ke Eloquent ORM besok.', 88),
(3, 6, 3, '2025-02-15', 'Pengenalan Flutter dan Dart programming. Install Flutter SDK, Android Studio, dan setup emulator. Membuat project Flutter pertama dengan StatelessWidget dan StatefulWidget. Belajar tentang widget tree dan layout di Flutter.', NULL, 'Pemahaman dasar sudah bagus. Coba eksplorasi lebih banyak widget bawaan Flutter.', 82),
(4, 5, 2, '2025-02-03', 'Mempelajari Eloquent ORM untuk database operation. Membuat model, migration, dan seeder. Praktik CRUD menggunakan Eloquent. Memahami relationship (hasMany, belongsTo) antar model. Membuat API endpoint untuk get all data dan get by id.', NULL, NULL, NULL),
(5, 5, 2, '2025-11-23', 'test', 'JURNAL_5_1763908127.jpg', NULL, NULL),
(6, 5, 2, '2025-11-23', 'cfvfdv', 'JURNAL_5_1763920545.jpg', NULL, NULL),
(7, 5, 2, '2025-11-23', 'vfjvkbdfkbvfd', 'JURNAL_5_1763921064.jpg', NULL, NULL),
(8, 5, 2, '2025-11-23', 'sdccsdcdsc', 'JURNAL_5_1763921116.jpg', NULL, NULL),
(9, 5, 2, '2025-11-23', 'csdksdnkcd', 'JURNAL_5_1763921150.pdf', NULL, NULL),
(10, 5, 2, '2025-11-23', 'aaaaaaaaaaa', 'JURNAL_5_1763921180.pdf', NULL, NULL),
(11, 5, 2, '2025-11-23', 'tttttttttttt', 'JURNAL_5_1763921427.jpg', NULL, NULL),
(12, 5, 2, '2025-11-23', 'csdcsdccsdc', 'JURNAL_5_1763922835.jpg', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lamaran`
--

CREATE TABLE `lamaran` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `lowongan_id` int(11) NOT NULL,
  `mentor_id` int(11) NOT NULL,
  `tgl_melamar` date NOT NULL,
  `status` enum('proses','diterima','ditolak') DEFAULT 'proses',
  `file_cv` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `lamaran`
--

INSERT INTO `lamaran` (`id`, `user_id`, `lowongan_id`, `mentor_id`, `tgl_melamar`, `status`, `file_cv`) VALUES
(1, 5, 1, 2, '2025-01-15', 'diterima', 'CV_5_sample.pdf'),
(2, 6, 2, 3, '2025-01-16', 'diterima', 'CV_6_sample.pdf'),
(3, 7, 3, 4, '2025-01-17', 'proses', 'CV_7_sample.pdf'),
(4, 8, 1, 2, '2025-01-18', 'proses', 'CV_8_sample.pdf'),
(5, 9, 4, 2, '2025-01-19', 'ditolak', 'CV_9_sample.pdf'),
(6, 5, 2, 3, '2025-01-20', 'proses', 'CV_5_sample2.pdf'),
(7, 5, 4, 2, '2025-11-17', 'diterima', 'CV_5_1763343813.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `lowongan`
--

CREATE TABLE `lowongan` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text,
  `mentor_id` int(11) NOT NULL,
  `tgl_mulai` date DEFAULT NULL,
  `tgl_selesai` date DEFAULT NULL,
  `status` enum('open','closed') DEFAULT 'open'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `lowongan`
--

INSERT INTO `lowongan` (`id`, `judul`, `deskripsi`, `mentor_id`, `tgl_mulai`, `tgl_selesai`, `status`) VALUES
(1, 'Magang Web Developer Backend', 'Lowongan magang untuk posisi Backend Developer. Mahasiswa akan belajar membuat REST API menggunakan PHP dan Laravel, integrasi database MySQL, dan implementasi authentication & authorization. Durasi magang 3 bulan dengan project real.', 2, '2025-02-01', '2025-05-01', 'open'),
(2, 'Magang Mobile App Developer', 'Kesempatan magang membuat aplikasi mobile menggunakan Flutter. Mahasiswa akan belajar membuat UI/UX responsive, integrasi API, state management, dan publish ke PlayStore. Project: Aplikasi E-Commerce.', 3, '2025-02-15', '2025-05-15', 'open'),
(3, 'Magang Data Analyst', 'Program magang untuk calon data analyst. Akan mempelajari data cleaning, exploratory data analysis, visualisasi data menggunakan Python, dan membuat dashboard interaktif. Tools: Python, Pandas, Matplotlib, Tableau.', 4, '2025-03-01', '2025-06-01', 'open'),
(4, 'Magang Full Stack Developer', 'Magang comprehensive full stack development. Frontend: React.js, Backend: Node.js, Database: MongoDB. Mahasiswa akan membuat aplikasi web lengkap dari scratch hingga deployment.', 2, '2025-02-10', '2025-05-10', 'open'),
(5, 'Magang Machine Learning Engineer', 'Kesempatan langka untuk belajar machine learning. Akan mempelajari supervised & unsupervised learning, neural networks, dan deploy ML model. Project: Sistem rekomendasi produk.', 4, '2025-03-15', '2025-06-15', 'open');

-- --------------------------------------------------------

--
-- Table structure for table `mentors`
--

CREATE TABLE `mentors` (
  `user_id` int(11) NOT NULL,
  `keahlian` varchar(255) NOT NULL,
  `bio` text,
  `status_open` tinyint(1) DEFAULT '1',
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `mentors`
--

INSERT INTO `mentors` (`user_id`, `keahlian`, `bio`, `status_open`, `foto`) VALUES
(2, 'Web Development, PHP, Laravel', 'Berpengalaman 10 tahun di bidang web development. Spesialisasi dalam PHP, Laravel, dan MySQL. Telah membimbing lebih dari 50 mahasiswa magang.', 1, NULL),
(3, 'Mobile Development, Flutter, React Native', 'Expert dalam pengembangan aplikasi mobile cross-platform. Berpengalaman membuat aplikasi untuk iOS dan Android menggunakan Flutter dan React Native.', 1, NULL),
(4, 'Data Science, Machine Learning, Python', 'Ahli di bidang data science dan machine learning. Berpengalaman dalam analisis data, pembuatan model ML, dan implementasi AI.', 1, NULL),
(12, 'test', 'test', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('mahasiswa','mentor','admin') NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Administrator', 'admin@magang.com', 'admin123', 'admin', '2025-11-17 08:36:13'),
(2, 'Dr. Budi Santosoo', 'budi@mentor.com', 'mentor123', 'mentor', '2025-11-17 08:36:13'),
(3, 'Ir. Siti Rahma', 'siti@mentor.com', 'mentor123', 'mentor', '2025-11-17 08:36:13'),
(4, 'Prof. Ahmad Yani', 'ahmad@mentor.com', 'mentor123', 'mentor', '2025-11-17 08:36:13'),
(5, 'Andi Prasetyoo0', 'andi@mahasiswa.com', 'mahasiswa123', 'mahasiswa', '2025-11-17 08:36:14'),
(6, 'Rina Wati', 'rina@mahasiswa.com', 'mahasiswa123', 'mahasiswa', '2025-11-17 08:36:14'),
(7, 'Deni Setiawan', 'deni@mahasiswa.com', 'mahasiswa123', 'mahasiswa', '2025-11-17 08:36:14'),
(8, 'Lina Kusumaa', 'lina@mahasiswa.com', 'mahasiswa123', 'mahasiswa', '2025-11-17 08:36:14'),
(9, 'Roni Hidayat', 'roni@mahasiswa.com', 'mahasiswa123', 'mahasiswa', '2025-11-17 08:36:14'),
(10, 'test', 'test@gmail.com', '44449999', 'mahasiswa', '2025-11-23 10:07:12'),
(12, 'test adminn', 'test@admin', 'test', 'mentor', '2025-11-23 21:24:06');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `jurnal`
--
ALTER TABLE `jurnal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `mentor_id` (`mentor_id`);

--
-- Indexes for table `lamaran`
--
ALTER TABLE `lamaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `lowongan_id` (`lowongan_id`),
  ADD KEY `mentor_id` (`mentor_id`);

--
-- Indexes for table `lowongan`
--
ALTER TABLE `lowongan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mentor_id` (`mentor_id`);

--
-- Indexes for table `mentors`
--
ALTER TABLE `mentors`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `jurnal`
--
ALTER TABLE `jurnal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `lamaran`
--
ALTER TABLE `lamaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `lowongan`
--
ALTER TABLE `lowongan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `jurnal`
--
ALTER TABLE `jurnal`
  ADD CONSTRAINT `jurnal_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `jurnal_ibfk_2` FOREIGN KEY (`mentor_id`) REFERENCES `mentors` (`user_id`);

--
-- Constraints for table `lamaran`
--
ALTER TABLE `lamaran`
  ADD CONSTRAINT `lamaran_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `lamaran_ibfk_2` FOREIGN KEY (`lowongan_id`) REFERENCES `lowongan` (`id`),
  ADD CONSTRAINT `lamaran_ibfk_3` FOREIGN KEY (`mentor_id`) REFERENCES `mentors` (`user_id`);

--
-- Constraints for table `lowongan`
--
ALTER TABLE `lowongan`
  ADD CONSTRAINT `lowongan_ibfk_1` FOREIGN KEY (`mentor_id`) REFERENCES `mentors` (`user_id`);

--
-- Constraints for table `mentors`
--
ALTER TABLE `mentors`
  ADD CONSTRAINT `mentors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

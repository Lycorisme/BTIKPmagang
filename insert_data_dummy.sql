-- Insert Data Dummy untuk Testing
-- Jalankan setelah membuat database dan tabel

USE magang_db;

-- Insert Admin
INSERT INTO users (nama, email, password, role, created_at) VALUES
('Administrator', 'admin@magang.com', 'admin123', 'admin', NOW());

-- Insert Mentor
INSERT INTO users (nama, email, password, role, created_at) VALUES
('Dr. Budi Santoso', 'budi@mentor.com', 'mentor123', 'mentor', NOW()),
('Ir. Siti Rahma', 'siti@mentor.com', 'mentor123', 'mentor', NOW()),
('Prof. Ahmad Yani', 'ahmad@mentor.com', 'mentor123', 'mentor', NOW());

-- Insert data ke tabel mentors
INSERT INTO mentors (user_id, keahlian, bio, status_open, foto) VALUES
(2, 'Web Development, PHP, Laravel', 'Berpengalaman 10 tahun di bidang web development. Spesialisasi dalam PHP, Laravel, dan MySQL. Telah membimbing lebih dari 50 mahasiswa magang.', 1, NULL),
(3, 'Mobile Development, Flutter, React Native', 'Expert dalam pengembangan aplikasi mobile cross-platform. Berpengalaman membuat aplikasi untuk iOS dan Android menggunakan Flutter dan React Native.', 1, NULL),
(4, 'Data Science, Machine Learning, Python', 'Ahli di bidang data science dan machine learning. Berpengalaman dalam analisis data, pembuatan model ML, dan implementasi AI.', 1, NULL);

-- Insert Mahasiswa
INSERT INTO users (nama, email, password, role, created_at) VALUES
('Andi Prasetyo', 'andi@mahasiswa.com', 'mahasiswa123', 'mahasiswa', NOW()),
('Rina Wati', 'rina@mahasiswa.com', 'mahasiswa123', 'mahasiswa', NOW()),
('Deni Setiawan', 'deni@mahasiswa.com', 'mahasiswa123', 'mahasiswa', NOW()),
('Lina Kusuma', 'lina@mahasiswa.com', 'mahasiswa123', 'mahasiswa', NOW()),
('Roni Hidayat', 'roni@mahasiswa.com', 'mahasiswa123', 'mahasiswa', NOW());

-- Insert Lowongan Magang
INSERT INTO lowongan (judul, deskripsi, mentor_id, tgl_mulai, tgl_selesai, status) VALUES
('Magang Web Developer Backend', 
'Lowongan magang untuk posisi Backend Developer. Mahasiswa akan belajar membuat REST API menggunakan PHP dan Laravel, integrasi database MySQL, dan implementasi authentication & authorization. Durasi magang 3 bulan dengan project real.', 
2, '2025-02-01', '2025-05-01', 'open'),

('Magang Mobile App Developer', 
'Kesempatan magang membuat aplikasi mobile menggunakan Flutter. Mahasiswa akan belajar membuat UI/UX responsive, integrasi API, state management, dan publish ke PlayStore. Project: Aplikasi E-Commerce.', 
3, '2025-02-15', '2025-05-15', 'open'),

('Magang Data Analyst', 
'Program magang untuk calon data analyst. Akan mempelajari data cleaning, exploratory data analysis, visualisasi data menggunakan Python, dan membuat dashboard interaktif. Tools: Python, Pandas, Matplotlib, Tableau.', 
4, '2025-03-01', '2025-06-01', 'open'),

('Magang Full Stack Developer', 
'Magang comprehensive full stack development. Frontend: React.js, Backend: Node.js, Database: MongoDB. Mahasiswa akan membuat aplikasi web lengkap dari scratch hingga deployment.', 
2, '2025-02-10', '2025-05-10', 'open'),

('Magang Machine Learning Engineer', 
'Kesempatan langka untuk belajar machine learning. Akan mempelajari supervised & unsupervised learning, neural networks, dan deploy ML model. Project: Sistem rekomendasi produk.', 
4, '2025-03-15', '2025-06-15', 'open');

-- Insert Lamaran (contoh beberapa mahasiswa melamar)
INSERT INTO lamaran (user_id, lowongan_id, mentor_id, tgl_melamar, status, file_cv) VALUES
(5, 1, 2, '2025-01-15', 'diterima', 'CV_5_sample.pdf'),
(6, 2, 3, '2025-01-16', 'diterima', 'CV_6_sample.pdf'),
(7, 3, 4, '2025-01-17', 'proses', 'CV_7_sample.pdf'),
(8, 1, 2, '2025-01-18', 'proses', 'CV_8_sample.pdf'),
(9, 4, 2, '2025-01-19', 'ditolak', 'CV_9_sample.pdf'),
(5, 2, 3, '2025-01-20', 'proses', 'CV_5_sample2.pdf');

-- Insert Jurnal (contoh jurnal untuk mahasiswa yang diterima)
INSERT INTO jurnal (user_id, mentor_id, tanggal, aktivitas, file_penunjang, feedback, nilai) VALUES
(5, 2, '2025-02-01', 
'Hari pertama magang. Melakukan setup development environment, install XAMPP, PHP, Composer, dan Laravel. Membaca dokumentasi Laravel dan memahami struktur MVC. Membuat project Laravel pertama dan menjalankan migration database.', 
NULL, 
'Bagus untuk hari pertama. Lanjutkan dengan mempelajari routing dan controller di Laravel.', 
85.0),

(5, 2, '2025-02-02', 
'Mempelajari Laravel Routing dan Controller. Membuat beberapa route sederhana dan controller untuk handling request. Mencoba blade templating engine untuk membuat view. Praktik membuat CRUD sederhana untuk data mahasiswa.', 
NULL, 
'Progress sangat baik. Sudah memahami konsep MVC dengan baik. Lanjut ke Eloquent ORM besok.', 
88.0),

(6, 3, '2025-02-15', 
'Pengenalan Flutter dan Dart programming. Install Flutter SDK, Android Studio, dan setup emulator. Membuat project Flutter pertama dengan StatelessWidget dan StatefulWidget. Belajar tentang widget tree dan layout di Flutter.', 
NULL, 
'Pemahaman dasar sudah bagus. Coba eksplorasi lebih banyak widget bawaan Flutter.', 
82.0),

(5, 2, '2025-02-03', 
'Mempelajari Eloquent ORM untuk database operation. Membuat model, migration, dan seeder. Praktik CRUD menggunakan Eloquent. Memahami relationship (hasMany, belongsTo) antar model. Membuat API endpoint untuk get all data dan get by id.', 
NULL, 
NULL, 
NULL);

-- Informasi Login
SELECT '==================== INFORMASI LOGIN ====================' as '';
SELECT 'ADMIN' as Role, 'admin@magang.com' as Email, 'admin123' as Password
UNION ALL
SELECT 'MENTOR', 'budi@mentor.com', 'mentor123'
UNION ALL
SELECT 'MENTOR', 'siti@mentor.com', 'mentor123'
UNION ALL
SELECT 'MENTOR', 'ahmad@mentor.com', 'mentor123'
UNION ALL
SELECT 'MAHASISWA', 'andi@mahasiswa.com', 'mahasiswa123'
UNION ALL
SELECT 'MAHASISWA', 'rina@mahasiswa.com', 'mahasiswa123'
UNION ALL
SELECT 'MAHASISWA', 'deni@mahasiswa.com', 'mahasiswa123'
UNION ALL
SELECT 'MAHASISWA', 'lina@mahasiswa.com', 'mahasiswa123'
UNION ALL
SELECT 'MAHASISWA', 'roni@mahasiswa.com', 'mahasiswa123';

SELECT '=========================================================' as '';
SELECT 'Data dummy berhasil diinsert!' as Status;
SELECT 'Silakan login menggunakan kredensial di atas' as Info;
# Sistem Manajemen Magang

Website manajemen magang berbasis PHP Native dengan Bootstrap 5 dan SweetAlert2.

## 📋 Fitur Utama

### Untuk Mahasiswa
- ✅ Registrasi dan Login
- ✅ Dashboard dengan statistik
- ✅ Cari dan lihat lowongan magang
- ✅ Melamar lowongan dengan upload CV
- ✅ Tracking status lamaran
- ✅ Lihat daftar mentor
- ✅ Input jurnal magang harian
- ✅ Download sertifikat magang
- ✅ Edit profil dan ubah password

### Untuk Mentor
- ✅ Login dan Dashboard
- ✅ Kelola profil mentor
- ✅ Review dan approve/reject lamaran
- ✅ Monitoring jurnal pemagang
- ✅ Berikan feedback dan nilai
- ✅ Lihat statistik pemagang

### Untuk Admin
- ✅ Dashboard admin
- ✅ CRUD Master Data Mahasiswa
- ✅ CRUD Master Data Mentor
- ✅ CRUD Master Data Lowongan
- ✅ 5 Laporan lengkap:
  - Laporan Data Mahasiswa
  - Laporan Data Mentor
  - Laporan Lowongan Magang
  - Laporan Lamaran & Status
  - Laporan Aktivitas Jurnal

## 🚀 Instalasi

### Prasyarat
- PHP 7.4 atau lebih tinggi
- MySQL/MariaDB
- Web Server (Apache/Nginx)
- Browser modern

### Langkah Instalasi

1. **Clone atau Download Project**
   ```
   Download dan extract ke folder htdocs (XAMPP) atau www (WAMP)
   ```

2. **Buat Database**
   ```sql
   CREATE DATABASE IF NOT EXISTS magang_db;
   ```

3. **Import Database**
   - Buka phpMyAdmin
   - Pilih database `magang_db`
   - Jalankan script SQL yang ada di dokumentasi awal

4. **Konfigurasi Database**
   - Buka file `config/database.php`
   - Sesuaikan credential database jika perlu:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'magang_db');
   ```

5. **Buat Folder Upload**
   ```
   mkdir assets/uploads
   chmod 777 assets/uploads
   ```

6. **Akses Website**
   ```
   http://localhost/magang-app/
   ```

## 👥 Default Login

### Admin
- Email: admin@magang.com
- Password: admin123
- Role: Admin

Silakan tambahkan user admin melalui database atau setelah membuat mentor/mahasiswa.

## 📁 Struktur Folder

```
magang-app/
├── config/
│   └── database.php              # Koneksi database
├── includes/
│   ├── header.php                # Header dengan CDN
│   ├── footer.php                # Footer
│   └── auth.php                  # Autentikasi
├── assets/
│   ├── img/                      # Gambar/logo
│   └── uploads/                  # Upload CV & jurnal
├── pages/
│   ├── home.php                  # Landing page
│   ├── tentang.php               # Tentang/FAQ/Kontak
│   ├── login.php                 # Login
│   ├── register.php              # Registrasi
│   ├── dashboard_mahasiswa.php   # Dashboard mahasiswa
│   ├── dashboard_mentor.php      # Dashboard mentor
│   ├── dashboard_admin.php       # Dashboard admin
│   ├── dashboard_admin_modals.php # Modal CRUD admin
│   └── ... (halaman lainnya)
├── process/
│   ├── login_process.php         # Proses login
│   ├── register_process.php      # Proses registrasi
│   ├── lamaran_process.php       # Proses lamaran
│   ├── jurnal_process.php        # Proses jurnal
│   ├── mentor_process.php        # Proses mentor
│   └── admin_process.php         # Proses admin CRUD
├── index.php                     # Entry point
├── logout.php                    # Logout
└── README.md                     # Dokumentasi
```

## 🔧 Teknologi yang Digunakan

- **PHP Native** - Backend
- **MySQL** - Database
- **Bootstrap 5.3.2** - UI Framework (via CDN)
- **SweetAlert2** - Alert & Notifikasi (via CDN)
- **Bootstrap Icons** - Icon Set (via CDN)

## 📝 Catatan Penting

### Keamanan
⚠️ **PENTING**: Website ini dibuat untuk project skala kecil-menengah dengan password plain text (tanpa hash). Untuk production, sangat disarankan untuk:
- Menggunakan `password_hash()` untuk menyimpan password
- Implementasi prepared statement untuk mencegah SQL injection
- Validasi input yang lebih ketat
- Implementasi CSRF protection

### Upload File
- CV: Format PDF, maksimal 2MB
- Jurnal: Format PDF/JPG/PNG, maksimal 2MB
- Pastikan folder `assets/uploads/` memiliki permission write

### Browser Support
- Chrome (Recommended)
- Firefox
- Edge
- Safari

## 🎯 Cara Penggunaan

### Untuk Mahasiswa
1. Registrasi akun baru sebagai Mahasiswa
2. Login dengan email dan password
3. Browse lowongan yang tersedia
4. Lamar lowongan dengan upload CV
5. Tunggu approval dari mentor
6. Jika diterima, mulai isi jurnal harian
7. Download sertifikat setelah selesai magang

### Untuk Mentor
1. Registrasi sebagai Mentor (isi keahlian dan bio)
2. Login ke sistem
3. Admin akan membuat lowongan untuk Anda (atau minta admin)
4. Review lamaran yang masuk
5. Approve/Reject lamaran
6. Monitor jurnal pemagang
7. Berikan feedback dan nilai

### Untuk Admin
1. Login sebagai admin
2. Kelola master data (Mahasiswa, Mentor, Lowongan)
3. Monitor semua aktivitas sistem
4. Generate dan cetak laporan

## 📊 Laporan

Sistem menyediakan 5 jenis laporan:
1. **Laporan Data Mahasiswa** - Rekap mahasiswa & aktivitas magang
2. **Laporan Data Mentor** - Rekap mentor & pembimbingan
3. **Laporan Lowongan Magang** - Rekap lowongan & pelamar
4. **Laporan Lamaran & Status** - Statistik lamaran
5. **Laporan Aktivitas Jurnal** - Monitoring jurnal magang

Semua laporan dapat dicetak langsung dari browser.

## 🐛 Troubleshooting

### Error: "Call to undefined function mysqli_connect()"
**Solusi**: Aktifkan extension mysqli di php.ini
```
extension=mysqli
```

### Error: Upload file gagal
**Solusi**: 
- Cek permission folder uploads (chmod 777)
- Cek ukuran file (max 2MB)
- Cek format file (PDF untuk CV)

### Error: SweetAlert tidak muncul
**Solusi**: Pastikan koneksi internet aktif (CDN)

### Password salah terus
**Solusi**: Password disimpan plain text, pastikan input exact sama dengan database

## 📞 Support

Jika ada pertanyaan atau bug, silakan dokumentasikan issue yang ditemukan.

## 📄 License

Project ini dibuat untuk keperluan pembelajaran dan pengembangan.

---

**Dibuat dengan ❤️ menggunakan PHP Native, Bootstrap & SweetAlert**

*Versi 1.0 - 2025*# BTIKPmagang

<?php include '../includes/header.php'; ?>

<div class="container my-5">
    <!-- Tentang Section -->
    <section id="tentang" class="mb-5">
        <h2 class="text-center mb-4">Tentang Sistem Magang</h2>
        <div class="row">
            <div class="col-md-8 mx-auto">
                <p class="lead text-center">
                    Sistem Manajemen Magang adalah platform digital yang dirancang untuk memfasilitasi proses magang mahasiswa secara efisien dan terstruktur.
                </p>
                <p>
                    Platform ini menghubungkan mahasiswa dengan mentor profesional, menyediakan berbagai lowongan magang, dan memungkinkan monitoring progres magang secara real-time. Dengan fitur-fitur yang lengkap, kami berkomitmen untuk memberikan pengalaman magang terbaik bagi semua pihak yang terlibat.
                </p>
            </div>
        </div>
    </section>

    <hr class="my-5">

    <!-- FAQ Section -->
    <section id="faq" class="mb-5">
        <h2 class="text-center mb-4">Frequently Asked Questions (FAQ)</h2>
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Bagaimana cara mendaftar sebagai mahasiswa?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Klik menu "Daftar" atau "Register" di halaman utama, kemudian isi formulir pendaftaran dengan data lengkap Anda. Setelah berhasil mendaftar, Anda dapat login dan mulai mencari lowongan magang.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Apakah ada biaya untuk menggunakan platform ini?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Tidak, platform ini sepenuhnya gratis untuk digunakan oleh mahasiswa yang ingin mencari dan mengikuti program magang.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Bagaimana cara melamar lowongan magang?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Setelah login, buka menu "Lowongan Magang", pilih lowongan yang diminati, lalu klik tombol "Lamar" pada halaman detail lowongan. Anda perlu mengunggah CV dan melengkapi formulir lamaran.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Bagaimana cara memantau status lamaran saya?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Anda dapat memantau status lamaran melalui menu "Status Lamaran" di dashboard Anda. Setiap perubahan status akan ditampilkan di sana.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                Apa itu jurnal magang dan bagaimana cara mengisinya?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Jurnal magang adalah catatan harian aktivitas Anda selama magang. Anda dapat mengisinya melalui menu "Jurnal Magang" dengan mencatat aktivitas, progres, dan melampirkan file pendukung jika diperlukan.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                Bagaimana cara mendapatkan sertifikat magang?
                            </button>
                        </h2>
                        <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Setelah Anda menyelesaikan program magang dan mendapat persetujuan dari mentor, sertifikat dapat diunduh melalui menu "Download Sertifikat" di dashboard Anda.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <hr class="my-5">

    <!-- Kontak Section -->
    <section id="kontak" class="mb-5">
        <h2 class="text-center mb-4">Kontak Kami</h2>
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Hubungi Kami</h5>
                        <div class="mb-3">
                            <i class="bi bi-envelope-fill text-primary"></i>
                            <strong class="ms-2">Email:</strong>
                            <p class="ms-4">info@sistemmagang.com</p>
                        </div>
                        <div class="mb-3">
                            <i class="bi bi-telephone-fill text-primary"></i>
                            <strong class="ms-2">Telepon:</strong>
                            <p class="ms-4">+62 812-3456-7890</p>
                        </div>
                        <div class="mb-3">
                            <i class="bi bi-geo-alt-fill text-primary"></i>
                            <strong class="ms-2">Alamat:</strong>
                            <p class="ms-4">Jl. Pendidikan No. 123, Jakarta 12345</p>
                        </div>
                        <div class="mb-3">
                            <i class="bi bi-clock-fill text-primary"></i>
                            <strong class="ms-2">Jam Operasional:</strong>
                            <p class="ms-4">Senin - Jumat: 08:00 - 17:00 WIB</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include '../includes/footer.php'; ?>
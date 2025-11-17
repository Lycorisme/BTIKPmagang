<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Magang - BTIKP</title>
    
    <!-- Bootstrap CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- SweetAlert2 via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/img/logo.png">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <img src="../assets/img/logo.png" alt="BTIKP Logo">
                <span>BTIKP</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['role'] == 'mahasiswa'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="dashboard_mahasiswa.php">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="lowongan_list.php">
                                    <i class="bi bi-briefcase"></i> Lowongan
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="mentor_list.php">
                                    <i class="bi bi-people"></i> Mentor
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="lamaran_status.php">
                                    <i class="bi bi-list-check"></i> Status Lamaran
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="jurnal_mahasiswa.php">
                                    <i class="bi bi-journal-text"></i> Jurnal
                                </a>
                            </li>
                        <?php elseif ($_SESSION['role'] == 'mentor'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="dashboard_mentor.php">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="pemagang_list.php">
                                    <i class="bi bi-people"></i> Pemagang
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="jurnal_monitor.php">
                                    <i class="bi bi-journal-check"></i> Monitor Jurnal
                                </a>
                            </li>
                        <?php elseif ($_SESSION['role'] == 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="dashboard_admin.php">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-file-earmark-bar-graph"></i> Laporan
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="laporan1.php">
                                        <i class="bi bi-people"></i> Laporan Mahasiswa
                                    </a></li>
                                    <li><a class="dropdown-item" href="laporan2.php">
                                        <i class="bi bi-person-badge"></i> Laporan Mentor
                                    </a></li>
                                    <li><a class="dropdown-item" href="laporan3.php">
                                        <i class="bi bi-briefcase"></i> Laporan Lowongan
                                    </a></li>
                                    <li><a class="dropdown-item" href="laporan4.php">
                                        <i class="bi bi-file-text"></i> Laporan Lamaran
                                    </a></li>
                                    <li><a class="dropdown-item" href="laporan5.php">
                                        <i class="bi bi-journal-text"></i> Laporan Jurnal
                                    </a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?= $_SESSION['nama'] ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="profile_<?= $_SESSION['role'] ?>.php">
                                        <i class="bi bi-person"></i> Profil
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="../logout.php">
                                        <i class="bi bi-box-arrow-right"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="home.php">
                                <i class="bi bi-house"></i> Beranda
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="tentang.php">
                                <i class="bi bi-info-circle"></i> Tentang
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
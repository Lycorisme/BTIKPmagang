<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include helpers
$helpers_path = __DIR__ . '/helpers.php';
if (file_exists($helpers_path)) {
    require_once $helpers_path;
}

// Deteksi path relatif untuk assets
$current_path = $_SERVER['SCRIPT_NAME'] ?? '';
$is_in_pages = strpos($current_path, '/pages/') !== false;
$assets_prefix = $is_in_pages ? '../' : './';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Magang/PKL - BTIKP</title>
    
    <!-- Bootstrap CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- SweetAlert2 via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= $assets_prefix ?>assets/css/style.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= $assets_prefix ?>assets/img/logo.png">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="<?= $assets_prefix ?>index.php">
                <img src="<?= $assets_prefix ?>assets/img/logo.png" alt="BTIKP Logo">
                <span>BTIKP Magang</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['role'] == 'peserta_magang'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $is_in_pages ? '' : 'pages/' ?>dashboard_peserta_magang.php">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $is_in_pages ? '' : 'pages/' ?>absensi.php">
                                    <i class="bi bi-calendar-check"></i> Absensi
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $is_in_pages ? '' : 'pages/' ?>jurnal_peserta.php">
                                    <i class="bi bi-journal-text"></i> Jurnal
                                </a>
                            </li>
                        <?php elseif ($_SESSION['role'] == 'mentor'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $is_in_pages ? '' : 'pages/' ?>dashboard_mentor.php">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $is_in_pages ? '' : 'pages/' ?>peserta_mentor.php">
                                    <i class="bi bi-people"></i> Peserta Magang
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $is_in_pages ? '' : 'pages/' ?>jurnal_monitor.php">
                                    <i class="bi bi-journal-check"></i> Monitor Jurnal
                                </a>
                            </li>
                        <?php elseif ($_SESSION['role'] == 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $is_in_pages ? '' : 'pages/' ?>dashboard_admin.php">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $is_in_pages ? '' : 'pages/' ?>kelola_pendaftaran.php">
                                    <i class="bi bi-clipboard-check"></i> Pendaftaran
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-file-earmark-bar-graph"></i> Laporan
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="<?= $is_in_pages ? '' : 'pages/' ?>laporan1.php">
                                        <i class="bi bi-people"></i> Laporan Peserta Magang
                                    </a></li>
                                    <li><a class="dropdown-item" href="<?= $is_in_pages ? '' : 'pages/' ?>laporan2.php">
                                        <i class="bi bi-person-badge"></i> Laporan Mentor
                                    </a></li>
                                    <li><a class="dropdown-item" href="<?= $is_in_pages ? '' : 'pages/' ?>laporan3.php">
                                        <i class="bi bi-calendar-check"></i> Laporan Absensi
                                    </a></li>
                                    <li><a class="dropdown-item" href="<?= $is_in_pages ? '' : 'pages/' ?>laporan4.php">
                                        <i class="bi bi-journal-text"></i> Laporan Jurnal
                                    </a></li>
                                    <li><a class="dropdown-item" href="<?= $is_in_pages ? '' : 'pages/' ?>laporan5.php">
                                        <i class="bi bi-award"></i> Laporan Sertifikat
                                    </a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['nama']) ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="<?= $is_in_pages ? '' : 'pages/' ?>profile_<?= $_SESSION['role'] ?>.php">
                                        <i class="bi bi-person"></i> Profil
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="<?= $assets_prefix ?>logout.php">
                                        <i class="bi bi-box-arrow-right"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $is_in_pages ? '' : 'pages/' ?>home.php">
                                <i class="bi bi-house"></i> Beranda
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $is_in_pages ? '' : 'pages/' ?>tentang.php">
                                <i class="bi bi-info-circle"></i> Tentang
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $is_in_pages ? '' : 'pages/' ?>login.php">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
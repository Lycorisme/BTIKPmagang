<?php
session_start();
require_once '../config/database.php';

$action = isset($_POST['action']) ? $_POST['action'] : '';

// CREATE - Melamar lowongan
if ($action == 'create') {
    $user_id = intval($_SESSION['user_id']);
    $lowongan_id = intval($_POST['lowongan_id']);
    $mentor_id = intval($_POST['mentor_id']);
    $tgl_melamar = date('Y-m-d');
    
    // Cek apakah lowongan masih buka
    $check_lowongan = "SELECT status FROM lowongan WHERE id = ?";
    $stmt_lowongan = mysqli_prepare($conn, $check_lowongan);
    mysqli_stmt_bind_param($stmt_lowongan, "i", $lowongan_id);
    mysqli_stmt_execute($stmt_lowongan);
    $result_lowongan = mysqli_stmt_get_result($stmt_lowongan);
    
    if (mysqli_num_rows($result_lowongan) == 0) {
        mysqli_stmt_close($stmt_lowongan);
        
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Lowongan tidak ditemukan'
            }).then(() => {
                window.history.back();
            });
        </script>";
        exit();
    }
    
    $lowongan = mysqli_fetch_assoc($result_lowongan);
    if ($lowongan['status'] != 'open') {
        mysqli_stmt_close($stmt_lowongan);
        
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian!',
                text: 'Lowongan sudah ditutup'
            }).then(() => {
                window.history.back();
            });
        </script>";
        exit();
    }
    mysqli_stmt_close($stmt_lowongan);
    
    // Cek apakah sudah pernah melamar lowongan ini
    $check_query = "SELECT id FROM lamaran WHERE user_id = ? AND lowongan_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "ii", $user_id, $lowongan_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) > 0) {
        mysqli_stmt_close($check_stmt);
        
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian!',
                text: 'Anda sudah pernah melamar lowongan ini'
            }).then(() => {
                window.history.back();
            });
        </script>";
        exit();
    }
    mysqli_stmt_close($check_stmt);
    
    // Upload CV
    $file_cv = '';
    if (isset($_FILES['file_cv']) && $_FILES['file_cv']['error'] == 0) {
        $file_name = $_FILES['file_cv']['name'];
        $file_tmp = $_FILES['file_cv']['tmp_name'];
        $file_size = $_FILES['file_cv']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if ($file_ext == 'pdf' && $file_size <= 5242880) { // 5MB
            $new_file_name = 'CV_' . $user_id . '_' . time() . '.pdf';
            $upload_path = '../assets/uploads/' . $new_file_name;
            
            // Pastikan folder uploads ada
            if (!file_exists('../assets/uploads/')) {
                mkdir('../assets/uploads/', 0777, true);
            }
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                $file_cv = $new_file_name;
            } else {
                echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Gagal mengupload CV'
                    }).then(() => {
                        window.history.back();
                    });
                </script>";
                exit();
            }
        } else {
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'File harus PDF dan maksimal 5MB'
                }).then(() => {
                    window.history.back();
                });
            </script>";
            exit();
        }
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'CV wajib diupload'
            }).then(() => {
                window.history.back();
            });
        </script>";
        exit();
    }
    
    $query = "INSERT INTO lamaran (user_id, lowongan_id, mentor_id, tgl_melamar, file_cv, status) 
              VALUES (?, ?, ?, ?, ?, 'proses')";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "iiiss", $user_id, $lowongan_id, $mentor_id, $tgl_melamar, $file_cv);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Lamaran Anda berhasil dikirim'
            }).then(() => {
                window.location.href = '../pages/lamaran_status.php';
            });
        </script>";
    } else {
        // Hapus file CV jika insert gagal
        if ($file_cv && file_exists('../assets/uploads/' . $file_cv)) {
            unlink('../assets/uploads/' . $file_cv);
        }
        
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Terjadi kesalahan saat mengirim lamaran'
            }).then(() => {
                window.history.back();
            });
        </script>";
    }
    mysqli_stmt_close($stmt);
}

// UPDATE STATUS - Untuk mentor
if ($action == 'update_status') {
    $lamaran_id = intval($_POST['lamaran_id']);
    $status = $_POST['status'];
    
    // Validasi status
    $valid_status = ['proses', 'diterima', 'ditolak'];
    if (!in_array($status, $valid_status)) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Status tidak valid'
            }).then(() => {
                window.history.back();
            });
        </script>";
        exit();
    }
    
    // Verifikasi mentor memiliki akses
    $mentor_user_id = intval($_SESSION['user_id']);
    
    // Ambil mentor_id dari tabel mentors
    $get_mentor = "SELECT id FROM mentors WHERE user_id = ?";
    $stmt_mentor = mysqli_prepare($conn, $get_mentor);
    mysqli_stmt_bind_param($stmt_mentor, "i", $mentor_user_id);
    mysqli_stmt_execute($stmt_mentor);
    $result_mentor = mysqli_stmt_get_result($stmt_mentor);
    
    if (mysqli_num_rows($result_mentor) == 0) {
        mysqli_stmt_close($stmt_mentor);
        
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Data mentor tidak ditemukan'
            }).then(() => {
                window.history.back();
            });
        </script>";
        exit();
    }
    
    $mentor_data = mysqli_fetch_assoc($result_mentor);
    $mentor_id = $mentor_data['id'];
    mysqli_stmt_close($stmt_mentor);
    
    // Cek apakah lamaran ini milik mentor
    $check_query = "SELECT id FROM lamaran WHERE id = ? AND mentor_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "ii", $lamaran_id, $mentor_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) == 0) {
        mysqli_stmt_close($check_stmt);
        
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Anda tidak memiliki akses untuk mengubah status lamaran ini'
            }).then(() => {
                window.history.back();
            });
        </script>";
        exit();
    }
    mysqli_stmt_close($check_stmt);
    
    $query = "UPDATE lamaran SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "si", $status, $lamaran_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Status lamaran berhasil diupdate'
            }).then(() => {
                window.location.href = '../pages/pemagang_list.php';
            });
        </script>";
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Terjadi kesalahan saat mengupdate status'
            }).then(() => {
                window.history.back();
            });
        </script>";
    }
    mysqli_stmt_close($stmt);
}

// DELETE - Batalkan lamaran (untuk mahasiswa)
if ($action == 'delete') {
    $lamaran_id = intval($_POST['lamaran_id']);
    $user_id = intval($_SESSION['user_id']);
    
    // Ambil data file untuk dihapus
    $check_query = "SELECT file_cv, status FROM lamaran WHERE id = ? AND user_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "ii", $lamaran_id, $user_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) == 0) {
        mysqli_stmt_close($check_stmt);
        
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Anda tidak memiliki akses untuk membatalkan lamaran ini'
            }).then(() => {
                window.history.back();
            });
        </script>";
        exit();
    }
    
    $lamaran_data = mysqli_fetch_assoc($check_result);
    $file_cv = $lamaran_data['file_cv'];
    $status = $lamaran_data['status'];
    mysqli_stmt_close($check_stmt);
    
    // Cek apakah lamaran masih bisa dibatalkan
    if ($status == 'diterima') {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian!',
                text: 'Lamaran yang sudah diterima tidak bisa dibatalkan'
            }).then(() => {
                window.history.back();
            });
        </script>";
        exit();
    }
    
    // Hapus lamaran
    $query = "DELETE FROM lamaran WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $lamaran_id, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        // Hapus file CV jika ada
        if ($file_cv && file_exists('../assets/uploads/' . $file_cv)) {
            unlink('../assets/uploads/' . $file_cv);
        }
        
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Lamaran berhasil dibatalkan'
            }).then(() => {
                window.location.href = '../pages/lamaran_status.php';
            });
        </script>";
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Terjadi kesalahan saat membatalkan lamaran'
            }).then(() => {
                window.history.back();
            });
        </script>";
    }
    mysqli_stmt_close($stmt);
}

// UPDATE CV - Update CV lamaran (untuk mahasiswa)
if ($action == 'update_cv') {
    $lamaran_id = intval($_POST['lamaran_id']);
    $user_id = intval($_SESSION['user_id']);
    
    // Cek kepemilikan dan status lamaran
    $check_query = "SELECT file_cv, status FROM lamaran WHERE id = ? AND user_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "ii", $lamaran_id, $user_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) == 0) {
        mysqli_stmt_close($check_stmt);
        
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Anda tidak memiliki akses untuk mengupdate CV ini'
            }).then(() => {
                window.history.back();
            });
        </script>";
        exit();
    }
    
    $lamaran_data = mysqli_fetch_assoc($check_result);
    $old_cv = $lamaran_data['file_cv'];
    $status = $lamaran_data['status'];
    mysqli_stmt_close($check_stmt);
    
    // Hanya bisa update CV jika status masih proses
    if ($status != 'proses') {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian!',
                text: 'CV hanya bisa diupdate saat lamaran masih dalam proses'
            }).then(() => {
                window.history.back();
            });
        </script>";
        exit();
    }
    
    // Upload CV baru
    if (isset($_FILES['file_cv']) && $_FILES['file_cv']['error'] == 0) {
        $file_name = $_FILES['file_cv']['name'];
        $file_tmp = $_FILES['file_cv']['tmp_name'];
        $file_size = $_FILES['file_cv']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if ($file_ext == 'pdf' && $file_size <= 5242880) {
            $new_file_name = 'CV_' . $user_id . '_' . time() . '.pdf';
            $upload_path = '../assets/uploads/' . $new_file_name;
            
            if (!file_exists('../assets/uploads/')) {
                mkdir('../assets/uploads/', 0777, true);
            }
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Update database
                $query = "UPDATE lamaran SET file_cv = ? WHERE id = ? AND user_id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "sii", $new_file_name, $lamaran_id, $user_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    // Hapus CV lama
                    if ($old_cv && file_exists('../assets/uploads/' . $old_cv)) {
                        unlink('../assets/uploads/' . $old_cv);
                    }
                    
                    mysqli_stmt_close($stmt);
                    
                    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
                    echo "<script>
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'CV berhasil diupdate'
                        }).then(() => {
                            window.location.href = '../pages/lamaran_status.php';
                        });
                    </script>";
                } else {
                    // Hapus file baru jika update gagal
                    if (file_exists($upload_path)) {
                        unlink($upload_path);
                    }
                    
                    mysqli_stmt_close($stmt);
                    
                    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
                    echo "<script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: 'Terjadi kesalahan saat mengupdate CV'
                        }).then(() => {
                            window.history.back();
                        });
                    </script>";
                }
            } else {
                echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Gagal mengupload CV baru'
                    }).then(() => {
                        window.history.back();
                    });
                </script>";
            }
        } else {
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'File harus PDF dan maksimal 5MB'
                }).then(() => {
                    window.history.back();
                });
            </script>";
        }
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'File CV wajib diupload'
            }).then(() => {
                window.history.back();
            });
        </script>";
    }
}

// BULK UPDATE STATUS - Untuk mentor mengupdate multiple lamaran sekaligus
if ($action == 'bulk_update_status') {
    $lamaran_ids = isset($_POST['lamaran_ids']) ? $_POST['lamaran_ids'] : [];
    $status = $_POST['status'];
    $mentor_user_id = intval($_SESSION['user_id']);
    
    if (empty($lamaran_ids)) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian!',
                text: 'Pilih minimal satu lamaran'
            }).then(() => {
                window.history.back();
            });
        </script>";
        exit();
    }
    
    // Validasi status
    $valid_status = ['proses', 'diterima', 'ditolak'];
    if (!in_array($status, $valid_status)) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Status tidak valid'
            }).then(() => {
                window.history.back();
            });
        </script>";
        exit();
    }
    
    // Ambil mentor_id
    $get_mentor = "SELECT id FROM mentors WHERE user_id = ?";
    $stmt_mentor = mysqli_prepare($conn, $get_mentor);
    mysqli_stmt_bind_param($stmt_mentor, "i", $mentor_user_id);
    mysqli_stmt_execute($stmt_mentor);
    $result_mentor = mysqli_stmt_get_result($stmt_mentor);
    $mentor_data = mysqli_fetch_assoc($result_mentor);
    $mentor_id = $mentor_data['id'];
    mysqli_stmt_close($stmt_mentor);
    
    $success_count = 0;
    $fail_count = 0;
    
    foreach ($lamaran_ids as $lamaran_id) {
        $lamaran_id = intval($lamaran_id);
        
        // Verifikasi kepemilikan
        $check_query = "SELECT id FROM lamaran WHERE id = ? AND mentor_id = ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, "ii", $lamaran_id, $mentor_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            $query = "UPDATE lamaran SET status = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "si", $status, $lamaran_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success_count++;
            } else {
                $fail_count++;
            }
            mysqli_stmt_close($stmt);
        } else {
            $fail_count++;
        }
        mysqli_stmt_close($check_stmt);
    }
    
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
        Swal.fire({
            icon: '" . ($fail_count == 0 ? 'success' : 'warning') . "',
            title: 'Proses Selesai!',
            html: 'Berhasil: " . $success_count . " lamaran<br>Gagal: " . $fail_count . " lamaran'
        }).then(() => {
            window.location.href = '../pages/pemagang_list.php';
        });
    </script>";
}
?>
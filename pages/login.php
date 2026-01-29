<?php 
session_start();

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    header("Location: dashboard_$role.php");
    exit();
}

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h3 class="text-center mb-4">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </h3>
                    
                    <form id="loginForm" method="POST" action="../process/login_process.php">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </button>
                    </form>
                    
                    <hr class="my-4">
                    
                    <p class="text-center mb-0">
                        Belum punya akun? <a href="register.php">Daftar sebagai Peserta Magang</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
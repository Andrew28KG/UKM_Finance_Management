<?php
session_start();
include('inc/header.php');
include('inc/auth.php');
include('class/finance.php');

// Check if user is already logged in
if(isLoggedIn()) {
    header("Location: index.php");
    exit();
}

// Clear preview mode if coming from preview
if(isset($_SESSION['preview_mode'])) {
    unset($_SESSION['preview_mode']);
}

$error = '';

// Process login form
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    if(empty($email) || empty($password)) {
        $error = "Email dan password harus diisi";
    } else {
        $finance = new Finance();
        
        try {
            $user = $finance->userLogin($email, $password);
            
            if($user) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nama'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['ukm_id'] = $user['ukm_id'];
                
                header("Location: index.php");
                exit();
            } else {
                $error = "Email atau password salah. Pastikan email dan password yang dimasukkan benar.";
            }
        } catch (Exception $e) {
            $error = "Terjadi kesalahan saat login: " . $e->getMessage();
            error_log("Login error: " . $e->getMessage());
        }
    }
}
?>

<section class="login-page">
    <div class="login-container">
        <div class="login-header">
            <h1>Login UKM Finance</h1>
            <p>Masuk untuk mengelola keuangan UKM Anda</p>
        </div>
        
        <?php if($error): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </div>
        </form>
        
        <div class="login-footer">
            <p>Belum memiliki akun? Hubungi administrator UKM.</p>
            <p><small style="color: #666;">Test credentials: admin@example.com / password123</small></p>
        </div>
    </div>
</section>

<?php include('inc/footer.php'); ?> 
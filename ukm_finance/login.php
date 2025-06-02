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
        
        // Verify database connection
        $conn = $finance->getConnection();
        if (!$conn) {
            $error = "Koneksi database gagal. Silakan hubungi administrator.";
            error_log("Database connection failed in login.php");
        } else {
            try {
                $user = $finance->userLogin($email, $password);
                
                if($user) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['nama'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['ukm_id'] = $user['ukm_id'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    // Log successful login
                    error_log("User {$user['email']} logged in successfully");
                    
                    header("Location: index.php");
                    exit();
                } else {
                    $error = "Email atau password salah. Pastikan email dan password yang dimasukkan benar.";
                    error_log("Failed login attempt for email: $email");
                }
            } catch (Exception $e) {
                $error = "Terjadi kesalahan saat login: " . $e->getMessage();
                error_log("Login error: " . $e->getMessage());
            }
        }
    }
}

// Get list of available users for testing
$finance = new Finance();
$testUsers = [
    ['email' => 'admin@example.com', 'name' => 'Admin', 'role' => 'admin'],
    ['email' => 'budi@example.com', 'name' => 'Budi Santoso', 'role' => 'bendahara', 'ukm' => 'UKM Olahraga'],
    ['email' => 'dewi@example.com', 'name' => 'Dewi Lestari', 'role' => 'bendahara', 'ukm' => 'UKM Musik'],
    ['email' => 'andi@example.com', 'name' => 'Andi Wijaya', 'role' => 'bendahara', 'ukm' => 'UKM Fotografi'],
    ['email' => 'siti@example.com', 'name' => 'Siti Nuraini', 'role' => 'bendahara', 'ukm' => 'UKM Jurnalistik'],
    ['email' => 'rudi@example.com', 'name' => 'Rudi Hermawan', 'role' => 'bendahara', 'ukm' => 'UKM Pecinta Alam']
];
?>

<section class="login-page">
    <div class="login-container">
        <div class="login-header">
            <h1>Login UKM Finance</h1>
            <p>Masuk untuk mengelola keuangan UKM Anda</p>
        </div>
        
        <?php if($error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
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
            <div class="test-accounts">
                <h4>Test Accounts (Password: password123)</h4>
                <table class="test-accounts-table">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>UKM</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($testUsers as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td><?php echo isset($user['ukm']) ? htmlspecialchars($user['ukm']) : '-'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<style>
.test-accounts {
    margin-top: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 5px;
}

.test-accounts h4 {
    margin-bottom: 10px;
    color: #333;
}

.test-accounts-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9em;
}

.test-accounts-table th,
.test-accounts-table td {
    padding: 8px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.test-accounts-table th {
    background-color: #f1f1f1;
}

.test-accounts-table tr:hover {
    background-color: #f5f5f5;
}
</style>

<?php include('inc/footer.php'); ?> 
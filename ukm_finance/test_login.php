<?php
// Test script for login functionality
include('class/finance.php');

// Test data
$email = isset($_GET['email']) ? $_GET['email'] : 'admin@example.com';
$password = isset($_GET['password']) ? $_GET['password'] : 'password123';

// Create Finance object
$finance = new Finance();

// Try login
$user = $finance->userLogin($email, $password);

// Display result
echo "<h2>Login Test</h2>";
echo "<p>Testing login with email: <strong>{$email}</strong> and password: <strong>{$password}</strong></p>";

if ($user) {
    echo "<div style='color: green; padding: 10px; border: 1px solid green;'>";
    echo "<h3>Login Successful! ✅</h3>";
    echo "<p>User details:</p>";
    echo "<ul>";
    echo "<li>ID: {$user['id']}</li>";
    echo "<li>Name: {$user['nama']}</li>";
    echo "<li>Email: {$user['email']}</li>";
    echo "<li>UKM ID: {$user['ukm_id']}</li>";
    echo "<li>Role: {$user['role']}</li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div style='color: red; padding: 10px; border: 1px solid red;'>";
    echo "<h3>Login Failed! ❌</h3>";
    echo "<p>Could not authenticate with the provided credentials.</p>";
    echo "</div>";
}

// Show form to try other credentials
echo "<h3>Try different credentials:</h3>";
echo "<form method='get' action=''>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='email'>Email:</label><br>";
echo "<input type='email' id='email' name='email' value='{$email}'>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='password'>Password:</label><br>";
echo "<input type='password' id='password' name='password' value='{$password}'>";
echo "</div>";
echo "<button type='submit'>Test Login</button>";
echo "</form>";

// Show a link to the actual login page
echo "<p><a href='login.php'>Go to the actual login page</a></p>";
?> 
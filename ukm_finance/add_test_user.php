<?php
// Simple script to add a test user to the database
include('class/finance.php');

// Connect to database
$finance = new Finance();
$conn = $finance->getConnection();

// Check if user already exists
$email = "admin@example.com";
$checkQuery = "SELECT * FROM users WHERE email = '$email'";
$result = mysqli_query($conn, $checkQuery);

if (mysqli_num_rows($result) > 0) {
    echo "User with email '$email' already exists.<br>";
} else {
    // Create test user
    $password = "admin123"; // Plain text for testing (normally you would hash this)
    $nama = "Administrator";
    $ukm_id = 1; // Assuming UKM with ID 1 exists
    
    $query = "INSERT INTO users (nama, email, password, ukm_id) VALUES ('$nama', '$email', '$password', $ukm_id)";
    
    if (mysqli_query($conn, $query)) {
        echo "Test user created successfully.<br>";
        echo "Email: $email<br>";
        echo "Password: $password<br>";
    } else {
        echo "Error creating user: " . mysqli_error($conn) . "<br>";
    }
}

// List all users in the database
$listQuery = "SELECT * FROM users";
$listResult = mysqli_query($conn, $listQuery);

if (mysqli_num_rows($listResult) > 0) {
    echo "<h3>Current users in database:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>UKM ID</th></tr>";
    
    while ($row = mysqli_fetch_assoc($listResult)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['nama'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td>" . $row['ukm_id'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "No users found in the database.";
}
?> 
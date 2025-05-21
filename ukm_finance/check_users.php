<?php
// Simple script to check database connection and users
include('class/finance.php');

// Connect to database
$finance = new Finance();
$conn = $finance->getConnection();

if (!$conn) {
    die("Database connection failed");
}

echo "Database connection successful<br>";

// Check if users table exists
$checkTableQuery = "SHOW TABLES LIKE 'users'";
$tableResult = mysqli_query($conn, $checkTableQuery);

if (mysqli_num_rows($tableResult) == 0) {
    echo "Users table does not exist<br>";
    exit();
}

// List all users in the database
$listQuery = "SELECT * FROM users";
$listResult = mysqli_query($conn, $listQuery);

if (!$listResult) {
    echo "Error executing query: " . mysqli_error($conn) . "<br>";
    exit();
}

if (mysqli_num_rows($listResult) > 0) {
    echo "<h3>Current users in database:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Password (hashed)</th><th>UKM ID</th></tr>";
    
    while ($row = mysqli_fetch_assoc($listResult)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['nama'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td>" . $row['password'] . "</td>";
        echo "<td>" . $row['ukm_id'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Add a message about using these credentials
    echo "<p>You can try logging in with any of these emails and the password 'password123' (or plain text password if shown).</p>";
} else {
    echo "No users found in the database.<br>";
    
    // Add a sample user if none exist
    $addUserQuery = "INSERT INTO users (nama, email, password, ukm_id, role) VALUES 
                    ('Test Admin', 'admin@test.com', 'admin123', 1, 'admin')";
    
    if (mysqli_query($conn, $addUserQuery)) {
        echo "Added a test user:<br>";
        echo "Email: admin@test.com<br>";
        echo "Password: admin123<br>";
    } else {
        echo "Error adding test user: " . mysqli_error($conn) . "<br>";
    }
}
?> 
<?php
session_start();

// Set preview mode session variable
$_SESSION['preview_mode'] = true;

// Redirect to the requested page or default to index
$redirect = isset($_GET['page']) ? $_GET['page'] : 'index.php';
header("Location: $redirect");
exit();
?> 
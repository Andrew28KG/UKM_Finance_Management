<?php
function isAuthenticated() {
    return isset($_SESSION['user_id']) || isset($_SESSION['preview_mode']);
}

function requireAuth() {
    if (!isAuthenticated()) {
        header("Location: login.php");
        exit();
    }
}

function isPreviewMode() {
    return isset($_SESSION['preview_mode']);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
?> 
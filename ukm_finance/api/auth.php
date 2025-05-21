<?php
session_start();

function isApiAuthenticated() {
    return isset($_SESSION['user_id']) || isset($_SESSION['preview_mode']);
}

function requireApiAuth() {
    if (!isApiAuthenticated()) {
        header('HTTP/1.0 401 Unauthorized');
        echo json_encode([
            'status' => 0,
            'status_pesan' => 'Unauthorized access'
        ]);
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
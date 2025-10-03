<?php
// auth.php
session_start();

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

function require_role($roles) {
    if (!in_array($_SESSION['role'], (array)$roles)) {
        // Forbidden
        http_response_code(403);
        echo "<h1>403 Forbidden</h1>";
        echo "<p>You do not have permission to access this page.</p>";
        exit();
    }
}

<?php
require_once "db_connect.php"; // Make sure this exists

// Check if user is logged in
function is_logged_in(){
    return isset($_SESSION['user_id']);
}

// Get current user info
function current_user(){
    if(!is_logged_in()) return null;
    global $conn;
    $stmt = $conn->prepare("SELECT id, name, email, role FROM users WHERE id=? LIMIT 1");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

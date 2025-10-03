<?php
// Don't call session_start() here if it's already started in index.php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

require_once "db_connect.php";
require_once "functions.php";

// Check if user is logged in
if(!isset($_SESSION['user_id'])){
    http_response_code(403);
    exit("Forbidden");
}

$user_role = $_SESSION['role'];

// List of all modules and their files
$all_modules = [
    "Dashboard"=>"dashboard.php",
    "Emergency Contacts"=>"contacts.php",
    "Drill Scheduling"=>"drills.php",
    "Evacuation Plans"=>"evacuation_plans.php",
    "Incident Logging"=>"incidents.php",
    "Safety Checklist"=>"safety_checklist.php",
    "Risk Assessment"=>"compliance.php",
    "Parent Notifications"=>"notifications.php",
    "Compliance Reports"=>"compliance.php",
    "Role Assignment"=>"roles.php",
    "First Aid Monitor"=>"supplies.php"
];

// Role-based accessible modules
$role_modules = [
    "admin" => array_keys($all_modules),
    "teacher" => ["Dashboard","Emergency Contacts","Drill Scheduling","Evacuation Plans","Incident Logging","Safety Checklist","Risk Assessment","Parent Notifications","First Aid Monitor"],
    "student" => ["Dashboard","Emergency Contacts","Drill Scheduling","Evacuation Plans","First Aid Monitor"]
];

// Check requested module
if(isset($_GET['m'])){
    $module = $_GET['m'];

    // Check if module exists and if role can access
    if(array_key_exists($module, $all_modules) && in_array($module, $role_modules[$user_role])){
        $module_file = __DIR__ . '/index.php/' . $all_modules[$module];
        
        if(file_exists($module_file)){
            include $module_file;
        } else {
            echo "<p>Module file not found.</p>";
        }
    } else {
        echo "<p>You do not have permission to access this module.</p>";
    }
} else {
    // Default module if none selected
    include __DIR__ . 'index.php';
}

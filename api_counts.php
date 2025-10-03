<?php
require_once 'functions.php';
$res = $conn->query("SELECT COUNT(*) AS c FROM first_aid_supplies");
$fa = $res->fetch_assoc()['c'] ?? 0;
header('Content-Type: application/json');
echo json_encode(['first_aid'=>$fa]);

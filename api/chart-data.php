<?php
require_once "../db_connect.php";

// get last 12 metric rows
$sql = "SELECT metric_date, safety_score FROM dashboard_metrics ORDER BY metric_date ASC LIMIT 12";
$res = $conn->query($sql);
$labels = []; $values = [];
if ($res) {
    while($r = $res->fetch_assoc()){
        $labels[] = $r['metric_date'];
        $values[] = (int)$r['safety_score'];
    }
}
// if empty, return sample data
if (empty($labels)) {
    $labels = ['2025-01','2025-02','2025-03','2025-04','2025-05','2025-06'];
    $values = [78,82,80,85,87,90];
}
header('Content-Type: application/json');
echo json_encode(['labels'=>$labels,'values'=>$values]);

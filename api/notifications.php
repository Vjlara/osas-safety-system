<?php
require_once "../db_connect.php";

$action = $_GET['action'] ?? null;
if ($action === 'get') {
    // unread count & last 10
    $res = $conn->query("SELECT COUNT(*) as c FROM notifications WHERE is_read=0");
    $unread = $res->fetch_assoc()['c'] ?? 0;
    $items = [];
    $r = $conn->query("SELECT id,title,body,level,created_at FROM notifications ORDER BY created_at DESC LIMIT 20");
    while($row = $r->fetch_assoc()) $items[] = $row;
    header('Content-Type: application/json');
    echo json_encode(['unread'=>(int)$unread,'items'=>$items]);
    exit;
}
if ($action === 'mark_read') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = (int)$data['id'];
    $conn->query("UPDATE notifications SET is_read=1 WHERE id=" . $id);
    echo json_encode(['ok'=>true]);
    exit;
}

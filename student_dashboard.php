<?php
session_start();
require_once "db_connect.php";
require_once "functions.php";

if(!is_logged_in() || $_SESSION['role']!=='student'){
    header("Location: index.php");
    exit;
}

$user = current_user();

// Notifications dynamic column
$notification_column = '';
$res = $conn->query("SHOW COLUMNS FROM notifications");
while($col=$res->fetch_assoc()){
    if(in_array($col['Field'], ['message','content','notification_text'])){
        $notification_column = $col['Field'];
        break;
    }
}
if(!$notification_column) die("Notifications table does not have a message column.");
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Student Dashboard - OSAS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#f6f7f8;font-family:sans-serif;}
.sidebar{width:260px;background:#198754;color:white;min-height:100vh;position:fixed;padding:20px;overflow-y:auto;}
.sidebar a{color:white;display:block;padding:10px;text-decoration:none;margin-bottom:5px;}
.sidebar a.active, .sidebar a:hover{background:rgba(255,255,255,0.2);border-radius:5px;}
.content{margin-left:280px;padding:20px;}
.call911{background:#d9534f;color:white;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;width:100%;margin-top:10px;}
</style>
</head>
<body>

<div class="sidebar">
<h4>Student Panel</h4>
<p>Welcome, <?= htmlspecialchars($user['name']) ?></p>
<a href="?module=dashboard" class="active">Dashboard</a>
<a href="?module=notifications">Notifications</a>
<a href="?module=drills">Upcoming Drills</a>
<a href="?module=evacuation">Evacuation Plans</a>
<hr>
<button id="call911" class="call911">📞 Call 911</button>
<a href="index.php?logout=1" class="mt-3 d-block">Logout</a>
</div>

<div class="content">
<?php
$module = $_GET['module'] ?? 'dashboard';

switch($module){
    case 'notifications':
        echo "<h3>Latest Notifications</h3>";
        echo "<ul class='list-group'>";
        $stmt = $conn->prepare("SELECT `$notification_column`, created_at FROM notifications ORDER BY created_at DESC LIMIT 10");
        $stmt->execute();
        $res = $stmt->get_result();
        while($row=$res->fetch_assoc()){
            echo "<li class='list-group-item'>".htmlspecialchars($row[$notification_column])."<span class='badge bg-secondary float-end'>".htmlspecialchars($row['created_at'])."</span></li>";
        }
        echo "</ul>";
        break;
    
    case 'drills':
        echo "<h3>Upcoming Drills</h3>";
        $res = $conn->query("SELECT title, date, description FROM drills ORDER BY date ASC LIMIT 10");
        if($res->num_rows){
            while($row=$res->fetch_assoc()){
                echo "<div class='card mb-2'><div class='card-body'><h5>{$row['title']}</h5><p>{$row['description']}</p><small>Date: {$row['date']}</small></div></div>";
            }
        } else echo "<p>No upcoming drills.</p>";
        break;

    case 'evacuation':
        echo "<h3>Evacuation Plans</h3>";
        $res = $conn->query("SELECT plan_name, location, details FROM evacuation_plans LIMIT 10");
        if($res->num_rows){
            while($row=$res->fetch_assoc()){
                echo "<div class='card mb-2'><div class='card-body'><h5>{$row['plan_name']}</h5><p>{$row['details']}</p><small>Location: {$row['location']}</small></div></div>";
            }
        } else echo "<p>No evacuation plans available.</p>";
        break;

    default:
        echo "<h3>Welcome to the Student Dashboard</h3>";
        echo "<p>Select a module from the sidebar to view its content.</p>";
        break;
}
?>
</div>

<script>
document.getElementById('call911').addEventListener('click', async function(){
    const name = "<?= htmlspecialchars($user['name']) ?>";
    const location = prompt("Your location (optional):","");
    const message = prompt("Short message for emergency:", "Emergency! Please respond.");
    if(!message) return alert("Message is required.");
    try {
        const resp = await fetch('emergency.php', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body:JSON.stringify({name, location, message})
        });
        const j = await resp.json();
        if(j.success){
            alert('Emergency logged. Calling 911...');
            window.location.href="tel:911";
        } else alert('Error: '+(j.message||'unknown'));
    } catch(e){ alert('Error: '+e.message);}
});
</script>

</body>
</html>

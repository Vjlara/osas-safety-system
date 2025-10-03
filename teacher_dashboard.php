<?php
session_start();
require_once "functions.php";
require_once "db_connect.php"; // ✅ ensure DB connection

// Protect page (teacher only)
if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}
$user = current_user();
if ($user['role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Teacher Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <style>
    body { background:#f6f7f8; font-family:sans-serif; margin:0; }
    .sidebar { width:250px; background:#163c8c; color:white; min-height:100vh; float:left; padding:20px; }
    .sidebar h4 { color:#fff; }
    .sidebar a { color:white; display:block; padding:8px; text-decoration:none; border-radius:4px; }
    .sidebar a:hover { background:rgba(255,255,255,0.1); }
    .content { margin-left:260px; padding:20px; }
    .call911 { background:#d9534f; color:white; border:none; padding:10px 16px; border-radius:6px; width:100%; margin-top:10px; }
    .card h3 { margin:0; font-weight:bold; }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <h4>Teacher Panel</h4>
    <p>Welcome, <?= htmlspecialchars($user['name']) ?></p>
    <a href="teacher_dashboard.php">📊 Dashboard</a>
    <a href="incidents.php">📝 Report Incident</a>
    <a href="evacuation_plans.php">📄 Evacuation Plans</a>
    <a href="drills.php">📅 Upcoming Drills</a>
    <hr>
    <button id="call911" class="call911">📞 Call 911</button>
    <a href="logout.php" class="d-block mt-2">🚪 Logout</a>
  </div>

  <!-- Content -->
  <div class="content">
    <h3 class="mb-4">Teacher Dashboard</h3>
    <p class="text-muted">Here you can report incidents, monitor evacuation plans, and view scheduled drills.</p>
    
    <div class="row g-3">
      <div class="col-md-4">
        <div class="card p-3 shadow-sm">
          <h6>Incidents Reported</h6>
          <?php
            $count = 0;
            if ($conn) {
              $res = $conn->query("SELECT COUNT(*) AS c FROM incidents");
              if ($res) { $count = $res->fetch_assoc()['c']; }
            }
            echo "<h3>{$count}</h3>";
          ?>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card p-3 shadow-sm">
          <h6>Evacuation Plans</h6>
          <?php
            $count = 0;
            if ($conn) {
              $res = $conn->query("SELECT COUNT(*) AS c FROM evacuation_plans");
              if ($res) { $count = $res->fetch_assoc()['c']; }
            }
            echo "<h3>{$count}</h3>";
          ?>
        </div>
      </div>
    </div>
  </div>

<script>
document.getElementById('call911').addEventListener('click', async function(){
  let name = "<?= htmlspecialchars($user['name']) ?>";
  let contact = prompt("Contact number (optional):", "");
  let location = prompt("Location (optional):", "");
  let message = prompt("Short message:", "Emergency - please respond");
  try {
    const resp = await fetch('emergency.php', {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({name, contact, location, message})
    });
    const j = await resp.json();
    if(j.success){ alert('🚨 Emergency logged. Calling 911...'); window.location.href="tel:911"; }
    else{ alert('❌ Error: '+(j.message||'unknown')); }
  } catch(e){ alert('⚠️ Error: '+e.message); }
});
</script>
</body>
</html>

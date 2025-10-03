
<?php
session_start();
require_once "db_connect.php";

// Protect page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Current module
$module = strtolower($_GET['m'] ?? 'dashboard');
$user   = $_SESSION['user_name'] ?? "Guest";

// ---------- QUICK COUNTS FUNCTION ----------
function getCount($conn, $table) {
    $res = $conn->query("SELECT COUNT(*) AS c FROM `$table`");
    return $res ? $res->fetch_assoc()['c'] : 0;
}

// Counts for dashboard cards (make sure table names match your DB schema)
$counts = [
    'Contacts'          => getCount($conn,'contacts'),
    'Drills'            => getCount($conn,'drills'),
    'Evacuation Plans'  => getCount($conn,'evacuation_plans'),
    'Incidents'         => getCount($conn,'incidents'),
    'Safety Checklist'  => getCount($conn,'safety_checklist'),
    'Risk'             => getCount($conn,'risk'),
    'Notifications'     => getCount($conn,'notifications'),
    'Compliance'        => getCount($conn,'compliance_reports'),
    'Roles'             => getCount($conn,'roles'),
    'Supplies'          => getCount($conn,'first_aid_supplies'),
    'Inspections'       => getCount($conn,'inspections'),
];

// Map modules to files
$moduleFiles = [
    'contacts'         => 'contacts.php',
    'drills'           => 'drills.php',
    'evacuation_plans' => 'evacuation_plans.php',
    'incidents'        => 'incidents.php',
    'safety_checklist' => 'safety_checklist.php',
    'risk'            => 'risk.php',
    'notify'           => 'notifications.php',
    'compliance'       => 'compliance.php',
    'roles'            => 'roles.php',
    'supplies'         => 'supplies.php',
    'inspections'      => 'inspections.php',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>OSAS Safety Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

<style>
:root {
  --gradient: linear-gradient(135deg,#8b5cf6,#3b82f6);
  --card-bg: #ffffff;
  --card-shadow: rgba(0,0,0,0.08);
  --accent-purple: #8b5cf6;
  --accent-blue: #3b82f6;
}
body { margin:0; background:#f3f4f6; font-family:"Poppins",sans-serif; }
/* Sidebar */
.sidebar { position:fixed; top:0; left:0; height:100vh; width:250px; background:var(--gradient); padding-top:1rem; color:#fff; }
.sidebar .brand { text-align:center; margin-bottom:1rem; }
.sidebar .brand img { width:60px; height:60px; }
.sidebar h4 { font-weight:600; margin-top:.5rem; color:#fff; }
.sidebar a { display:block; padding:.75rem 1rem; color:#f0f0f0; text-decoration:none; font-weight:500; transition:0.2s; }
.sidebar a:hover, .sidebar .active { background: rgba(255,255,255,0.2); color:#fff; border-radius:6px; }
/* Navbar */
.navbar { margin-left:250px; background:var(--gradient); color:#fff; box-shadow:0 2px 6px rgba(0,0,0,0.08); }
.navbar h5 { font-weight:600; }
/* Content */
.content { margin-left:250px; padding:2rem; }
/* Cards */
.card { border:none; background:var(--card-bg); box-shadow:0 4px 10px rgba(0,0,0,0.08); border-radius:.75rem; transition:transform 0.2s; }
.card:hover { transform:translateY(-3px); }
.card h6 { color:var(--accent-blue); font-weight:600; }
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <div class="brand">
    <img src="logo.png" alt="Logo">
    <h4>OSAS Safety</h4>
  </div>
  <a href="?m=dashboard" class="<?= $module==='dashboard'?'active':'' ?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
  <a href="?m=contacts" class="<?= $module==='contacts'?'active':'' ?>"><i class="bi bi-telephone"></i> Emergency Contacts</a>
  <a href="?m=drills" class="<?= $module==='drills'?'active':'' ?>"><i class="bi bi-calendar-event"></i> Drill Scheduling</a>
  <a href="?m=evacuation_plans" class="<?= $module==='evacuation_plans'?'active':'' ?>"><i class="bi bi-map"></i> Evacuation Plans</a>
  <a href="?m=incidents" class="<?= $module==='incidents'?'active':'' ?>"><i class="bi bi-exclamation-triangle"></i> Incidents</a>
  <a href="?m=safety_checklist" class="<?= $module==='safety_checklist'?'active':'' ?>"><i class="bi bi-list-check"></i> Inspection Checklist</a>
  <a href="?m=risk" class="<?= $module==='risk'?'active':'' ?>"><i class="bi bi-graph-down"></i> Risk Assessment</a>
  <a href="?m=notify" class="<?= $module==='notify'?'active':'' ?>"><i class="bi bi-bell"></i> Notifications</a>
  <a href="?m=compliance" class="<?= $module==='compliance'?'active':'' ?>"><i class="bi bi-file-earmark-text"></i> Compliance Reports</a>
  <a href="?m=roles" class="<?= $module==='roles'?'active':'' ?>"><i class="bi bi-people"></i> Emergency Roles</a>
  <a href="?m=supplies" class="<?= $module==='supplies'?'active':'' ?>"><i class="bi bi-heart-pulse"></i> First Aid Supplies</a>
  <hr class="bg-light">
  <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg">
  <div class="container-fluid">
     <h5 class="mb-0">Disaster Preparedness & Safety Dashboard</h5>
     <span class="ms-auto">👤 <?= htmlspecialchars($user) ?></span>
  </div>
</nav>

<!-- Main Content -->
<div class="content">
<?php
if ($module==='dashboard') {
    // DASHBOARD CONTENT
    ?>
    <h3 class="mb-4">System Overview</h3>
    <div class="row g-3">
      <?php foreach ($counts as $label=>$num): ?>
        <div class="col-md-3">
          <div class="card p-3 text-center">
            <i class="bi bi-circle-fill mb-2" style="color:var(--accent-purple)"></i>
            <h6 class="text-uppercase small mb-2"><?= $label ?></h6>
            <span class="fs-3 fw-bold" style="color:var(--accent-blue)"><?= $num ?></span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="row mt-4">
      <div class="col-md-6">
        <div class="card p-3">
          <canvas id="barChart" height="200"></canvas>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card p-3">
          <canvas id="pieChart" height="200"></canvas>
        </div>
      </div>
    </div>
    <script>
      const labels = <?= json_encode(array_keys($counts)) ?>;
      const data = <?= json_encode(array_values($counts)) ?>;
      new Chart(document.getElementById('barChart'), {
        type:'bar',
        data:{ labels, datasets:[{label:'Records', data, backgroundColor:'var(--accent-blue)'}] },
        options:{responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}}}
      });
      new Chart(document.getElementById('pieChart'), {
        type:'pie',
        data:{ labels, datasets:[{data, backgroundColor:['#3b82f6','#8b5cf6','#6366f1','#a78bfa','#c084fc','#d8b4fe','#93c5fd','#60a5fa','#1d4ed8','#1e40af','#312e81']}] },
        options:{responsive:true}
      });
    </script>
    <?php
} elseif (isset($moduleFiles[$module])) {
    $file = $moduleFiles[$module];
    if (file_exists($file)) {
        include $file;
    } else {
        echo "<div class='alert alert-danger'>Module file not found: ".htmlspecialchars($file)."</div>";
    }
} else {
    echo "<div class='alert alert-danger'>Module not found: ".htmlspecialchars($module)."</div>";
}
?>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
</body>
</html>



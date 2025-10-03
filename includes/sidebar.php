<!-- sidebar.php -->
<nav class="col-md-2 d-md-block sidebar p-3">
  <h4 class="logo-title">OSAS</h4>
  <p class="small">Disaster Preparedness & Safety System</p>
  <ul class="nav flex-column">
    <li class="nav-item"><a href="index.php?m=dashboard" class="nav-link active"><i class="fa fa-chart-line"></i> Dashboard</a></li>
    <li class="nav-item"><a href="index.php?m=contacts" class="nav-link"><i class="fa fa-address-book"></i> Emergency Contacts</a></li>
    <li class="nav-item"><a href="index.php?m=drills" class="nav-link"><i class="fa fa-calendar"></i> Drill Scheduling</a></li>
    <li class="nav-item"><a href="index.php?m=evacuation_plans" class="nav-link"><i class="fa fa-map"></i> Evacuation Plans</a></li>
    <li class="nav-item"><a href="index.php?m=incidents" class="nav-link"><i class="fa fa-file-alt"></i> Incident Logging</a></li>
    <li class="nav-item"><a href="index.php?m=checklist" class="nav-link"><i class="fa fa-check-square"></i> Safety Checklist</a></li>
    <li class="nav-item"><a href="index.php?m=risk" class="nav-link"><i class="fa fa-exclamation-triangle"></i> Risk Assessment</a></li>
    <li class="nav-item"><a href="index.php?m=notifications" class="nav-link"><i class="fa fa-envelope"></i> Parent Notifications</a></li>
    <li class="nav-item"><a href="index.php?m=compliance" class="nav-link"><i class="fa fa-clipboard-list"></i> Compliance Reports</a></li>
    <li class="nav-item"><a href="index.php?m=roles" class="nav-link"><i class="fa fa-users-cog"></i> Role Assignment</a></li>
    <li class="nav-item"><a href="index.php?m=supplies" class="nav-link"><i class="fa fa-medkit"></i> First Aid Monitor</a></li>
  </ul>
  <hr>
  <button class="btn btn-danger w-100"><i class="fa fa-phone"></i> Call 911</button>
</nav>

<!-- index.css -->
<style>
.sidebar {
  background: linear-gradient(180deg, #6a5acd, #1e3a8a); /* purple → deep blue */
  color: #fff;
  min-height: 100vh;
  box-shadow: 2px 0 6px rgba(0,0,0,0.2);
}

.sidebar h4.logo-title {
  color: #fff;
  font-weight: 700;
  text-align: center;
  margin-bottom: 5px;
  letter-spacing: 1px;
}

.sidebar p.small {
  text-align: center;
  color: #cbd5e1;
  margin-bottom: 20px;
}

.sidebar .nav-link {
  color: #e0e7ff;
  padding: 10px 15px;
  border-radius: 6px;
  transition: background 0.3s, color 0.3s;
  font-weight: 500;
}

.sidebar .nav-link i {
  margin-right: 8px;
}

.sidebar .nav-link:hover,
.sidebar .nav-link.active {
  background: rgba(255, 255, 255, 0.2); /* soft highlight */
  color: #fff;
  font-weight:

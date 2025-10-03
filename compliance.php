<?php
include __DIR__ . "/db_connect.php";

// Predefined certificates
$certificates = [
    "Certificate of Occupancy",
    "Copy of Land Ownership",
    "Campus Site and Location Map",
    "Barangay Business Clearance",
    "Permit to Operate",
    "Building Insurance",
    "Sanitary Permit",
    "Health Certification of Potable Water",
    "Pest Control Certification and Report",
    "Certificate of Annual Electrical Inspection",
    "Fire Safety Inspection Certificate",
    "Tax Declaration of Real Property",
    "Plumbing/Sanitary Certificate",
    "Building Permit"
];

// AJAX handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $id = intval($_POST['id'] ?? 0);
    $certificate = $_POST['certificate'] ?? '';
    $date = $_POST['date'] ?? date('Y-m-d');

    $file_path = '';
    if (!empty($_FILES['file']['name'])) {
        $filename = time() . '_' . basename($_FILES['file']['name']);
        $target = __DIR__ . '/uploads/' . $filename;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
            $file_path = 'uploads/' . $filename;
        } else { echo "File upload failed"; exit; }
    }

    if ($_POST['action'] === 'add') {
        $stmt = $conn->prepare("INSERT INTO compliance_reports (report_name, file_path, date) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $certificate, $file_path, $date);
        $stmt->execute();
        $stmt->close();
        echo "success";
    } elseif ($_POST['action'] === 'update') {
        if ($file_path) {
            $stmt = $conn->prepare("UPDATE compliance_reports SET report_name=?, file_path=?, date=? WHERE id=?");
            $stmt->bind_param("sssi", $certificate, $file_path, $date, $id);
        } else {
            $stmt = $conn->prepare("UPDATE compliance_reports SET report_name=?, date=? WHERE id=?");
            $stmt->bind_param("ssi", $certificate, $date, $id);
        }
        $stmt->execute();
        $stmt->close();
        echo "success";
    } elseif ($_POST['action'] === 'delete') {
        $stmt = $conn->prepare("DELETE FROM compliance_reports WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        echo "success";
    }
    exit;
}

// Fetch all items
$result = $conn->query("SELECT * FROM compliance_reports ORDER BY date DESC");
?>

<div class="container-fluid mt-4">
  <div class="row">
    <!-- Left sidebar: list of certificates -->
    <div class="col-md-3">
      <div class="card p-3 mb-3">
        <h6>Compliance Reports</h6>
        <ul class="list-group" id="certificateList">
          <?php if ($result->num_rows > 0): $result->data_seek(0); ?>
            <?php while($row = $result->fetch_assoc()): ?>
              <li class="list-group-item" onclick="scrollToRow(<?= $row['id'] ?>)">
                <?= htmlspecialchars($row['report_name']) ?>
              </li>
            <?php endwhile; ?>
          <?php else: ?>
            <li class="list-group-item text-center">No certificates uploaded</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>

    <!-- Right content: Add button + table -->
    <div class="col-md-9">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h5>Certificates</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#complianceModal" onclick="openAdd()">+ Add Certificate</button>
      </div>

      <div class="card p-3">
        <table class="table table-bordered table-striped" id="complianceTable">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>Certificate</th>
              <th>File</th>
              <th>Date</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php $result->data_seek(0); ?>
            <?php if ($result->num_rows > 0): ?>
              <?php while($row = $result->fetch_assoc()): ?>
                <tr id="row_<?= $row['id'] ?>">
                  <td><?= $row['id'] ?></td>
                  <td><?= htmlspecialchars($row['report_name']) ?></td>
                  <td>
                    <?php if ($row['file_path']): ?>
                      <a href="<?= $row['file_path'] ?>" target="_blank">View File</a>
                    <?php else: ?>
                      N/A
                    <?php endif; ?>
                  </td>
                  <td><?= $row['date'] ?></td>
                  <td>
                    <button class="btn btn-sm btn-warning" onclick="openEdit(<?= $row['id'] ?>,'<?= htmlspecialchars($row['report_name'],ENT_QUOTES) ?>','<?= $row['date'] ?>')"><i class="fa fa-edit"></i> Edit</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteItem(<?= $row['id'] ?>)"><i class="fa fa-trash"></i> Delete</button>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="5" class="text-center">No certificates uploaded.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="complianceModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="complianceForm" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Add Certificate</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="certificate_id">
          <input type="hidden" name="ajax" value="1">
          <input type="hidden" name="action" id="form_action" value="add">

          <div class="mb-3">
            <label>Certificate</label>
            <select name="certificate" id="certificate_name" class="form-select" required>
              <option value="">-- Select Certificate --</option>
              <?php foreach ($certificates as $cert): ?>
                <option value="<?= $cert ?>"><?= $cert ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label>Upload File</label>
            <input type="file" name="file" id="certificate_file" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Date</label>
            <input type="date" name="date" id="certificate_date" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Save</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openAdd() {
  document.getElementById("complianceForm").reset();
  document.getElementById("certificate_id").value = "";
  document.getElementById("form_action").value = "add";
  document.getElementById("modalTitle").innerText = "Add Certificate";
}

function openEdit(id, name, date) {
  document.getElementById("certificate_id").value = id;
  document.getElementById("certificate_name").value = name;
  document.getElementById("certificate_date").value = date;
  document.getElementById("form_action").value = "update";
  document.getElementById("modalTitle").innerText = "Edit Certificate";
  var modal = new bootstrap.Modal(document.getElementById('complianceModal'));
  modal.show();
}

document.getElementById("complianceForm").addEventListener("submit", function(e){
  e.preventDefault();
  let formData = new FormData(this);
  fetch("compliance.php", { method:"POST", body: formData })
    .then(res=>res.text())
    .then(data=>{
      if(data.trim() === "success"){
        location.reload();
      } else { alert(data); }
    });
});

function deleteItem(id){
  if(confirm("Are you sure you want to delete this certificate?")){
    let formData = new FormData();
    formData.append("ajax", 1);
    formData.append("action", "delete");
    formData.append("id", id);
    fetch("compliance.php", { method:"POST", body: formData })
      .then(res=>res.text())
      .then(data=>{
        if(data.trim() === "success"){
          document.getElementById("row_"+id).remove();
        } else { alert(data); }
      });
  }
}

function scrollToRow(id){
  let row = document.getElementById("row_" + id);
  if(row){
    row.scrollIntoView({behavior:"smooth", block:"center"});
    row.style.backgroundColor = "#fff3cd"; // highlight briefly
    setTimeout(()=>row.style.backgroundColor="", 2000);
  }
}
</script>

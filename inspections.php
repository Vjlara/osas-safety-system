<?php
include "../db_connect.php";

// Add checklist item
if (isset($_POST['add'])) {
    $item = $_POST['item_name'];
    $status = $_POST['status'];
    $date = $_POST['date'];
    $notes = $_POST['notes'];

    $stmt = $conn->prepare("INSERT INTO safety_checklist (item_name, status, date, notes) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $item, $status, $date, $notes);
    $stmt->execute();
    $stmt->close();
    header("Location: inspections.php");
    exit;
}

// Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM safety_checklist WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: inspections.php");
    exit;
}

$result = $conn->query("SELECT * FROM safety_checklist ORDER BY created_at DESC");
if (!$result) { die("DB error: " . $conn->error); }
?>

<?php include "../includes/header.php"; ?>
<?php include "../includes/sidebar.php"; ?>

<main class="col-md-10">
  <div class="topbar d-flex justify-content-between align-items-center">
    <h5>Safety Inspection Checklist</h5>
    <span><i class="fa fa-user"></i> Admin</span>
  </div>

  <div class="container mt-4">
    <div class="card p-3 mb-4">
      <h6>Add Checklist Item</h6>
      <form method="post" class="row g-3">
        <div class="col-md-5"><input type="text" name="item_name" class="form-control" placeholder="Item Name" required></div>
        <div class="col-md-3">
          <select name="status" class="form-select">
            <option value="Incomplete">Incomplete</option>
            <option value="Complete">Complete</option>
          </select>
        </div>
        <div class="col-md-3"><input type="date" name="date" class="form-control"></div>
        <div class="col-md-12"><textarea name="notes" class="form-control" rows="2" placeholder="Notes (optional)"></textarea></div>
        <div class="col-md-1"><button type="submit" name="add" class="btn btn-primary w-100">Add</button></div>
      </form>
    </div>

    <div class="card p-3">
      <h6>Checklist</h6>
      <table class="table table-bordered table-striped mt-3">
        <thead class="table-dark"><tr><th>ID</th><th>Item</th><th>Status</th><th>Date</th><th>Notes</th><th>Action</th></tr></thead>
        <tbody>
          <?php if ($result->num_rows > 0): while($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= htmlspecialchars($row['item_name']) ?></td>
              <td>
                <?php if($row['status']=='Complete'): ?><span class="badge bg-success">Complete</span>
                <?php else: ?><span class="badge bg-danger">Incomplete</span><?php endif; ?>
              </td>
              <td><?= $row['date'] ?></td>
              <td><?= nl2br(htmlspecialchars($row['notes'])) ?></td>
              <td><a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete item?')"><i class="fa fa-trash"></i></a></td>
            </tr>
          <?php endwhile; else: ?>
            <tr><td colspan="6" class="text-center">No checklist items.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<?php include "../includes/footer.php"; ?>

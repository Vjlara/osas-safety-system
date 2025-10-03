<?php
include "db_connect.php"; // DB connection

// ---------- ADD ROLE ----------
if (isset($_POST['add'])) {
    $staff   = $_POST['staff_name'] ?? '';
    $role    = $_POST['role'] ?? '';
    $contact = $_POST['contact'] ?? '';

    $stmt = $conn->prepare("INSERT INTO roles (staff_name, role, contact) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $staff, $role, $contact);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php?page=roles");
    exit;
}

// ---------- UPDATE ROLE ----------
if (isset($_POST['update'])) {
    $id      = intval($_POST['id'] ?? 0);
    $staff   = $_POST['staff_name'] ?? '';
    $role    = $_POST['role'] ?? '';
    $contact = $_POST['contact'] ?? '';

    $stmt = $conn->prepare("UPDATE roles SET staff_name=?, role=?, contact=? WHERE id=?");
    $stmt->bind_param("sssi", $staff, $role, $contact, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php?page=roles");
    exit;
}

// ---------- DELETE ROLE ----------
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM roles WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php?page=roles");
    exit;
}

// ---------- FETCH ROLES ----------
$result = $conn->query("SELECT * FROM roles ORDER BY created_at DESC");
if (!$result) die("DB error: " . $conn->error);

// ---------- FETCH EDIT DATA ----------
$editData = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM roles WHERE id=$id");
    if ($res && $res->num_rows > 0) {
        $editData = $res->fetch_assoc();
    }
}
?>

<div class="container mt-4">
  <!-- Add/Edit Role Form -->
  <div class="card p-3 mb-4">
    <h6><?= $editData ? "Edit Role" : "Assign Role" ?></h6>
    <form method="post" class="row g-3">
      <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">

      <div class="col-md-4">
        <input type="text" name="staff_name" class="form-control" placeholder="Staff Name"
               value="<?= htmlspecialchars($editData['staff_name'] ?? '') ?>" required>
      </div>

      <div class="col-md-4">
        <input type="text" name="role" class="form-control" placeholder="Role (e.g. Evacuation Lead)"
               value="<?= htmlspecialchars($editData['role'] ?? '') ?>" required>
      </div>

      <div class="col-md-3">
        <input type="text" name="contact" class="form-control" placeholder="Contact (phone/email)"
               value="<?= htmlspecialchars($editData['contact'] ?? '') ?>">
      </div>

      <div class="col-md-1">
        <?php if ($editData): ?>
          <button type="submit" name="update" class="btn btn-warning w-100">Update</button>
        <?php else: ?>
          <button type="submit" name="add" class="btn btn-primary w-100">Add</button>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- Roles Table -->
  <div class="card p-3">
    <h6>Assigned Roles</h6>
    <table class="table table-bordered table-striped mt-3">
      <thead class="table-dark">
        <tr><th>ID</th><th>Staff</th><th>Role</th><th>Contact</th><th>Action</th></tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows > 0): ?>
          <?php while($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= htmlspecialchars($row['staff_name'] ?? '') ?></td>
              <td><?= htmlspecialchars($row['role'] ?? '') ?></td>
              <td><?= htmlspecialchars($row['contact'] ?? '') ?></td>
              <td>
                <a href="index.php?page=roles&edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning"><i class="fa fa-edit"></i></a>
                <a href="index.php?page=roles&delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
                   onclick="return confirm('Delete role?')"><i class="fa fa-trash"></i></a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="5" class="text-center">No roles assigned.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>


<?php
require_once "db_connect.php";

// Handle Add
if (isset($_POST['add'])) {
    $role_name       = trim($_POST['role_name'] ?? '');
    $assigned_person = trim($_POST['assigned_person'] ?? '');
    $staff_name      = trim($_POST['staff_name'] ?? '');
    $role            = trim($_POST['role'] ?? '');
    $contact         = trim($_POST['contact'] ?? '');

    if ($role_name && $staff_name && $role) {
        $stmt = $conn->prepare("INSERT INTO roles (role_name, assigned_person, staff_name, role, contact) VALUES (?,?,?,?,?)");
        $stmt->bind_param("sssss", $role_name, $assigned_person, $staff_name, $role, $contact);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: index.php?m=roles");
    exit;
}

// Handle Update
if (isset($_POST['update'])) {
    $id              = intval($_POST['id'] ?? 0);
    $role_name       = trim($_POST['role_name'] ?? '');
    $assigned_person = trim($_POST['assigned_person'] ?? '');
    $staff_name      = trim($_POST['staff_name'] ?? '');
    $role            = trim($_POST['role'] ?? '');
    $contact         = trim($_POST['contact'] ?? '');

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE roles SET role_name=?, assigned_person=?, staff_name=?, role=?, contact=? WHERE id=?");
        $stmt->bind_param("sssssi", $role_name, $assigned_person, $staff_name, $role, $contact, $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: index.php?m=roles");
    exit;
}

// Handle Delete
if (isset($_POST['delete'])) {
    $id = intval($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM roles WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: index.php?m=roles");
    exit;
}

// Fetch all roles
$result = $conn->query("SELECT * FROM roles ORDER BY id DESC");
?>

<div class="container mt-4">
  <div class="d-flex justify-content-between mb-3">
    <h2>Emergency Role Assignment</h2>
    <!-- Button trigger Add Modal -->
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">Add Role</button>
  </div>

  <!-- Roles Table -->
  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Role Name</th>
        <th>Assigned Person</th>
        <th>Staff Name</th>
        <th>Role</th>
        <th>Contact</th>
        <th>Created At</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['role_name']) ?></td>
        <td><?= htmlspecialchars($row['assigned_person']) ?></td>
        <td><?= htmlspecialchars($row['staff_name']) ?></td>
        <td><?= htmlspecialchars($row['role']) ?></td>
        <td><?= htmlspecialchars($row['contact']) ?></td>
        <td><?= $row['created_at'] ?></td>
        <td>
          <!-- Edit Button -->
          <button class="btn btn-sm btn-warning" 
                  data-bs-toggle="modal" 
                  data-bs-target="#editModal<?= $row['id'] ?>">Edit</button>
          <!-- Delete Button -->
          <button class="btn btn-sm btn-danger" 
                  data-bs-toggle="modal" 
                  data-bs-target="#deleteModal<?= $row['id'] ?>">Delete</button>
        </td>
      </tr>

      <!-- Edit Modal -->
      <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <form method="POST">
              <div class="modal-header">
                <h5 class="modal-title">Edit Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <div class="mb-2">
                  <label>Role Name</label>
                  <input type="text" name="role_name" class="form-control" value="<?= htmlspecialchars($row['role_name']) ?>" required>
                </div>
                <div class="mb-2">
                  <label>Assigned Person</label>
                  <input type="text" name="assigned_person" class="form-control" value="<?= htmlspecialchars($row['assigned_person']) ?>">
                </div>
                <div class="mb-2">
                  <label>Staff Name</label>
                  <input type="text" name="staff_name" class="form-control" value="<?= htmlspecialchars($row['staff_name']) ?>" required>
                </div>
                <div class="mb-2">
                  <label>Role</label>
                  <input type="text" name="role" class="form-control" value="<?= htmlspecialchars($row['role']) ?>" required>
                </div>
                <div class="mb-2">
                  <label>Contact</label>
                  <input type="text" name="contact" class="form-control" value="<?= htmlspecialchars($row['contact']) ?>">
                </div>
              </div>
              <div class="modal-footer">
                <button type="submit" name="update" class="btn btn-success">Update</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Delete Modal -->
      <div class="modal fade" id="deleteModal<?= $row['id'] ?>" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <form method="POST">
              <div class="modal-header">
                <h5 class="modal-title">Delete Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                Are you sure you want to delete <strong><?= htmlspecialchars($row['role_name']) ?></strong>?
              </div>
              <div class="modal-footer">
                <button type="submit" name="delete" class="btn btn-danger">Delete</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    <?php endwhile; ?>
    </tbody>
  </table>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Add New Role</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label>Role Name</label>
            <input type="text" name="role_name" class="form-control" required>
          </div>
          <div class="mb-2">
            <label>Assigned Person</label>
            <input type="text" name="assigned_person" class="form-control">
          </div>
          <div class="mb-2">
            <label>Staff Name</label>
            <input type="text" name="staff_name" class="form-control" required>
          </div>
          <div class="mb-2">
            <label>Role</label>
            <input type="text" name="role" class="form-control" required>
          </div>
          <div class="mb-2">
            <label>Contact</label>
            <input type="text" name="contact" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="add" class="btn btn-primary">Add</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>


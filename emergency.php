<?php
require_once "db_connect.php";

// Handle Add
if (isset($_POST['add'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $relation = trim($_POST['relation']);

    $stmt = $conn->prepare("INSERT INTO emergency_contacts (name, phone, relation) VALUES (?,?,?)");
    $stmt->bind_param("sss", $name, $phone, $relation);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php?m=contacts");
    exit;
}

// Handle Update
if (isset($_POST['edit'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $relation = trim($_POST['relation']);

    $stmt = $conn->prepare("UPDATE emergency_contacts SET name=?, phone=?, relation=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $phone, $relation, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php?m=contacts");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM emergency_contacts WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php?m=contacts");
    exit;
}

// Fetch contacts
$result = $conn->query("SELECT * FROM emergency_contacts ORDER BY id DESC");
?>

<div class="container">
  <h2 class="mb-3">Emergency Contact Database</h2>

  <!-- Add Form -->
  <form method="POST" class="row g-2 mb-4">
    <div class="col-md-3">
      <input type="text" name="name" class="form-control" placeholder="Name" required>
    </div>
    <div class="col-md-3">
      <input type="text" name="phone" class="form-control" placeholder="Phone" required>
    </div>
    <div class="col-md-3">
      <input type="text" name="relation" class="form-control" placeholder="Relation" required>
    </div>
    <div class="col-md-3">
      <button type="submit" name="add" class="btn btn-primary w-100">Add Contact</button>
    </div>
  </form>

  <!-- Contacts Table -->
  <table class="table table-striped table-bordered">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Phone</th>
        <th>Relation</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    <?php while($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['phone']) ?></td>
        <td><?= htmlspecialchars($row['relation']) ?></td>
        <td>
          <!-- Edit -->
          <form method="POST" class="d-inline">
            <input type="hidden" name="id" value="<?= $row['id'] ?>">
            <input type="hidden" name="name" value="<?= htmlspecialchars($row['name']) ?>">
            <input type="hidden" name="phone" value="<?= htmlspecialchars($row['phone']) ?>">
            <input type="hidden" name="relation" value="<?= htmlspecialchars($row['relation']) ?>">
            <button type="submit" name="edit" class="btn btn-sm btn-warning">Edit</button>
          </form>
          <!-- Delete -->
          <a href="index.php?m=contacts&delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
             onclick="return confirm('Delete this contact?')">Delete</a>
        </td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
</div>

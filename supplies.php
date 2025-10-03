<?php
include "db_connect.php";

// ---------- ADD SUPPLY ----------
if (isset($_POST['add'])) {
    $item     = $_POST['item_name'];
    $quantity = $_POST['quantity'];
    $unit     = $_POST['unit'] ?? '';
    $restock  = $_POST['restock_date'] ?? null;

    $stmt = $conn->prepare("INSERT INTO first_aid_supplies (item_name, quantity, unit, restock_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siss", $item, $quantity, $unit, $restock);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php?page=supplies");
    exit;
}

// ---------- UPDATE SUPPLY ----------
if (isset($_POST['update'])) {
    $id       = $_POST['id'];
    $item     = $_POST['item_name'];
    $quantity = $_POST['quantity'];
    $unit     = $_POST['unit'] ?? '';
    $restock  = $_POST['restock_date'] ?? null;

    $stmt = $conn->prepare("UPDATE first_aid_supplies SET item_name=?, quantity=?, unit=?, restock_date=? WHERE id=?");
    $stmt->bind_param("sissi", $item, $quantity, $unit, $restock, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php?page=supplies");
    exit;
}

// ---------- DELETE SUPPLY ----------
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM first_aid_supplies WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php?page=supplies");
    exit;
}

// ---------- FETCH SUPPLIES ----------
$result = $conn->query("SELECT * FROM first_aid_supplies ORDER BY id ASC");
?>

<div class="container mt-4">
  <!-- Add Supply -->
  <div class="card p-3 mb-4">
    <h6>Add New Supply</h6>
    <form method="post" class="row g-3">
      <div class="col-md-4">
        <input type="text" name="item_name" class="form-control" placeholder="Item Name" required>
      </div>
      <div class="col-md-2">
        <input type="number" name="quantity" class="form-control" placeholder="Quantity" required>
      </div>
      <div class="col-md-3">
        <input type="text" name="unit" class="form-control" placeholder="Unit (pcs, bottles, etc)">
      </div>
      <div class="col-md-3">
        <input type="date" name="restock_date" class="form-control" placeholder="Restock Date">
      </div>
      <div class="col-md-2">
        <button type="submit" name="add" class="btn btn-primary w-100">Add</button>
      </div>
    </form>
  </div>

  <!-- Supplies Table -->
  <div class="card p-3">
    <h6>Supplies Inventory</h6>
    <table class="table table-bordered table-striped mt-3">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Item</th>
          <th>Quantity</th>
          <th>Unit</th>
          <th>Restock Date</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['item_name']) ?></td>
          <td><?= $row['quantity'] ?></td>
          <td><?= htmlspecialchars($row['unit'] ?? '') ?></td>
          <td><?= $row['restock_date'] ?? '' ?></td>
          <td>
            <!-- Edit Button -->
            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">
              <i class="fa fa-edit"></i> Edit
            </button>

            <!-- Delete Button -->
            <a href="index.php?page=supplies&delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
               onclick="return confirm('Delete this supply?')">
              <i class="fa fa-trash"></i> Delete
            </a>
          </td>
        </tr>

        <!-- Edit Modal -->
        <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="post">
                <div class="modal-header">
                  <h5 class="modal-title">Edit Supply</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" name="id" value="<?= $row['id'] ?>">
                  <div class="mb-3">
                    <label>Item Name</label>
                    <input type="text" name="item_name" class="form-control" value="<?= htmlspecialchars($row['item_name']) ?>" required>
                  </div>
                  <div class="mb-3">
                    <label>Quantity</label>
                    <input type="number" name="quantity" class="form-control" value="<?= $row['quantity'] ?>" required>
                  </div>
                  <div class="mb-3">
                    <label>Unit</label>
                    <input type="text" name="unit" class="form-control" value="<?= htmlspecialchars($row['unit'] ?? '') ?>">
                  </div>
                  <div class="mb-3">
                    <label>Restock Date</label>
                    <input type="date" name="restock_date" class="form-control" value="<?= $row['restock_date'] ?? '' ?>">
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="submit" name="update" class="btn btn-success">Save Changes</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

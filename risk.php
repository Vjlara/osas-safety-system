<?php
include "db_connect.php";

// Add new item
if(isset($_POST['add'])) {
    $category = $_POST['category'] ?? '';
    $subtask  = $_POST['subtask'] ?? '';
    $response = $_POST['response'] ?? '';
    $details  = $_POST['details'] ?? '';

    $stmt = $conn->prepare("INSERT INTO risk (category, subtask, response, details) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $category, $subtask, $response, $details);
    $stmt->execute();
    $stmt->close();

    header("Location: ?m=risk");
    exit;
}

// Update existing item
if(isset($_POST['update'])) {
    $id       = intval($_POST['id'] ?? 0);
    $category = $_POST['category'] ?? '';
    $subtask  = $_POST['subtask'] ?? '';
    $response = $_POST['response'] ?? '';
    $details  = $_POST['details'] ?? '';

    $stmt = $conn->prepare("UPDATE risk SET category=?, subtask=?, response=?, details=? WHERE id=?");
    $stmt->bind_param("ssssi", $category, $subtask, $response, $details, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: ?m=risk");
    exit;
}

// Delete item
if(isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM risk WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: ?m=risk");
    exit;
}

// Fetch all items
$result = $conn->query("SELECT * FROM risk ORDER BY category, id ASC");
?>

<div class="container mt-4">
    <h3>Risk Assessment Tool</h3>

    <!-- Add Item Button aligned right -->
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">Add Checklist Item</button>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Checklist Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Category / Area</label>
                            <input type="text" name="category" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Subtask / Item</label>
                            <textarea name="subtask" class="form-control" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label>Inspection Response</label>
                            <select name="response" class="form-select" required>
                                <option value="Good">Good</option>
                                <option value="Needs Action">Needs Action</option>
                                <option value="N/A">N/A</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Details / Notes</label>
                            <textarea name="details" class="form-control"></textarea>
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

    <!-- Checklist Table -->
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Category / Area</th>
                <th>Subtask / Item</th>
                <th>Response</th>
                <th>Details</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if($result->num_rows>0): while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td><?= htmlspecialchars($row['subtask']) ?></td>
                <td>
                    <?php
                        if($row['response']=='Good') echo '<span class="badge bg-success">Good</span>';
                        elseif($row['response']=='Needs Action') echo '<span class="badge bg-warning text-dark">Needs Action</span>';
                        else echo '<span class="badge bg-secondary">N/A</span>';
                    ?>
                </td>
                <td><?= htmlspecialchars($row['details']) ?></td>
                <td>
                    <!-- Edit Button triggers Modal -->
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">Edit</button>
                    <a href="?m=risk&delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this item?')">Delete</a>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="post">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Checklist Item</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <div class="mb-3">
                                            <label>Category / Area</label>
                                            <input type="text" name="category" class="form-control" value="<?= htmlspecialchars($row['category']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>Subtask / Item</label>
                                            <textarea name="subtask" class="form-control" required><?= htmlspecialchars($row['subtask']) ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label>Inspection Response</label>
                                            <select name="response" class="form-select" required>
                                                <option value="Good" <?= $row['response']=='Good'?'selected':'' ?>>Good</option>
                                                <option value="Needs Action" <?= $row['response']=='Needs Action'?'selected':'' ?>>Needs Action</option>
                                                <option value="N/A" <?= $row['response']=='N/A'?'selected':'' ?>>N/A</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label>Details / Notes</label>
                                            <textarea name="details" class="form-control"><?= htmlspecialchars($row['details']) ?></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" name="update" class="btn btn-primary">Update</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="6" class="text-center">No checklist items found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

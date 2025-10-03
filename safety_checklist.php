<?php
include "db_connect.php";

// ---------- ADD ----------
if(isset($_POST['add'])){
    $item = trim($_POST['item']);
    $status = $_POST['status'];
    $remarks = trim($_POST['remarks']);

    $stmt = $conn->prepare("INSERT INTO safety_checklist (item, status, remarks) VALUES (?, ?, ?)");
    $stmt->bind_param("sss",$item,$status,$remarks);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php?m=safety_checklist");
    exit;
}

// ---------- UPDATE ----------
if(isset($_POST['update'])){
    $id = intval($_POST['id']);
    $item = trim($_POST['item']);
    $status = $_POST['status'];
    $remarks = trim($_POST['remarks']);

    $stmt = $conn->prepare("UPDATE safety_checklist SET item=?, status=?, remarks=? WHERE id=?");
    $stmt->bind_param("sssi",$item,$status,$remarks,$id);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php?m=safety_checklist");
    exit;
}

// ---------- DELETE ----------
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM safety_checklist WHERE id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php?m=safety_checklist");
    exit;
}

// ---------- FETCH ----------
$result = $conn->query("SELECT * FROM safety_checklist ORDER BY id ASC");
?>

<div class="container mt-4">
    <!-- Add Item -->
    <div class="card p-3 mb-4">
        <h5>Add Safety Inspection Item</h5>
        <form method="post" class="row g-3">
            <div class="col-md-6">
                <input type="text" name="item" class="form-control" placeholder="Item Description" required>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-control" required>
                    <option value="Pending">Pending</option>
                    <option value="Completed">Completed</option>
                    <option value="Not Applicable">N/A</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="remarks" class="form-control" placeholder="Remarks">
            </div>
            <div class="col-md-12 mt-2">
                <button type="submit" name="add" class="btn btn-primary w-100">Add Item</button>
            </div>
        </form>
    </div>

    <!-- Checklist Table -->
    <div class="card p-3">
        <h5>Safety Inspection Checklist</h5>
        <table class="table table-bordered table-striped mt-3">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Item</th>
                    <th>Status</th>
                    <th>Remarks</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['item']) ?></td>
                    <td>
                        <?php 
                        if($row['status']=='Completed') echo '<span class="badge bg-success">Completed</span>';
                        elseif($row['status']=='Pending') echo '<span class="badge bg-warning text-dark">Pending</span>';
                        else echo '<span class="badge bg-secondary">N/A</span>';
                        ?>
                    </td>
                    <td><?= htmlspecialchars($row['remarks']) ?></td>
                    <td>
                        <button class="btn btn-sm btn-warning edit-btn" 
                                data-id="<?= $row['id'] ?>" 
                                data-item="<?= htmlspecialchars($row['item']) ?>" 
                                data-status="<?= $row['status'] ?>" 
                                data-remarks="<?= htmlspecialchars($row['remarks']) ?>" 
                                data-bs-toggle="modal" data-bs-target="#editModal">Edit</button>
                        <a href="index.php?m=safety_checklist&delete=<?= $row['id'] ?>" 
                           class="btn btn-sm btn-danger" 
                           onclick="return confirm('Delete this item?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Single Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit-id">
                <div class="mb-3">
                    <label>Item</label>
                    <input type="text" name="item" id="edit-item" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Status</label>
                    <select name="status" id="edit-status" class="form-control" required>
                        <option value="Pending">Pending</option>
                        <option value="Completed">Completed</option>
                        <option value="Not Applicable">Not Applicable</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Remarks</label>
                    <input type="text" name="remarks" id="edit-remarks" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="update" class="btn btn-success">Save Changes</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Populate edit modal dynamically
    const editButtons = document.querySelectorAll('.edit-btn');
    const editModal = document.getElementById('editModal');
    editButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('edit-id').value = btn.getAttribute('data-id');
            document.getElementById('edit-item').value = btn.getAttribute('data-item');
            document.getElementById('edit-status').value = btn.getAttribute('data-status');
            document.getElementById('edit-remarks').value = btn.getAttribute('data-remarks');
        });
    });
</script>

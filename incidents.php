<?php
include __DIR__ . "/db_connect.php";

// ---------- HANDLE ADD ----------
if (isset($_POST['add_incident'])) {
    $title = trim($_POST['incident_title']);
    $location = trim($_POST['location']);
    $desc = trim($_POST['description']);
    $date = str_replace('T', ' ', $_POST['date_reported']);

    $stmt = $conn->prepare("INSERT INTO incidents (incident_type, description, location, incident_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $title, $desc, $location, $date);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php?m=incidents&msg=added");
    exit;
}

// ---------- HANDLE EDIT ----------
if (isset($_POST['edit_incident'])) {
    $id = intval($_POST['id']);
    $title = trim($_POST['incident_title']);
    $location = trim($_POST['location']);
    $desc = trim($_POST['description']);
    $date = str_replace('T', ' ', $_POST['date_reported']);

    $stmt = $conn->prepare("UPDATE incidents SET incident_type=?, description=?, location=?, incident_date=? WHERE id=?");
    $stmt->bind_param("ssssi", $title, $desc, $location, $date, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php?m=incidents&msg=updated");
    exit;
}

// ---------- HANDLE DELETE ----------
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM incidents WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php?m=incidents&msg=deleted");
    exit;
}

// ---------- FETCH INCIDENTS ----------
$result = $conn->query("SELECT * FROM incidents ORDER BY incident_date DESC, created_at DESC");
?>

<div class="container mt-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Incident Logging</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-circle"></i> Add Incident
        </button>
    </div>

    <!-- Sticky Success Message -->
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show position-sticky top-0" style="z-index:1050;">
            Incident <?= htmlspecialchars($_GET['msg']) ?> successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Incident Table -->
    <div class="card p-3">
        <h6>Incident Records</h6>
        <table class="table table-striped table-bordered mt-3">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Location</th>
                    <th>Date & Time</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result->num_rows > 0): while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['incident_type']) ?></td>
                    <td><?= htmlspecialchars($row['location']) ?></td>
                    <td><?= date("Y-m-d H:i", strtotime($row['incident_date'])) ?></td>
                    <td><?= nl2br(htmlspecialchars($row['description'])) ?></td>
                    <td>
                        <button class="btn btn-sm btn-warning edit-btn"
                                data-id="<?= $row['id'] ?>"
                                data-title="<?= htmlspecialchars($row['incident_type']) ?>"
                                data-location="<?= htmlspecialchars($row['location']) ?>"
                                data-date="<?= date('Y-m-d\TH:i', strtotime($row['incident_date'])) ?>"
                                data-desc="<?= htmlspecialchars($row['description']) ?>"
                                data-bs-toggle="modal"
                                data-bs-target="#editModal">
                            <i class="bi bi-pencil-square"></i> Edit
                        </button>
                        <a href="index.php?m=incidents&delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this incident?')">
                            <i class="bi bi-trash"></i> Delete
                        </a>
                    </td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="6" class="text-center">No incidents logged.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Log New Incident</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Title</label>
                    <input type="text" name="incident_title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Location</label>
                    <input type="text" name="location" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Date & Time</label>
                    <input type="datetime-local" name="date_reported" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="4" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="add_incident" class="btn btn-primary">Add Incident</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Incident</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">
                <div class="mb-3">
                    <label>Title</label>
                    <input type="text" name="incident_title" id="edit_title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Location</label>
                    <input type="text" name="location" id="edit_location" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Date & Time</label>
                    <input type="datetime-local" name="date_reported" id="edit_date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="description" id="edit_desc" class="form-control" rows="4" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="edit_incident" class="btn btn-success">Save Changes</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Populate Edit Modal
    const editModal = document.getElementById('editModal');
    editModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        document.getElementById('edit_id').value = button.getAttribute('data-id');
        document.getElementById('edit_title').value = button.getAttribute('data-title');
        document.getElementById('edit_location').value = button.getAttribute('data-location');
        document.getElementById('edit_date').value = button.getAttribute('data-date');
        document.getElementById('edit_desc').value = button.getAttribute('data-desc');
    });
</script>

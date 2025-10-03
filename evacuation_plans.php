<?php
include __DIR__ . "/db_connect.php";

// ---------- HANDLE ADD ----------
if (isset($_POST['add_plan'])) {
    $plan_name = trim($_POST['plan_name']);
    $location  = trim($_POST['location']);
    $details   = trim($_POST['details']);

    // Handle map upload
    $map_file = null;
    if (isset($_FILES['map_file']) && $_FILES['map_file']['error'] == 0) {
        $ext = pathinfo($_FILES['map_file']['name'], PATHINFO_EXTENSION);
        $map_file = "uploads/" . uniqid() . "." . $ext;
        move_uploaded_file($_FILES['map_file']['tmp_name'], $map_file);
    }

    $stmt = $conn->prepare("INSERT INTO evacuation_plans (plan_name, location, details, map_file) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $plan_name, $location, $details, $map_file);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php?m=evacuation_plans&msg=added");
    exit;
}

// ---------- HANDLE EDIT ----------
if (isset($_POST['edit_plan'])) {
    $id        = intval($_POST['id']);
    $plan_name = trim($_POST['plan_name']);
    $location  = trim($_POST['location']);
    $details   = trim($_POST['details']);

    // Preserve existing map if no new file uploaded
    $map_file = $_POST['existing_map'] ?? null;
    if (isset($_FILES['map_file']) && $_FILES['map_file']['error'] == 0) {
        $ext = pathinfo($_FILES['map_file']['name'], PATHINFO_EXTENSION);
        $map_file = "uploads/" . uniqid() . "." . $ext;
        move_uploaded_file($_FILES['map_file']['tmp_name'], $map_file);
    }

    $stmt = $conn->prepare("UPDATE evacuation_plans SET plan_name=?, location=?, details=?, map_file=? WHERE id=?");
    $stmt->bind_param("ssssi", $plan_name, $location, $details, $map_file, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php?m=evacuation_plans&msg=updated");
    exit;
}

// ---------- HANDLE DELETE ----------
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM evacuation_plans WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php?m=evacuation_plans&msg=deleted");
    exit;
}

// ---------- FETCH PLANS ----------
$result = $conn->query("SELECT * FROM evacuation_plans ORDER BY id DESC");
?>

<div class="container mt-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Evacuation Plans</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-circle"></i> Add Plan
        </button>
    </div>

    <!-- Sticky Success Message -->
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show position-sticky top-0" style="z-index: 1050;">
            Plan <?= htmlspecialchars($_GET['msg']) ?> successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Plans Table -->
    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Plan Name</th>
                <th>Location</th>
                <th>Details</th>
                <th>Map</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['plan_name']) ?></td>
                <td><?= htmlspecialchars($row['location']) ?></td>
                <td><?= htmlspecialchars($row['details']) ?></td>
                <td>
                    <?php if (!empty($row['map_file'])): ?>
                        <a href="<?= htmlspecialchars($row['map_file']) ?>" target="_blank">View Map</a>
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </td>
                <td>
                    <button class="btn btn-sm btn-warning edit-btn"
                            data-id="<?= $row['id'] ?>"
                            data-name="<?= htmlspecialchars($row['plan_name']) ?>"
                            data-location="<?= htmlspecialchars($row['location']) ?>"
                            data-details="<?= htmlspecialchars($row['details']) ?>"
                            data-map="<?= htmlspecialchars($row['map_file']) ?>"
                            data-bs-toggle="modal"
                            data-bs-target="#editModal">
                        <i class="bi bi-pencil-square"></i> Edit
                    </button>
                    <a href="index.php?m=evacuation_plans&delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this plan?')">
                        <i class="bi bi-trash"></i> Delete
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" class="modal-content" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title">Add Evacuation Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Plan Name</label>
                    <input type="text" name="plan_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Location</label>
                    <input type="text" name="location" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Details</label>
                    <input type="text" name="details" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Upload Map</label>
                    <input type="file" name="map_file" class="form-control" accept="image/*,application/pdf">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="add_plan" class="btn btn-primary">Add Plan</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" class="modal-content" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title">Edit Evacuation Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">
                <input type="hidden" name="existing_map" id="edit_existing_map">
                <div class="mb-3">
                    <label>Plan Name</label>
                    <input type="text" name="plan_name" id="edit_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Location</label>
                    <input type="text" name="location" id="edit_location" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Details</label>
                    <input type="text" name="details" id="edit_details" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Upload Map</label>
                    <input type="file" name="map_file" class="form-control" accept="image/*,application/pdf">
                    <small id="map_preview" class="text-muted"></small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="edit_plan" class="btn btn-success">Save Changes</button>
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
        document.getElementById('edit_name').value = button.getAttribute('data-name');
        document.getElementById('edit_location').value = button.getAttribute('data-location');
        document.getElementById('edit_details').value = button.getAttribute('data-details');
        document.getElementById('edit_existing_map').value = button.getAttribute('data-map');

        const map_preview = document.getElementById('map_preview');
        if(button.getAttribute('data-map')) {
            map_preview.innerText = "Existing Map: " + button.getAttribute('data-map');
        } else {
            map_preview.innerText = "";
        }
    });
</script>

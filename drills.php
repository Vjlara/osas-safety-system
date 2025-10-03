<?php
include __DIR__ . "/db_connect.php";

// ---------- HANDLE ADD ----------
if (isset($_POST['add_drill'])) {
    $drill_name = trim($_POST['drill_name']);
    $drill_date = trim($_POST['drill_date']);
    $location   = trim($_POST['location']);
    $scenario   = trim($_POST['scenario']);
    $planning   = trim($_POST['planning']);

    $stmt = $conn->prepare("INSERT INTO drills (drill_name, drill_date, location, scenario, planning) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $drill_name, $drill_date, $location, $scenario, $planning);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php?m=drills&msg=added");
    exit;
}

// ---------- HANDLE EDIT ----------
if (isset($_POST['edit_drill'])) {
    $id         = intval($_POST['id']);
    $drill_name = trim($_POST['drill_name']);
    $drill_date = trim($_POST['drill_date']);
    $location   = trim($_POST['location']);
    $scenario   = trim($_POST['scenario']);
    $planning   = trim($_POST['planning']);

    $stmt = $conn->prepare("UPDATE drills SET drill_name=?, drill_date=?, location=?, scenario=?, planning=? WHERE id=?");
    $stmt->bind_param("sssssi", $drill_name, $drill_date, $location, $scenario, $planning, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php?m=drills&msg=updated");
    exit;
}

// ---------- HANDLE DELETE ----------
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM drills WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php?m=drills&msg=deleted");
    exit;
}

// ---------- FETCH ALL DRILLS ----------
$result = $conn->query("SELECT * FROM drills ORDER BY drill_date DESC");
?>

<div class="container mt-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Drill Scheduling & Planning</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-circle"></i> Add Drill
        </button>
    </div>

    <!-- Success Message -->
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            Drill <?= htmlspecialchars($_GET['msg']) ?> successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Drill Table -->
    <table class="table table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Drill Name</th>
                <th>Date</th>
                <th>Location</th>
                <th>Scenario</th>
                <th>Planning</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['drill_name']) ?></td>
                    <td><?= $row['drill_date'] ?></td>
                    <td><?= htmlspecialchars($row['location']) ?></td>
                    <td><?= htmlspecialchars($row['scenario']) ?></td>
                    <td><?= htmlspecialchars($row['planning']) ?></td>
                    <td>
                        <button class="btn btn-sm btn-warning"
                                data-bs-toggle="modal"
                                data-bs-target="#editModal"
                                data-id="<?= $row['id'] ?>"
                                data-name="<?= htmlspecialchars($row['drill_name']) ?>"
                                data-date="<?= $row['drill_date'] ?>"
                                data-location="<?= htmlspecialchars($row['location']) ?>"
                                data-scenario="<?= htmlspecialchars($row['scenario']) ?>"
                                data-planning="<?= htmlspecialchars($row['planning']) ?>">
                            Edit
                        </button>
                        <a href="index.php?m=drills&delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this drill?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Add Drill Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Add Drill</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label>Drill Name</label>
                <input type="text" name="drill_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Date</label>
                <input type="date" name="drill_date" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Location</label>
                <input type="text" name="location" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Scenario</label>
                <input type="text" name="scenario" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Planning / Procedure</label>
                <input type="text" name="planning" class="form-control" required>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" name="add_drill" class="btn btn-primary">Add Drill</button>
        </div>
    </form>
  </div>
</div>

<!-- Edit Drill Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Edit Drill</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="id" id="edit_id">
            <div class="mb-3">
                <label>Drill Name</label>
                <input type="text" name="drill_name" id="edit_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Date</label>
                <input type="date" name="drill_date" id="edit_date" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Location</label>
                <input type="text" name="location" id="edit_location" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Scenario</label>
                <input type="text" name="scenario" id="edit_scenario" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Planning / Procedure</label>
                <input type="text" name="planning" id="edit_planning" class="form-control" required>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" name="edit_drill" class="btn btn-success">Save Changes</button>
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
        document.getElementById('edit_date').value = button.getAttribute('data-date');
        document.getElementById('edit_location').value = button.getAttribute('data-location');
        document.getElementById('edit_scenario').value = button.getAttribute('data-scenario');
        document.getElementById('edit_planning').value = button.getAttribute('data-planning');
    });
</script>

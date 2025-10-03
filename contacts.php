<?php
// contacts.php
require_once "db_connect.php";

// =====================
// Handle Add Contact
// =====================
if (isset($_POST['add_contact'])) {
    $name     = trim($_POST['name']);
    $phone    = trim($_POST['phone']);
    $relation = trim($_POST['relation']);

    $stmt = $conn->prepare("INSERT INTO contacts (name, phone, relation) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $phone, $relation);
    $stmt->execute();

    header("Location: index.php?m=contacts&msg=added");
    exit;
}

// =====================
// Handle Edit Contact
// =====================
if (isset($_POST['edit_contact'])) {
    $id       = $_POST['id'];
    $name     = trim($_POST['name']);
    $phone    = trim($_POST['phone']);
    $relation = trim($_POST['relation']);

    $stmt = $conn->prepare("UPDATE contacts SET name=?, phone=?, relation=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $phone, $relation, $id);
    $stmt->execute();

    header("Location: index.php?m=contacts&msg=updated");
    exit;
}

// =====================
// Handle Delete Contact
// =====================
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM contacts WHERE id=$id");

    header("Location: index.php?m=contacts&msg=deleted");
    exit;
}

// =====================
// Fetch Contacts
// =====================
$result = $conn->query("SELECT * FROM contacts ORDER BY id DESC");
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Emergency Contacts</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-circle"></i> Add Contact
        </button>
    </div>

    <!-- Sticky Success Messages -->
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible show">
            Contact <?= htmlspecialchars($_GET['msg']) ?> successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Contacts Table -->
    <table class="table table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Relation</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td><?= htmlspecialchars($row['relation']) ?></td>
                    <td>
                        <button class="btn btn-sm btn-warning" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editModal<?= $row['id'] ?>">
                            Edit
                        </button>
                        <a href="index.php?m=contacts&delete=<?= $row['id'] ?>" 
                           onclick="return confirm('Delete this contact?')" 
                           class="btn btn-sm btn-danger">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- =====================
     Add Modal
     ===================== -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Relation</label>
                    <input type="text" name="relation" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="add_contact" class="btn btn-primary">Add</button>
            </div>
        </form>
    </div>
</div>

<!-- =====================
     Edit Modals (outside the table)
     ===================== -->
<?php
$result->data_seek(0); // rewind result pointer
while ($row = $result->fetch_assoc()):
?>
<div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <div class="mb-3">
                    <label>Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($row['name']) ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Phone</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($row['phone']) ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Relation</label>
                    <input type="text" name="relation" value="<?= htmlspecialchars($row['relation']) ?>" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="edit_contact" class="btn btn-success">Save</button>
            </div>
        </form>
    </div>
</div>
<?php endwhile; ?>

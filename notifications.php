<?php
// Assumes $conn is already connected (dashboard connection)

// ---------- AJAX CRUD ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $action = $_POST['action'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $body = $_POST['message'] ?? '';
    $level = $_POST['level'] ?? 'info';
    $sent_date = $_POST['sent_date'] ?: date('Y-m-d H:i:s');

    if ($action === 'add') {
        $stmt = $conn->prepare("INSERT INTO notifications (subject, body, level, sent_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $subject, $body, $level, $sent_date);
        $stmt->execute();
        echo "success";
        exit;
    }

    if ($action === 'update') {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("UPDATE notifications SET subject=?, body=?, level=?, sent_date=? WHERE id=?");
        $stmt->bind_param("ssssi", $subject, $body, $level, $sent_date, $id);
        $stmt->execute();
        echo "success";
        exit;
    }

    if ($action === 'delete') {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM notifications WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo "success";
        exit;
    }
}

// ---------- FETCH NOTIFICATIONS ----------
$result = $conn->query("SELECT * FROM notifications ORDER BY sent_date DESC");
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Parent Notification System</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#notifModal" onclick="openAdd()">+ Add Notification</button>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Subject</th>
                <th>Message</th>
                <th>Level</th>
                <th>Sent Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="notifTable">
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['subject']) ?></td>
                    <td><?= nl2br(htmlspecialchars($row['body'])) ?></td>
                    <td><?= ucfirst($row['level']) ?></td>
                    <td><?= $row['sent_date'] ?></td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="openEdit(<?= $row['id'] ?>,'<?= htmlspecialchars($row['subject'], ENT_QUOTES) ?>','<?= htmlspecialchars($row['body'], ENT_QUOTES) ?>','<?= $row['level'] ?>','<?= $row['sent_date'] ?>')">Edit</button>
                        <button class="btn btn-sm btn-danger" onclick="deleteNotif(<?= $row['id'] ?>)">Delete</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center">No notifications found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="notifModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="notifForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="notifTitle">Add Notification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="notif_id">
                    <input type="hidden" name="ajax" value="1">
                    <input type="hidden" name="action" id="notif_action" value="add">

                    <div class="mb-3">
                        <label>Subject</label>
                        <input type="text" name="subject" id="notif_subject" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Message</label>
                        <textarea name="message" id="notif_message" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Level</label>
                        <select name="level" id="notif_level" class="form-select">
                            <option value="info">Info</option>
                            <option value="warning">Warning</option>
                            <option value="danger">Danger</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Sent Date</label>
                        <input type="datetime-local" name="sent_date" id="notif_date" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Open Add Modal
function openAdd() {
    document.getElementById("notifForm").reset();
    document.getElementById("notif_id").value = "";
    document.getElementById("notif_action").value = "add";
    document.getElementById("notifTitle").innerText = "Add Notification";
}

// Open Edit Modal
function openEdit(id, subject, message, level, sent) {
    document.getElementById("notif_id").value = id;
    document.getElementById("notif_subject").value = subject;
    document.getElementById("notif_message").value = message;
    document.getElementById("notif_level").value = level;
    document.getElementById("notif_date").value = sent.replace(" ", "T");
    document.getElementById("notif_action").value = "update";
    document.getElementById("notifTitle").innerText = "Edit Notification";
    var modal = new bootstrap.Modal(document.getElementById('notifModal'));
    modal.show();
}

// Delete Notification
function deleteNotif(id){
    if(!confirm("Delete this notification?")) return;
    let formData = new FormData();
    formData.append('ajax', 1);
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch('modules/notifications.php', { method:'POST', body: formData })
    .then(res => res.text())
    .then(data=>{
        if(data.trim() === 'success'){
            location.reload();
        } else alert(data);
    });
}

// AJAX Submit Form
document.getElementById("notifForm").addEventListener("submit", function(e){
    e.preventDefault();
    let formData = new FormData(this);
    fetch('modules/notifications.php', { method:'POST', body: formData })
    .then(res=>res.text())
    .then(data=>{
        if(data.trim() === 'success'){
            location.reload(); // refresh table
        } else alert(data);
    });
});
</script>

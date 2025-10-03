
<?php
include "db_connect.php";

// ---------- AJAX HANDLER ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax']) && $_POST['ajax'] == '1') {
    header('Content-Type: application/json; charset=utf-8');
    $action = $_POST['action'] ?? '';

    if ($action === 'update') {
        $id       = intval($_POST['id']);
        $item     = $_POST['item_name'] ?? '';
        $quantity = intval($_POST['quantity'] ?? 0);
        $unit     = $_POST['unit'] ?? '';
        $restock  = !empty($_POST['restock_date']) ? $_POST['restock_date'] : null;

        $stmt = $conn->prepare("UPDATE first_aid_supplies SET item_name=?, quantity=?, unit=?, restock_date=? WHERE id=?");
        $stmt->bind_param("sissi", $item, $quantity, $unit, $restock, $id);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            $res = $conn->query("SELECT * FROM first_aid_supplies WHERE id=$id LIMIT 1");
            echo json_encode(['status' => 'success', 'row' => $res->fetch_assoc()]);
        } else {
            echo json_encode(['status' => 'error']);
        }
        exit;
    }

    if ($action === 'delete') {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM first_aid_supplies WHERE id=?");
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();
        echo json_encode(['status' => $ok ? 'success' : 'error']);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    exit;
}

// ---------- NORMAL PAGE (non-AJAX) ----------
$result = $conn->query("SELECT * FROM first_aid_supplies ORDER BY id ASC");
?>

<div class="container mt-4">
  <div class="card p-3">
    <h6>Supplies Inventory</h6>
    <div class="table-responsive">
      <table class="table table-bordered" id="suppliesTable">
        <thead class="table-dark">
          <tr>
            <th>ID</th><th>Item</th><th>Qty</th><th>Unit</th><th>Restock</th><th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row=$result->fetch_assoc()): ?>
          <tr id="row_<?= $row['id'] ?>">
            <td><?= $row['id'] ?></td>
            <td class="col-item"><?= htmlspecialchars($row['item_name']) ?></td>
            <td class="col-qty"><?= $row['quantity'] ?></td>
            <td class="col-unit"><?= htmlspecialchars($row['unit']) ?></td>
            <td class="col-restock"><?= $row['restock_date'] ?></td>
            <td>
              <button class="btn btn-sm btn-warning btn-edit"
                data-id="<?= $row['id'] ?>"
                data-item="<?= htmlspecialchars($row['item_name'],ENT_QUOTES) ?>"
                data-qty="<?= $row['quantity'] ?>"
                data-unit="<?= htmlspecialchars($row['unit'],ENT_QUOTES) ?>"
                data-restock="<?= $row['restock_date'] ?>">
                Edit
              </button>
              <button class="btn btn-sm btn-danger btn-delete" data-id="<?= $row['id'] ?>">Delete</button>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="editForm" class="modal-content">
      <div class="modal-header"><h5>Edit Supply</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <input type="hidden" name="ajax" value="1">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" id="edit_id">
        <div class="mb-2"><label>Item</label><input class="form-control" name="item_name" id="edit_item"></div>
        <div class="mb-2"><label>Quantity</label><input type="number" class="form-control" name="quantity" id="edit_qty"></div>
        <div class="mb-2"><label>Unit</label><input class="form-control" name="unit" id="edit_unit"></div>
        <div class="mb-2"><label>Restock Date</label><input type="date" class="form-control" name="restock_date" id="edit_restock"></div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
  const editModal = new bootstrap.Modal(document.getElementById('editModal'));
  const editForm  = document.getElementById('editForm');

  // open modal
  document.getElementById('suppliesTable').addEventListener('click', e=>{
    const btn = e.target.closest('.btn-edit');
    if(btn){
      document.getElementById('edit_id').value       = btn.dataset.id;
      document.getElementById('edit_item').value     = btn.dataset.item;
      document.getElementById('edit_qty').value      = btn.dataset.qty;
      document.getElementById('edit_unit').value     = btn.dataset.unit;
      document.getElementById('edit_restock').value  = btn.dataset.restock;
      editModal.show();
    }
  });

  // save edit
  editForm.addEventListener('submit', e=>{
    e.preventDefault();
    fetch('supplies.php',{method:'POST',body:new FormData(editForm)})
    .then(r=>r.json()).then(resp=>{
      if(resp.status==='success'){
        let row=resp.row;
        let tr=document.getElementById('row_'+row.id);
        tr.querySelector('.col-item').innerText=row.item_name;
        tr.querySelector('.col-qty').innerText=row.quantity;
        tr.querySelector('.col-unit').innerText=row.unit;
        tr.querySelector('.col-restock').innerText=row.restock_date;
        editModal.hide();
      } else alert('Update failed');
    });
  });

  // delete
  document.getElementById('suppliesTable').addEventListener('click', e=>{
    const btn=e.target.closest('.btn-delete');
    if(btn){
      if(!confirm('Delete this supply?')) return;
      let fd=new FormData();
      fd.append('ajax','1');
      fd.append('action','delete');
      fd.append('id',btn.dataset.id);
      fetch('supplies.php',{method:'POST',body:fd})
      .then(r=>r.json()).then(resp=>{
        if(resp.status==='success'){
          document.getElementById('row_'+btn.dataset.id).remove();
        } else alert('Delete failed');
      });
    }
  });
});
</script>



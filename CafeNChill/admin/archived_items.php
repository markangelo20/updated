<?php
session_start();
include "../config/db.php";


if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

$page = basename($_SERVER['PHP_SELF']);


if(isset($_GET['archive_id']) && is_numeric($_GET['archive_id'])){
    $id = intval($_GET['archive_id']);
    $stmt = $conn->prepare("UPDATE items SET status='archived' WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success'] = "Item archived successfully!";
    header("Location: archived_items.php");
    exit();
}


if(isset($_GET['restore_id']) && is_numeric($_GET['restore_id'])){
    $id = intval($_GET['restore_id']);
    $stmt = $conn->prepare("UPDATE items SET status='approved' WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success'] = "Item restored successfully!";
    header("Location: archived_items.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cafe N Chill | Archived Items</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

:root{
    --bg-color:#0c0a09;
    --sidebar-color:#1c1917;
    --coffee-brown:#845c44;
    --cream-accent:#d4a373;
    --text-light:#fafaf9;
    --card-bg:#292524;
}

body{
    margin:0;
    font-family:'Poppins', sans-serif;
    background:var(--bg-color);
    color:var(--text-light);
    display:flex;
}

.sidebar {
    width: 260px;
    height: 100vh;
    background: var(--sidebar-color);
    position: fixed;
    padding-top: 20px;
    border-right: 1px solid rgba(132, 92, 68, 0.2);
    overflow-y: auto;
}

.sidebar h2 {
    color: var(--cream-accent);
    text-align: center;
    font-size: 1.3rem;
    margin-bottom: 30px;
    letter-spacing: 2px;
    border-bottom: 1px solid rgba(212, 163, 115, 0.1);
    padding-bottom: 20px;
}

.sidebar a {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #a8a29e;
    padding: 12px 25px;
    text-decoration: none;
    transition: 0.3s;
    font-size: 0.9rem;
    cursor: pointer;
}

.sidebar a:hover {
    background: rgba(132, 92, 68, 0.1);
    color: var(--cream-accent);
}

.sidebar a.active {
    background: var(--coffee-brown);
    color: white;
    border-left: 4px solid var(--cream-accent);
}

.main{
    margin-left:260px;
    padding:40px;
    width:100%;
}

h1{
    color:var(--cream-accent);
    margin-bottom:30px;
}

.table-container{
    background:var(--card-bg);
    padding:25px;
    border-radius:15px;
    overflow-x:auto;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    text-align:left;
    padding:15px;
    color:var(--cream-accent);
    border-bottom:2px solid #3f3f46;
    text-transform:uppercase;
    font-size:.85rem;
}

td{
    padding:15px;
    border-bottom:1px solid #3f3f46;
}

.status-archived{
    color:#ef4444;
    font-weight:600;
    font-size:.75rem;
    background:rgba(239,68,68,.1);
    padding:4px 10px;
    border-radius:6px;
}

.btn-restore{
    background:#22c55e;
    color:white;
    padding:8px 15px;
    border-radius:8px;
    text-decoration:none;
    font-size:0.85rem;
    font-weight:600;
    display:inline-flex;
    align-items:center;
    gap:8px;
    transition:0.3s;
}

.btn-restore:hover{
    background:#16a34a;
}

.no-data{
    text-align:center;
    padding:40px;
    color:#a8a29e;
}
</style>
</head>

<body>

<div class="sidebar">
    <h2>CAFE N CHILL</h2>
    <a href="admin_dashboard.php" class="<?= $page == 'admin_dashboard.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-gauge"></i> Dashboard
    </a>
    <a href="add_item.php" class="<?= $page == 'add_item.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-plus"></i> Add Item
    </a>
    <a href="view_items.php" class="<?= $page == 'view_items.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-box-open"></i> View Items
    </a>
    <a href="update_item.php" class="<?= $page == 'update_item.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-pen-to-square"></i> Update Item
    </a>
    <a href="archived_items.php" class="<?= $page == 'archived_items.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-box-archive"></i> Archived Items
    </a>
    <a href="view_user.php" class="<?= $page == 'view_user.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-users"></i> View Users
    </a>
    <a href="add_user.php" class="<?= $page == 'add_user.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-user-plus"></i> Add User
    </a>
    <a href="archived_users.php" class="<?= $page == 'archived_users.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-user-slash"></i> Archived Users
    </a>
    <a href="reset_password.php" class="<?= $page == 'reset_password.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-key"></i> Reset Password
    </a>
    <a href="approve_item.php" class="<?= $page == 'approve_item.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-check-double"></i> Approve Items
    </a>

   
    <a onclick="confirmLogout()" style="margin-top: 20px; color: #f87171; cursor: pointer;">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
    </a>
</div>

<div class="main">
    <h1>
        <i class="fa-solid fa-box-archive"></i>
        Archived Inventory
    </h1>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res = $conn->query("SELECT * FROM items WHERE status='archived' ORDER BY id DESC");
                if($res && $res->num_rows > 0){
                    while($r = $res->fetch_assoc()){
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($r['name']) ?></strong></td>
                    <td><?= htmlspecialchars($r['category']) ?></td>
                    <td><?= $r['quantity'] ?></td>
                    <td><span class="status-archived">ARCHIVED</span></td>
                    <td>
                        <a href="#" class="btn-restore" onclick="confirmRestore(<?= $r['id'] ?>)">
                            <i class="fa-solid fa-rotate-left"></i> Restore
                        </a>
                    </td>
                </tr>
                <?php } } else { ?>
                <tr>
                    <td colspan="5" class="no-data">No archived items found.</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function confirmLogout() {
    Swal.fire({
        title: 'Logout?',
        text: "Are you sure you want to log out?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#845c44',
        cancelButtonColor: '#292524',
        confirmButtonText: 'Yes, Logout',
        cancelButtonText: 'Stay Here',
        background: '#1c1917',
        color: '#fafaf9'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "../auth/logout.php";
        }
    });
}

function confirmRestore(id){
    Swal.fire({
        title: 'Restore Item?',
        text: 'This item will return to active inventory.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#22c55e',
        cancelButtonColor: '#3f3f46',
        confirmButtonText: 'Yes, Restore',
        background: '#1c1917',
        color: '#fafaf9'
    }).then((result)=>{
        if(result.isConfirmed){
            window.location.href = "archived_items.php?restore_id=" + id;
        }
    });
}

<?php if(isset($_SESSION['success'])): ?>
Swal.fire({
    icon:'success',
    title:'Success',
    text:'<?= $_SESSION['success'] ?>',
    confirmButtonColor:'#845c44',
    background:'#1c1917',
    color:'#fafaf9'
});
<?php unset($_SESSION['success']); endif; ?>
</script>

</body>
</html>
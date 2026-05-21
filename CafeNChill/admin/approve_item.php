<?php
session_start();
include "../config/db.php";

// SECURITY CHECK - Admin access only
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

// Handle Approve Logic
if(isset($_GET['approve_id'])){
    $id = intval($_GET['approve_id']);

    $stmt_get = $conn->prepare("SELECT * FROM item_requests WHERE id = ?");
    $stmt_get->bind_param("i", $id);
    $stmt_get->execute();
    $request_data = $stmt_get->get_result()->fetch_assoc();

    if($request_data){
        // Isinama na rin ang UOM sa pag-insert sa items table
        $stmt_ins = $conn->prepare("INSERT INTO items (name, category, quantity, uom, status) VALUES (?, ?, ?, ?, 'approved')");
        $stmt_ins->bind_param("ssis", $request_data['name'], $request_data['category'], $request_data['quantity'], $request_data['uom']);
        
        if($stmt_ins->execute()){
            $stmt_del = $conn->prepare("DELETE FROM item_requests WHERE id = ?");
            $stmt_del->bind_param("i", $id);
            $stmt_del->execute();
            
            header("Location: approve_item.php?msg=approved");
            exit;
        }
    }
}

// Handle Decline Logic
if(isset($_GET['decline_id'])){
    $id = intval($_GET['decline_id']);
    $stmt_del = $conn->prepare("DELETE FROM item_requests WHERE id = ?");
    $stmt_del->bind_param("i", $id);
    
    if($stmt_del->execute()){
        header("Location: approve_item.php?msg=declined");
        exit;
    }
}

$page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cafe N Chill | Approve Items</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        :root {
            --bg-color: #0c0a09;
            --sidebar-color: #1c1917;
            --coffee-brown: #845c44;
            --cream-accent: #d4a373;
            --text-light: #fafaf9;
            --card-bg: #292524;
            --danger-red: #ef4444;
            --success-green: #10b981;
        }

        body { margin: 0; font-family: 'Poppins', sans-serif; background: var(--bg-color); color: var(--text-light); display: flex; }

        .sidebar { width: 260px; height: 100vh; background: var(--sidebar-color); position: fixed; padding-top: 20px; border-right: 1px solid rgba(132, 92, 68, 0.2); overflow-y: auto; }
        .sidebar h2 { color: var(--cream-accent); text-align: center; font-size: 1.3rem; margin-bottom: 30px; letter-spacing: 2px; border-bottom: 1px solid rgba(212, 163, 115, 0.1); padding-bottom: 20px; }
        .sidebar a { display: flex; align-items: center; gap: 12px; color: #a8a29e; padding: 12px 25px; text-decoration: none; transition: 0.3s; font-size: 0.9rem; cursor: pointer; }
        .sidebar a:hover { background: rgba(132, 92, 68, 0.1); color: var(--cream-accent); }
        .sidebar a.active { background: var(--coffee-brown); color: white; border-left: 4px solid var(--cream-accent); }

        .main { margin-left: 260px; padding: 40px; width: calc(100% - 260px); min-height: 100vh; box-sizing: border-box; }
        .header-title h1 { color: var(--cream-accent); font-weight: 600; margin: 0; }
        .table-container { background: var(--card-bg); padding: 25px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); margin-top: 20px; border: 1px solid rgba(132, 92, 68, 0.1); }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; color: var(--cream-accent); padding: 15px; border-bottom: 2px solid #3f3f46; font-size: 0.9rem; }
        td { padding: 15px; border-bottom: 1px solid #3f3f46; color: #d1d5db; font-size: 0.85rem; }

        .btn-group { display: flex; gap: 8px; }
        .btn-action { padding: 8px 12px; border-radius: 6px; text-decoration: none; font-size: 0.8rem; font-weight: 600; color: white; cursor: pointer; border: none; display: flex; align-items: center; gap: 5px; }
        .btn-approve { background: var(--success-green); }
        .btn-decline { background: var(--danger-red); }
        .btn-action:hover { opacity: 0.8; transform: translateY(-2px); transition: 0.2s; }
        
        .no-data { text-align: center; padding: 50px; color: #a8a29e; font-style: italic; }
        .reason-text { font-size: 0.75rem; color: #d4a373; font-style: italic; background: rgba(0,0,0,0.2); padding: 5px; border-radius: 4px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>CAFE N CHILL</h2>
    <a href="admin_dashboard.php" class="<?= $page == 'admin_dashboard.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-gauge"></i> Dashboard
    </a>
    <a href="add_item.php"><i class="fa-solid fa-plus"></i> Add Item</a>
    <a href="view_items.php"><i class="fa-solid fa-box-open"></i> View Items</a>
    <a href="update_item.php"><i class="fa-solid fa-pen-to-square"></i> Update Item</a>
    <a href="archived_items.php"><i class="fa-solid fa-box-archive"></i> Archived Items</a>
    <a href="view_user.php"><i class="fa-solid fa-users"></i> View Users</a>
    <a href="add_user.php"><i class="fa-solid fa-user-plus"></i> Add User</a>
    <a href="archived_users.php"><i class="fa-solid fa-user-slash"></i> Archived Users</a>
    <a href="reset_password.php"><i class="fa-solid fa-key"></i> Reset Password</a>
    <a href="approve_item.php" class="<?= $page == 'approve_item.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-check-double"></i> Approve Items
    </a>

    <a onclick="confirmLogout()" style="margin-top: 20px; color: #f87171;">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
    </a>
</div>

<div class="main">
    <div class="header-title">
        <h1><i class="fa-solid fa-clipboard-check"></i> Item Requests</h1>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Reason</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res = $conn->query("SELECT * FROM item_requests ORDER BY id DESC");
                if($res->num_rows > 0){
                    while($r = $res->fetch_assoc()){
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($r['name']) ?></strong></td>
                    <td><?= htmlspecialchars($r['category']) ?></td>
                    <td><?= $r['quantity'] . ' ' . htmlspecialchars($r['uom']) ?></td>
                    <td><div class="reason-text"><?= htmlspecialchars($r['reason'] ?? 'No reason provided') ?></div></td>
                    <td>
                        <div class="btn-group">
                            <button onclick="handleAction(<?= $r['id'] ?>, 'approve')" class="btn-action btn-approve">
                                <i class="fa-solid fa-check"></i> Approve
                            </button>
                            <button onclick="handleAction(<?= $r['id'] ?>, 'decline')" class="btn-action btn-decline">
                                <i class="fa-solid fa-xmark"></i> Decline
                            </button>
                        </div>
                    </td>
                </tr>
                <?php } } else { ?>
                    <tr><td colspan="5" class="no-data">No pending requests at the moment.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function confirmLogout() {
        Swal.fire({
            title: 'Logout?',
            text: "Are you sure you want to end your session?",
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

    function handleAction(id, type) {
        Swal.fire({
            title: type === 'approve' ? 'Approve this item?' : 'Decline this request?',
            text: type === 'approve' ? "This will add the item to the main inventory." : "This request will be permanently deleted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: type === 'approve' ? '#10b981' : '#ef4444',
            cancelButtonColor: '#292524',
            confirmButtonText: 'Yes, proceed!',
            background: '#1c1917',
            color: '#fafaf9'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `approve_item.php?${type}_id=${id}`;
            }
        });
    }
</script>

<?php
if(isset($_GET['msg'])){
    $msg = $_GET['msg'];
    $title = ($msg == 'approved') ? 'Approved!' : 'Declined!';
    $icon = ($msg == 'approved') ? 'success' : 'info';
    echo "<script>
        Swal.fire({ 
            title: '$title', 
            icon: '$icon', 
            background: '#1c1917', 
            color: '#fafaf9',
            confirmButtonColor: '#845c44'
        });
    </script>";
}
?>
</body>
</html>
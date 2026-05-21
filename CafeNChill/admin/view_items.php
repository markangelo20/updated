<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

// Kukunin lahat ng approved items mula sa database kasama ang bagong professional fields
$query = "SELECT * FROM items WHERE status = 'approved' ORDER BY id DESC";
$result = $conn->query($query);
$page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Inventory | Cafe N Chill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');

        :root {
            --bg-color: #0c0a09;
            --sidebar-color: #171717;
            --coffee-brown: #845c44;
            --cream-accent: #d4a373;
            --text-light: #fafaf9;
            --card-bg: #1c1917;
            --border-color: rgba(212, 163, 115, 0.1);
        }

        body {
            margin: 0; font-family: 'Poppins', sans-serif;
            background: var(--bg-color); color: var(--text-light);
            display: flex; min-height: 100vh;
        }

        /* Sidebar Styling */
        .sidebar { width: 260px; height: 100vh; background: var(--sidebar-color); position: fixed; padding-top: 20px; border-right: 1px solid rgba(132, 92, 68, 0.2); overflow-y: auto; }
        .sidebar h2 { color: var(--cream-accent); text-align: center; font-size: 1.3rem; margin-bottom: 30px; border-bottom: 1px solid rgba(212, 163, 115, 0.1); padding-bottom: 20px; }
        .sidebar a { display: flex; align-items: center; gap: 12px; color: #a8a29e; padding: 12px 25px; text-decoration: none; font-size: 0.9rem; transition: 0.3s; cursor: pointer; }
        .sidebar a:hover { background: rgba(132, 92, 68, 0.1); color: var(--cream-accent); }
        .sidebar a.active { background: var(--coffee-brown); color: white; border-left: 4px solid var(--cream-accent); }

        /* Main Layout */
        .main-content {
            margin-left: 260px; padding: 40px; width: 100%;
            box-sizing: border-box;
        }

        .glass-card {
            background: var(--card-bg); padding: 30px; border-radius: 16px;
            border: 1px solid var(--border-color); box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .section-title {
            font-size: 1.1rem; color: var(--cream-accent); margin-bottom: 25px;
            display: flex; align-items: center; gap: 10px; font-weight: 500;
        }

        /* Table Design */
        .inventory-table { width: 100%; border-collapse: collapse; text-align: left; }
        .inventory-table th { font-size: 0.75rem; color: #737373; text-transform: uppercase; padding: 15px 12px; border-bottom: 1px solid var(--border-color); letter-spacing: 0.5px; }
        .inventory-table td { padding: 18px 12px; font-size: 0.9rem; border-bottom: 1px solid rgba(255,255,255,0.03); vertical-align: middle; }
        
        .status-badge {
            background: rgba(34, 197, 94, 0.1); color: #4ade80;
            padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 500;
            text-transform: capitalize;
        }

        .desc-col {
            color: #a8a29e; font-size: 0.85rem; max-width: 220px; 
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }

        .uom-badge {
            font-size: 0.75rem; background: rgba(255,255,255,0.05); color: #a8a29e;
            padding: 2px 6px; border-radius: 4px; margin-left: 5px; font-weight: 400;
        }

        /* Action Buttons */
        .btn-edit {
            background: rgba(212, 163, 115, 0.1); color: var(--cream-accent);
            padding: 8px 12px; border-radius: 6px; border: 1px solid rgba(212, 163, 115, 0.2);
            text-decoration: none; font-size: 0.85rem; transition: 0.2s; display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-edit:hover { background: var(--coffee-brown); color: white; transform: translateY(-1px); }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>CAFE N CHILL</h2>
    <a href="admin_dashboard.php" class="<?= $page == 'admin_dashboard.php' ? 'active' : '' ?>"><i class="fa-solid fa-gauge"></i> Dashboard</a>
    <a href="add_item.php" class="<?= $page == 'add_item.php' ? 'active' : '' ?>"><i class="fa-solid fa-plus"></i> Add Item</a>
    <a href="view_items.php" class="<?= $page == 'view_items.php' ? 'active' : '' ?>"><i class="fa-solid fa-box-open"></i> View Items</a>
    <a href="update_item.php" class="<?= $page == 'update_item.php' ? 'active' : '' ?>"><i class="fa-solid fa-pen-to-square"></i> Update Item</a>
    <a href="archived_items.php" class="<?= $page == 'archived_items.php' ? 'active' : '' ?>"><i class="fa-solid fa-box-archive"></i> Archived Items</a>
    <a href="view_user.php" class="<?= $page == 'view_user.php' ? 'active' : '' ?>"><i class="fa-solid fa-users"></i> View Users</a>
    <a href="add_user.php" class="<?= $page == 'add_user.php' ? 'active' : '' ?>"><i class="fa-solid fa-user-plus"></i> Add User</a>
    <a href="archived_users.php" class="<?= $page == 'archived_users.php' ? 'active' : '' ?>"><i class="fa-solid fa-user-slash"></i> Archived Users</a>
    <a href="reset_password.php" class="<?= $page == 'reset_password.php' ? 'active' : '' ?>"><i class="fa-solid fa-key"></i> Reset Password</a>
    <a href="approve_item.php" class="<?= $page == 'approve_item.php' ? 'active' : '' ?>"><i class="fa-solid fa-check-double"></i> Approve Items</a>
    <a onclick="confirmLogout()" style="margin-top: 20px; color: #f87171; cursor: pointer;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</div>

<main class="main-content">
    <section class="glass-card">
        <div class="section-title">
            <i class="fa-solid fa-box-open"></i> Menu Inventory List
        </div>

        <table class="inventory-table">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Stock Qty</th>
                    <th>Selling Price</th> <th>Description</th>
                    <th>Status</th>
                    <th style="text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td style="font-weight: 500; color: #fff;"><?= htmlspecialchars($row['name']); ?></td>
                        
                        <td><span style="color: #a8a29e;"><?= htmlspecialchars($row['category']); ?></span></td>
                        
                        <td style="font-weight: 600; color: <?= $row['quantity'] <= 5 ? '#f87171' : '#fafaf9'; ?>;">
                            <?= $row['quantity']; ?>
                            <span class="uom-badge"><?= !empty($row['uom']) ? htmlspecialchars($row['uom']) : 'pcs'; ?></span>
                            <?= $row['quantity'] <= 5 ? ' <small style="color:#f87171; font-weight:400;">(Low)</small>' : ''; ?>
                        </td>
                        
                        <td style="font-weight: 600; color: var(--cream-accent);">
                            ₱<?= number_format((isset($row['selling_price']) ? $row['selling_price'] : 0), 2); ?>
                        </td>
                        
                        <td class="desc-col">
                            <?= (!empty($row['description'])) ? htmlspecialchars($row['description']) : '<em style="color:#525252; font-style: normal;">No description</em>'; ?>
                        </td>
                        
                        <td><span class="status-badge"><?= htmlspecialchars($row['status']); ?></span></td>
                        
                        <td style="text-align: center;">
                            <a href="update_item.php?id=<?= $row['id']; ?>" class="btn-edit">
                                <i class="fa-solid fa-pen-to-square"></i> Restock / Edit
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: #737373; padding: 40px 0;">
                            <i class="fa-solid fa-folder-open" style="font-size: 2rem; display:block; margin-bottom:10px;"></i>
                            No approved items found in inventory.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>

<script>
    function confirmLogout() {
        Swal.fire({
            title: 'Logout?',
            text: "Are you sure you want to end your session?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#845c44',
            cancelButtonColor: '#262626',
            confirmButtonText: 'Yes, Logout',
            cancelButtonText: 'Stay Here',
            background: '#1c1917',
            color: '#fafaf9'
        }).then((result) => {
            if (result.isConfirmed) { 
                window.location.href = '../auth/logout.php'; 
            }
        });
    }
</script>

</body>
</html>
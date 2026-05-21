<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

$page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cafe N Chill | Admin Dashboard</title>
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
            --pending-yellow: #f59e0b;
            --approved-green: #10b981;
            --archived-red: #ef4444;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: var(--bg-color);
            color: var(--text-light);
            display: flex;
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


        .main {
            margin-left: 260px;
            padding: 40px;
            width: 100%;
        }

        .header-section {
            background: linear-gradient(135deg, var(--coffee-brown), #5a3e2e);
            color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            margin-bottom: 30px;
        }

 
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            border-bottom: 4px solid var(--coffee-brown);
            transition: 0.3s;
        }

        .stat-card:hover { transform: translateY(-5px); }
        .stat-card h3 { margin: 0; font-size: 0.85rem; opacity: 0.7; text-transform: uppercase; }
        .stat-card p { margin: 10px 0 0; font-size: 2.2rem; font-weight: 700; color: var(--cream-accent); }

        .stat-card.green { border-bottom-color: var(--approved-green); }
        .stat-card.green p { color: var(--approved-green); }

        .stat-card.yellow { border-bottom-color: var(--pending-yellow); }
        .stat-card.yellow p { color: var(--pending-yellow); }

        .stat-card.red { border-bottom-color: var(--archived-red); }
        .stat-card.red p { color: var(--archived-red); }

        /* TABLE */
        .table-wrapper {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .table-wrapper h2 {
            margin-top: 0;
            font-size: 1.2rem;
            color: var(--cream-accent);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        table { width: 100%; border-collapse: collapse; }

        th {
            text-align: left;
            padding: 15px;
            font-size: 0.9rem;
            color: var(--cream-accent);
            border-bottom: 2px solid #3f3f46;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #3f3f46;
            font-size: 0.9rem;
            color: #d1d5db;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .bg-approved  { background: rgba(16, 185, 129, 0.1); color: var(--approved-green); }
        .bg-pending   { background: rgba(245, 158, 11, 0.1);  color: var(--pending-yellow); }
        .bg-archived  { background: rgba(239, 68, 68, 0.1);   color: var(--archived-red); }
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
    <a href="view_user.php" ><i class="fa-solid fa-users"></i> View Users</a>
    <a href="add_user.php"><i class="fa-solid fa-user-plus"></i> Add User</a>
    <a href="archived_users.php"><i class="fa-solid fa-user-slash"></i> Archived Users</a>
    <a href="reset_password.php"><i class="fa-solid fa-key"></i> Reset Password</a>
    <a href="approve_item.php"><i class="fa-solid fa-check-double"></i> Approve Items</a>

    <a onclick="confirmLogout()" style="margin-top: 20px; color: #f87171;">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
    </a>
</div>


<div class="main">
    <div class="header-section">
        <h1 style="margin:0;">Welcome, Admin!</h1>
        <p style="opacity:0.8; margin:5px 0 0;">Cafe N Chill  Inventory Management System.</p>
    </div>

   
    <?php
    $pendingCount = $conn->query("SELECT COUNT(*) as c FROM item_requests WHERE status='pending'")->fetch_assoc()['c'];
    
    $approvedCount = $conn->query("SELECT COUNT(*) as c FROM items WHERE status='approved'")->fetch_assoc()['c'];
    $archivedCount = $conn->query("SELECT COUNT(*) as c FROM items WHERE status='archived'")->fetch_assoc()['c'];
    
   
    $totalCount = $approvedCount + $pendingCount;
    
    $totalUsers = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
    ?>

    <div class="dashboard-grid">
        <div class="stat-card">
            <h3>Total Active Items</h3>
            <p><?= $totalCount ?></p>
        </div>
        <div class="stat-card green">
            <h3>Approved Items</h3>
            <p><?= $approvedCount ?></p>
        </div>
        <div class="stat-card yellow">
            <h3>Pending Items</h3>
            <p><?= $pendingCount ?></p>
        </div>
        <div class="stat-card red">
            <h3>Archived Items</h3>
            <p><?= $archivedCount ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Users</h3>
            <p><?= $totalUsers ?></p>
        </div>
    </div>

    <div class="table-wrapper">
        <h2><i class="fa-solid fa-list" style="margin-right:10px;"></i> Inventory Overview</h2>
        <table>
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "
                    (SELECT name, category, quantity, status, id FROM items)
                    UNION ALL
                    (SELECT name, category, quantity, status, id FROM item_requests WHERE status = 'pending')
                    ORDER BY status DESC, id DESC LIMIT 10
                ";
                
                $res = $conn->query($sql);
                while($r = $res->fetch_assoc()):
                ?>
                <tr>
                    <td>
                        <i class="fa-solid fa-cube" style="color:var(--coffee-brown); margin-right:8px;"></i>
                        <?= htmlspecialchars($r['name']) ?>
                    </td>
                    <td><?= htmlspecialchars($r['category']) ?></td>
                    <td><strong><?= $r['quantity'] ?></strong></td>
                    <td>
                        <?php
                        if($r['status'] == 'approved'):
                            echo "<span class='status-badge bg-approved'>Approved</span>";
                        elseif($r['status'] == 'pending'):
                            echo "<span class='status-badge bg-pending'>Pending</span>";
                        elseif($r['status'] == 'archived'):
                            echo "<span class='status-badge bg-archived'>Archived</span>";
                        else:
                            echo "<span class='status-badge bg-pending'>" . htmlspecialchars($r['status']) . "</span>";
                        endif;
                        ?>
                    </td>
                </tr>
                <?php endwhile; ?>
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
</script>

</body>
</html>
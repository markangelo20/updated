<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Query para sa mga available na items
$query = "SELECT * FROM items WHERE status='approved'";
if (!empty($search)) {
    $query .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
}
$result = $conn->query($query);

// Query para sa history ng requests ng staff
$req_query = "SELECT * FROM item_requests WHERE user_id = ? ORDER BY id DESC LIMIT 5";
$stmt_req = $conn->prepare($req_query);
$stmt_req->bind_param("i", $user_id);
$stmt_req->execute();
$req_result = $stmt_req->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>CAFE N CHILL | Staff Dashboard</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

        :root {
            --bg-color: #0c0a09;
            --card-bg: #1c1917;
            --text-color: #fafaf9;
            --main-color: #845c44;
            --accent-color: #d6d3d1;
            --border: #292524;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-color); padding: 20px; }
        .dashboard-container { max-width: 1000px; margin: 40px auto; }

        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 1px solid var(--border); padding-bottom: 20px; }
        .welcome-msg h2 { font-size: 1.8rem; color: var(--main-color); }
        
        .toolbar { display: flex; justify-content: space-between; margin-bottom: 20px; gap: 15px; }
        .search-box { display: flex; flex: 1; }
        .search-box input { width: 100%; padding: 12px; border-radius: 8px 0 0 8px; border: 1px solid var(--border); background: var(--card-bg); color: white; outline: none; }
        .search-btn { padding: 10px 20px; background: var(--main-color); border: none; color: white; border-radius: 0 8px 8px 0; cursor: pointer; }
        
        .btn-request { background: var(--main-color); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; }
        
        .item-card { background: var(--card-bg); border-radius: 12px; padding: 25px; border: 1px solid var(--border); margin-bottom: 30px; }
        .item-card h3 { margin-bottom: 20px; color: var(--accent-color); font-size: 1.1rem; }
        .item-list { width: 100%; border-collapse: collapse; }
        .item-list th, .item-list td { text-align: left; padding: 15px; border-bottom: 1px solid var(--border); font-size: 0.9rem; }
        .item-list th { color: var(--main-color); text-transform: uppercase; font-size: 0.75rem; }
        
        .status-pill { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
    </style>
</head>
<body>

<div class="dashboard-container">
    <div class="header">
        <div class="welcome-msg">
            <h2>Kumusta, <?php echo htmlspecialchars($user['full_name']); ?>! ☕</h2>
            <p style="color: #78716c;">Cafe N Chill Staff Portal</p>
        </div>
        <a href="#" onclick="confirmLogout()" style="color: #ef4444; text-decoration: none;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>

    <div class="toolbar">
        <form action="" method="GET" class="search-box">
            <input type="text" name="search" placeholder="Search inventory..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>
        <a href="add_item_request.php" class="btn-request"><i class="fa-solid fa-plus"></i> Request Item</a>
    </div>

    <div class="item-card">
        <h3><i class="fa-solid fa-boxes-stacked"></i> Available Inventory</h3>
        <table class="item-list">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Stock</th>
                    <th>UOM</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo $row['quantity']; ?></td>
                    <td><?php echo htmlspecialchars($row['uom']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="item-card">
        <h3><i class="fa-solid fa-clock-rotate-left"></i> My Recent Requests</h3>
        <table class="item-list">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while($req = $req_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($req['name']); ?></td>
                    <td><?php echo $req['quantity']; ?></td>
                    <td>
                        <span class="status-pill" style="background: <?php echo ($req['status'] == 'approved' ? '#065f46' : '#92400e'); ?>;">
                            <?php echo ucfirst($req['status']); ?>
                        </span>
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
        text: "Are you sure you want to logout?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#845c44',
        background: '#1c1917',
        color: '#fafaf9'
    }).then((result) => {
        if (result.isConfirmed) { window.location.href = '../auth/logout.php'; }
    });
}
</script>

</body>
</html>
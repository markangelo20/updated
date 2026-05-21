<?php
// 1. PHP ANTI-CACHE HEADERS - Pinipilit nito ang browser na magtanong sa server tuwing mag-ba-back button ang user
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

include "../config/db.php";
session_start();

// 2. SESSION GUARD - Dito iche-check kung may valid session. Kung wala o nag-logout na, bounce agad sa login page.
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'superadmin'){
    header("Location: ../auth/login.php");
    exit();
}

$page = basename($_SERVER['PHP_SELF']);

$msg_status = "";
$msg_text = "";

// === SALES REPORT LOGIC (Kalkulasyon mula sa Database) ===
// 1. Benta Ngayong Araw (Daily)
$sales_today_query = "SELECT SUM(total_amount) as total FROM orders WHERE DATE(created_at) = CURDATE()";
$sales_today_res = $conn->query($sales_today_query)->fetch_assoc();
$sales_today = $sales_today_res['total'] ? $sales_today_res['total'] : 0;

// 2. Benta Ngayong Linggo (Weekly - Mon to Sun)
$sales_week_query = "SELECT SUM(total_amount) as total FROM orders WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";
$sales_week_res = $conn->query($sales_week_query)->fetch_assoc();
$sales_week = $sales_week_res['total'] ? $sales_week_res['total'] : 0;

// 3. Benta Ngayong Buwan (Monthly)
$sales_month_query = "SELECT SUM(total_amount) as total FROM orders WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
$sales_month_res = $conn->query($sales_month_query)->fetch_assoc();
$sales_month = $sales_month_res['total'] ? $sales_month_res['total'] : 0;

// 4. Benta Ngayong Taon (Yearly)
$sales_year_query = "SELECT SUM(total_amount) as total FROM orders WHERE YEAR(created_at) = YEAR(CURDATE())";
$sales_year_res = $conn->query($sales_year_query)->fetch_assoc();
$sales_year = $sales_year_res['total'] ? $sales_year_res['total'] : 0;
// =========================================================

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_reset_password'])) {
    $admin_id = isset($_POST['admin_user_id']) ? intval($_POST['admin_user_id']) : 0;
    $new_pass = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
    $confirm_pass = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';

    if ($admin_id === 0 || empty($new_pass) || empty($confirm_pass)) {
        $msg_status = "error";
        $msg_text = "All fields are required. Please fill up the form completely.";
    } elseif ($new_pass !== $confirm_pass) {
        $msg_status = "error";
        $msg_text = "Passwords do not match. Please check and try again.";
    } elseif (strlen($new_pass) < 6) {
        $msg_status = "error";
        $msg_text = "Password must be at least 6 characters long for security purposes.";
    } else {
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND role = 'admin'");
        $check_stmt->bind_param("i", $admin_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
            
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_password, $admin_id);
            
            if ($update_stmt->execute()) {
                $msg_status = "success";
                $msg_text = "Admin password has been successfully updated!";
            } else {
                $msg_status = "error";
                $msg_text = "Database Error: Unable to update password. " . $conn->error;
            }
            $update_stmt->close();
        } else {
            $msg_status = "error";
            $msg_text = "Invalid User Account selected. Only admin accounts can be reset.";
        }
        $check_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <title>Cafe N Chill | Superadmin Dashboard</title>
    
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
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: var(--bg-color);
            color: var(--text-light);
        }

        .sidebar {
            width: 240px;
            height: 100vh;
            background: var(--sidebar-color);
            position: fixed;
            padding-top: 30px;
            border-right: 1px solid rgba(132, 92, 68, 0.2);
            z-index: 100;
        }

        .sidebar h2 {
            color: var(--cream-accent);
            text-align: center;
            font-size: 1.5rem;
            margin-bottom: 30px;
            letter-spacing: 2px;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #a8a29e;
            padding: 15px 25px;
            text-decoration: none;
            transition: 0.3s;
            font-size: 1rem;
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
            margin-left: 240px;
            padding: 40px;
        }

        .header {
            background: linear-gradient(135deg, var(--coffee-brown), #5a3e2e);
            color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            margin-bottom: 30px;
        }

        .header h2 { 
            margin: 0; 
            font-weight: 600; 
        }

        /* Bagong section wrapper para sa maayos na alignment */
        .section-title {
            font-size: 1.1rem;
            color: var(--cream-accent);
            margin: 25px 0 10px 0;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-weight: 600;
        }

        .cards {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 25px;
        }

        .card {
            flex: 1;
            min-width: 200px;
            padding: 22px;
            color: white;
            border-radius: 15px;
            text-align: left;
            position: relative;
            overflow: hidden;
            transition: 0.3s;
        }

        .card:hover { 
            transform: translateY(-5px); 
        }

        .card h3 { 
            margin: 0; 
            font-size: 0.9rem; 
            opacity: 0.8; 
        }
        
        .card p { 
            margin: 10px 0 0; 
            font-size: 2rem; 
            font-weight: 700; 
        }

        .coffee-card { 
            background: var(--card-bg); 
            border-bottom: 4px solid var(--coffee-brown); 
        }
        
        .archived-card { 
            background: var(--card-bg); 
            border-bottom: 4px solid #ef4444; 
        }

        /* Bagong border designs para sa bawat report card */
        .sales-day { border-bottom: 4px solid #38bdf8; }
        .sales-week { border-bottom: 4px solid #fbbf24; }
        .sales-month { border-bottom: 4px solid #34d399; }
        .sales-year { border-bottom: 4px solid #a78bfa; }

        .add-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: var(--coffee-brown);
            color: white;
            text-decoration: none;
            margin: 15px 0 25px 0;
            border-radius: 8px;
            font-weight: 600;
            transition: 0.3s;
        }

        .add-btn:hover { 
            background: var(--cream-accent); 
            color: var(--bg-color); 
        }

        .table-container {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            background: rgba(132, 92, 68, 0.1);
            color: var(--cream-accent);
            padding: 15px;
            font-weight: 600;
            border-bottom: 2px solid #3f3f46;
        }

        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #3f3f46;
            color: #d1d5db;
        }

        .status-pill {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            background: rgba(34, 197, 94, 0.1);
            color: #4ade80;
        }

        .action-link {
            color: #ef4444;
            text-decoration: none;
            font-weight: 600;
        }

        .action-link:hover { 
            text-decoration: underline; 
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .modal-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        .modal-container {
            background: var(--sidebar-color);
            width: 100%;
            max-width: 480px;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.6);
            border: 1px solid rgba(132, 92, 68, 0.25);
            transform: scale(0.85);
            transition: all 0.3s ease;
        }

        .modal-overlay.active .modal-container {
            transform: scale(1);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #3f3f46;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .modal-header h3 {
            margin: 0;
            color: var(--cream-accent);
            font-size: 1.25rem;
            font-weight: 600;
        }

        .modal-close-btn {
            background: none;
            border: none;
            color: #a8a29e;
            font-size: 1.2rem;
            cursor: pointer;
            transition: 0.2s;
        }

        .modal-close-btn:hover {
            color: #ef4444;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #d1d5db;
            font-size: 0.9rem;
        }

        .form-group select {
            width: 100%;
            padding: 12px;
            background: var(--card-bg);
            border: 1px solid #3f3f46;
            border-radius: 8px;
            color: white;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            box-sizing: border-box;
            outline: none;
            transition: 0.3s;
        }

        .form-group select:focus {
            border-color: var(--coffee-brown);
        }

        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-wrapper input {
            width: 100%;
            padding: 12px 45px 12px 12px;
            background: var(--card-bg);
            border: 1px solid #3f3f46;
            border-radius: 8px;
            color: white;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            box-sizing: border-box;
            outline: none;
            transition: 0.3s;
        }

        .password-wrapper input:focus {
            border-color: var(--coffee-brown);
        }

        .toggle-icon {
            position: absolute;
            right: 15px;
            color: #a8a29e;
            cursor: pointer;
            user-select: none;
            transition: 0.2s;
        }

        .toggle-icon:hover {
            color: var(--cream-accent);
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 25px;
        }

        .btn-cancel {
            padding: 10px 20px;
            background: transparent;
            border: 1px solid #3f3f46;
            color: #a8a29e;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.2s;
        }

        .btn-cancel:hover {
            background: rgba(255,255,255,0.05);
            color: white;
        }

        .btn-submit {
            padding: 10px 20px;
            background: var(--coffee-brown);
            border: none;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.2s;
        }

        .btn-submit:hover {
            background: var(--cream-accent);
            color: var(--bg-color);
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>CAFE N CHILL</h2>

    <a href="superadmin_dashboard.php" class="<?= $page == 'superadmin_dashboard.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-house"></i> Dashboard
    </a>
    
    <a href="add_admin.php" class="<?= $page == 'add_admin.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-user-plus"></i> Add Admin
    </a>
    
    <a href="javascript:void(0);" onclick="openResetModal()" id="resetNavBtn">
        <i class="fa-solid fa-key"></i> Reset Admin Password
    </a>
    
    <a href="archived_admin.php" class="<?= $page == 'archived_admin.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-box-archive"></i> Archived
    </a>
    
    <a onclick="confirmLogout()" style="margin-top: 50px; color: #f87171;">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
    </a>
</div>

<div class="main">

    <div class="header">
        <h2>Welcome</h2>
        <p style="margin: 5px 0 0; opacity: 0.8;">CAFE N CHILL Inventory Management System!</p>
    </div>

    <div class="section-title"><i class="fa-solid fa-users-gear"></i> System Accounts</div>
    <div class="cards">
        <div class="card coffee-card">
            <h3>Total Active Admins</h3>
            <p>
                <?php
                $active_query = "SELECT COUNT(*) as total FROM users WHERE role='admin' AND status='active'";
                echo $conn->query($active_query)->fetch_assoc()['total'];
                ?>
            </p>
        </div>

        <div class="card archived-card">
            <h3>Archived Admins</h3>
            <p>
                <?php
                $archived_query = "SELECT COUNT(*) as total FROM users WHERE role='admin' AND status='archived'";
                echo $conn->query($archived_query)->fetch_assoc()['total'];
                ?>
            </p>
        </div>
    </div>

    <div class="section-title"><i class="fa-solid fa-chart-line"></i> Sales Overview</div>
    <div class="cards">
        <div class="card var(--card-bg) sales-day">
            <h3>Daily Sales (Today)</h3>
            <p>₱<?= number_format($sales_today, 2) ?></p>
        </div>

        <div class="card var(--card-bg) sales-week">
            <h3>Weekly Sales</h3>
            <p>₱<?= number_format($sales_week, 2) ?></p>
        </div>

        <div class="card var(--card-bg) sales-month">
            <h3>Monthly Sales</h3>
            <p>₱<?= number_format($sales_month, 2) ?></p>
        </div>

        <div class="card var(--card-bg) sales-year">
            <h3>Yearly Sales</h3>
            <p>₱<?= number_format($sales_year, 2) ?></p>
        </div>
    </div>

    <a class="add-btn" href="add_admin.php">
        <i class="fa-solid fa-plus"></i> Add New Admin
    </a>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res = $conn->query("SELECT * FROM users WHERE role='admin' AND status='active'");
                while($r=$res->fetch_assoc()){
                ?>
                <tr>
                    <td>
                        <i class="fa-solid fa-user-tie" style="color: var(--cream-accent); margin-right: 10px;"></i> 
                        <?= htmlspecialchars($r['username']) ?>
                    </td>
                    <td>
                        <span class="status-pill"><?= ucfirst($r['status']) ?></span>
                    </td>
                    <td>
                        <a class="action-link" href="archive_admin.php?id=<?= $r['id'] ?>">
                            <i class="fa-solid fa-folder-minus"></i> Archive
                        </a> 
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

</div>

<div class="modal-overlay" id="resetPasswordModal">
    <div class="modal-container">
        <div class="modal-header">
            <h3><i class="fa-solid fa-key" style="margin-right: 8px;"></i> Reset Admin Password</h3>
            <button class="modal-close-btn" onclick="closeResetModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST" action="">
            <div class="form-group">
                <label for="admin_user_id">Select Admin Account</label>
                <select name="admin_user_id" id="admin_user_id" required>
                    <option value="" disabled selected>Choose Admin Account</option>
                    <?php
                    $admin_fetch = $conn->query("SELECT id, username FROM users WHERE role='admin' AND status='active' ORDER BY username ASC");
                    while($admin_row = $admin_fetch->fetch_assoc()) {
                        echo "<option value='".intval($admin_row['id'])."'>@".htmlspecialchars($admin_row['username'])."</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="new_password">New Password</label>
                <div class="password-wrapper">
                    <input type="password" name="new_password" id="new_password" placeholder="Enter new password (min. 6 chars)" required>
                    <i class="fa-solid fa-eye toggle-icon" onclick="togglePasswordVisibility('new_password', this)"></i>
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <div class="password-wrapper">
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Re-type new password" required>
                    <i class="fa-solid fa-eye toggle-icon" onclick="togglePasswordVisibility('confirm_password', this)"></i>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeResetModal()">Cancel</button>
                <button type="submit" name="btn_reset_password" class="btn-submit">Update Password</button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmLogout() {
    Swal.fire({
        title: 'Are you sure you want to logout?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#845c44',
        cancelButtonColor: '#292524', 
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
        background: '#1c1917',         
        color: '#fafaf9'          
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "../auth/logout.php";
        }
    })
}

function openResetModal() {
    const modal = document.getElementById('resetPasswordModal');
    const navBtn = document.getElementById('resetNavBtn');
    
    if(modal) {
        modal.classList.add('active');
    }
    if(navBtn) {
        navBtn.classList.add('active');
        navBtn.style.background = "var(--coffee-brown)";
        navBtn.style.color = "white";
        navBtn.style.borderLeft = "4px solid var(--cream-accent)";
    }
}

function closeResetModal() {
    const modal = document.getElementById('resetPasswordModal');
    const navBtn = document.getElementById('resetNavBtn');
    
    if(modal) {
        modal.classList.remove('active');
    }
    if(navBtn) {
        navBtn.classList.remove('active');
        navBtn.style.background = "transparent";
        navBtn.style.color = "#a8a29e";
        navBtn.style.borderLeft = "none";
    }
    
    document.getElementById('admin_user_id').value = "";
    document.getElementById('new_password').value = "";
    document.getElementById('confirm_password').value = "";
    
    const icons = document.querySelectorAll('.toggle-icon');
    icons.forEach(icon => {
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    });
}

function togglePasswordVisibility(inputId, iconElement) {
    const inputField = document.getElementById(inputId);
    if (inputField.type === "password") {
        inputField.type = "text";
        iconElement.classList.remove('fa-eye');
        iconElement.classList.add('fa-eye-slash');
    } else {
        inputField.type = "password";
        iconElement.classList.remove('fa-eye-slash');
        iconElement.classList.add('fa-eye');
    }
}

window.onclick = function(event) {
    const modal = document.getElementById('resetPasswordModal');
    if (event.target == modal) {
        closeResetModal();
    }
}
</script>

<?php if(!empty($msg_status) && !empty($msg_text)): ?>
<script>
    Swal.fire({
        icon: '<?= $msg_status ?>',
        title: '<?= $msg_status == "success" ? "Success!" : "Notice!" ?>',
        text: '<?= $msg_text ?>',
        confirmButtonColor: '#845c44',
        background: '#1c1917',
        color: '#fafaf9'
    });
</script>
<?php endif; ?>

</body>
</html>
<?php
session_start();
include "../config/db.php";

// SECURITY CHECK - Admin access only
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

$success = false;
$error_msg = "";

// Get active users list for the dropdown
$users_query = "SELECT id, username, full_name FROM users WHERE status = 'active' ORDER BY full_name ASC";
$users_result = $conn->query($users_query);

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $user_to_reset = $_POST['user_id'];
    $current_admin_password = $_POST['current_admin_password']; 
    $new_password = $_POST['new_password'];
    $admin_id = $_SESSION['user']['id'];

    // 1. Verify the current Admin's password first
    $check_admin = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $check_admin->bind_param("i", $admin_id);
    $check_admin->execute();
    $admin_res = $check_admin->get_result();
    $admin_data = $admin_res->fetch_assoc();

    if($admin_data && password_verify($current_admin_password, $admin_data['password'])){
        // 2. If admin password is correct, update the target user's password
        $hashed_new = password_hash($new_password, PASSWORD_BCRYPT);
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update_stmt->bind_param("si", $hashed_new, $user_to_reset);

        if($update_stmt->execute()){
            $success = true;
        } else {
            $error_msg = "Database error. Please try again.";
        }
    } else {
        $error_msg = "Incorrect current password. Authorization failed.";
    }
}

$page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cafe N Chill | Reset Password</title>
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
            --danger: #f87171;
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
            width: calc(100% - 260px);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            box-sizing: border-box;
        }

        .form-container {
            background: var(--card-bg);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            border: 1px solid rgba(132, 92, 68, 0.2);
            margin-top: 20px; 
        }

        h1 {
            color: var(--cream-accent);
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
        }

        .input-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.85rem;
            color: #a8a29e;
        }

        input, select {
            width: 100%;
            padding: 12px 15px;
            background: #1c1917;
            border: 1px solid #444;
            border-radius: 10px;
            color: white;
            font-family: inherit;
            outline: none;
            box-sizing: border-box;
            transition: 0.3s;
        }

        input:focus, select:focus {
            border-color: var(--coffee-brown);
        }

        select option {
            background: var(--sidebar-color);
        }

        button {
            width: 100%;
            padding: 15px;
            background: var(--coffee-brown);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        button:hover {
            background: var(--cream-accent);
            transform: translateY(-2px);
        }

        .cancel-link {
            margin-top: 20px;
            display: block;
            text-align: center;
            color: #a8a29e;
            text-decoration: none;
            font-size: 0.85rem;
        }

        .alert-info {
            background: rgba(212, 163, 115, 0.1);
            border-left: 4px solid var(--cream-accent);
            padding: 15px;
            margin-bottom: 20px;
            font-size: 0.8rem;
            color: #d4a373;
            line-height: 1.5;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>CAFE N CHILL</h2>
    <a href="admin_dashboard.php" class="<?= ($page == 'admin_dashboard.php') ? 'active' : '' ?>">
        <i class="fa-solid fa-gauge"></i> Dashboard
    </a>
    <a href="add_item.php"><i class="fa-solid fa-plus"></i> Add Item</a>
    <a href="view_items.php"><i class="fa-solid fa-box-open"></i> View Items</a>
    <a href="update_item.php"><i class="fa-solid fa-pen-to-square"></i> Update Item</a>
    <a href="archived_items.php"><i class="fa-solid fa-box-archive"></i> Archived Items</a>
    <a href="view_user.php" ><i class="fa-solid fa-users"></i> View Users</a>
    <a href="add_user.php"><i class="fa-solid fa-user-plus"></i> Add User</a>
    <a href="archived_users.php"><i class="fa-solid fa-user-slash"></i> Archived Users</a>
    <a href="reset_password.php" class="active"><i class="fa-solid fa-key"></i> Reset Password</a>
    <a href="approve_item.php"><i class="fa-solid fa-check-double"></i> Approve Items</a>
    
    <!-- Updated Logout Section -->
    <a onclick="confirmLogout()" style="margin-top: 20px; color: #f87171; cursor: pointer;">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
    </a>
</div>

<div class="main">
    <div class="form-container">
        <h1><i class="fa-solid fa-shield-halved"></i> Reset Password</h1>
        
        <div class="alert-info">
            <i class="fa-solid fa-circle-info"></i> <strong>Authorization Required:</strong> Please enter your current admin password to confirm this action.
        </div>

        <form method="POST" autocomplete="off">
            <div class="input-group">
                <label>Select User Account</label>
                <select name="user_id" required>
                    <option value="" disabled selected>Select an account...</option>
                    <?php while($row = $users_result->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>">
                            <?= htmlspecialchars($row['full_name']) ?> (<?= htmlspecialchars($row['username']) ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="input-group">
                <label>Current Admin Password</label>
                <input type="password" name="current_admin_password" placeholder="Verify your password" required>
            </div>

            <div class="input-group">
                <label>New Password for User</label>
                <input type="password" name="new_password" placeholder="Enter new password" required minlength="6">
            </div>

            <button type="submit">Update Password</button>
        </form>

        <a href="admin_dashboard.php" class="cancel-link">Cancel and Return</a>
    </div>
</div>

<script>
    // Logout function katulad ng sa dashboard
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

    <?php if($success){ ?>
    Swal.fire({
        title: 'Success!',
        text: 'The password has been successfully updated.',
        icon: 'success',
        confirmButtonColor: '#845c44',
        background: '#1c1917',
        color: '#fafaf9'
    }).then(() => { window.location.href = 'admin_dashboard.php'; });
    <?php } ?>

    <?php if($error_msg){ ?>
    Swal.fire({
        title: 'Error!',
        text: '<?php echo $error_msg; ?>',
        icon: 'error',
        confirmButtonColor: '#845c44',
        background: '#1c1917',
        color: '#fafaf9'
    });
    <?php } ?>
</script>

</body>
</html>
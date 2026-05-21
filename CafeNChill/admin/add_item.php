<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

$success = false;
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $n = trim($_POST['name']);
    $c = trim($_POST['category']);
    $q = intval($_POST['quantity']);
    $uom = trim($_POST['uom']); // Bagong Field: Unit of Measurement
    $cost = floatval($_POST['cost_price']); // Bagong Field: Puhunan
    $price = floatval($_POST['selling_price']); // Bagong Field: Benta
    $desc = trim($_POST['description']); // Bagong Field: Deskripsyon
    
    $u_id = $_SESSION['user']['id']; 
    $status = "pending"; 

    // In-update ang INSERT query para pumasok ang mga bagong professional fields sa database niyo
    // Data bindings: s = string, i = integer, d = double/float
    $stmt = $conn->prepare("INSERT INTO item_requests (name, category, quantity, uom, cost_price, selling_price, description, user_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisddsis", $n, $c, $q, $uom, $cost, $price, $desc, $u_id, $status);

    if($stmt->execute()){
        $success = true;
    }
}

$page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cafe N Chill | Add Item</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');

        :root {
            --bg-color: #0c0a09;
            --sidebar-color: #1c1917;
            --coffee-brown: #845c44;
            --cream-accent: #d4a373;
            --text-light: #fafaf9;
            --card-bg: #292524;
            --input-bg: #1c1917;
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
            padding: 40px 20px;
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
            max-width: 600px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            border: 1px solid rgba(132, 92, 68, 0.2);
        }

        <h1> {
            color: var(--cream-accent);
            margin-bottom: 5px;
            text-align: center;
            font-weight: 600;
        }

        .input-group { margin-bottom: 18px; }
        .row-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        
        label { display: block; margin-bottom: 8px; font-size: 0.8rem; color: #a8a29e; text-transform: uppercase; letter-spacing: 0.5px; }

        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            background: var(--input-bg);
            border: 1px solid #444;
            border-radius: 10px;
            color: white;
            outline: none;
            box-sizing: border-box;
            transition: 0.3s;
            font-family: 'Poppins', sans-serif;
        }

        input:focus, select:focus, textarea:focus { border-color: var(--coffee-brown); background: #221f1d; }

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

        button:hover { background: #6b4a36; transform: translateY(-2px); }

        .back-link { 
            display: inline-block;
            margin-top: 20px; 
            color: #a8a29e; 
            text-decoration: none; 
            font-size: 0.85rem;
            transition: 0.3s;
        }
        .back-link:hover { color: var(--cream-accent); }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>CAFE N CHILL</h2>
    <a href="admin_dashboard.php" class="<?= $page == 'admin_dashboard.php' ? 'active' : '' ?>"><i class="fa-solid fa-gauge"></i> Dashboard</a>
    <a href="add_item.php" class="<?= $page == 'add_item.php' ? 'active' : '' ?>"><i class="fa-solid fa-plus"></i> Add Item</a>
    <a href="view_items.php"><i class="fa-solid fa-box-open"></i> View Items</a>
    <a href="update_item.php"><i class="fa-solid fa-pen-to-square"></i> Update Item</a>
    <a href="archived_items.php"><i class="fa-solid fa-box-archive"></i> Archived Items</a>
    <a href="view_user.php"><i class="fa-solid fa-users"></i> View Users</a>
    <a href="add_user.php"><i class="fa-solid fa-user-plus"></i> Add User</a>
    <a href="archived_users.php"><i class="fa-solid fa-user-slash"></i> Archived Users</a>
    <a href="reset_password.php"><i class="fa-solid fa-key"></i> Reset Password</a>
    <a href="approve_item.php"><i class="fa-solid fa-check-double"></i> Approve Items</a>
    <a onclick="confirmLogout()" style="margin-top: 20px; color: #f87171; cursor: pointer;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</div>

<div class="main">
    <div class="form-container">
        <h1><i class="fa-solid fa-mug-hot"></i> New Item</h1>
        <p style="text-align: center; color: #d4a373; font-size: 0.8rem; margin-bottom: 25px; opacity: 0.8;">Waiting for Admin Approval</p>
        
        <form method="POST">
            <div class="row-grid">
                <div class="input-group">
                    <label>Item Name</label>
                    <input type="text" name="name" placeholder="e.g. Arabica Beans" required>
                </div>
                <div class="input-group">
                    <label>Category</label>
                    <input type="text" name="category" placeholder="e.g. Coffee" required>
                </div>
            </div>

            <div class="row-grid">
                <div class="input-group">
                    <label>Initial Quantity</label>
                    <input type="number" name="quantity" min="0" placeholder="0" required>
                </div>
                <div class="input-group">
                    <label>Unit (UOM)</label>
                    <select name="uom" required>
                        <option value="pcs">Piece (pcs)</option>
                        <option value="12oz">12 oz (Small Size)</option>
                        <option value="16oz">16 oz (Medium Size)</option>
                        <option value="22oz">22 oz (Large Size)</option>
                    </select>
                </div>
            </div>

            <div class="row-grid">
                <div class="input-group">
                    <label>Cost Price / Puhunan (₱)</label>
                    <input type="number" step="0.01" min="0" name="cost_price" placeholder="0.00" required>
                </div>
                <div class="input-group">
                    <label>Selling Price / Benta (₱)</label>
                    <input type="number" step="0.01" min="0" name="selling_price" placeholder="0.00" required>
                </div>
            </div>

            <div class="input-group">
                <label>Item Description / Specifications</label>
                <textarea name="description" rows="3" placeholder="Enter item notes, supplier details, or descriptions here..."></textarea>
            </div>

            <button type="submit">Confirm Add Item</button>
        </form>

        <div style="text-align: center;">
            <a href="admin_dashboard.php" class="back-link">Cancel and go back</a>
        </div>
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

<?php if($success){ ?>
<script>
    Swal.fire({
        title: 'Request Submitted!',
        text: 'Waiting for Admin approval.',
        icon: 'info',
        confirmButtonColor: '#845c44',
        background: '#1c1917',
        color: '#fafaf9'
    }).then(() => {
        window.location.href = 'add_item.php';
    });
</script>
<?php } ?>

</body>
</html>
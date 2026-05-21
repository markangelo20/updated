<?php
include "../config/db.php";
session_start();

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'superadmin'){
    header("Location: ../auth/login.php");
    exit();
}

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $name = trim($_POST['name']);
    $user = trim($_POST['username']);
    $pass = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $check = $conn->prepare("SELECT id FROM users WHERE username=?");
    $check->bind_param("s", $user);
    $check->execute();
    $result = $check->get_result();

    if($result->num_rows > 0){
        $error = "Username'$user' is already in use!";
    } else {

        $role = "admin";
        $status = "active";

        $stmt = $conn->prepare("
            INSERT INTO users(full_name, username, password, role, status)
            VALUES(?,?,?,?,?)
        ");

        $stmt->bind_param("sssss", $name, $user, $pass, $role, $status);
        
        if($stmt->execute()){
            $success = true; 
        } else {
            $error = "Nagkaroon ng error sa pag-save.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cafe N Chill | Add Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #0c0a09;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #fafaf9;
            overflow: hidden;
        }

        body::before {
            content: "";
            position: absolute;
            width: 100%;
            height: 100%;
            background: url('https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?q=80&w=2070') center/cover;
            opacity: 0.15;
            z-index: -1;
        }

        .card {
            background: #1c1917;
            padding: 40px;
            width: 450px;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            border: 1px solid rgba(132, 92, 68, 0.3);
            position: relative;
        }

        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            color: #a8a29e;
            text-decoration: none;
            font-size: 0.9rem;
            transition: 0.3s;
        }
        .back-btn:hover { color: #d4a373; }

        h2 {
            text-align: center;
            color: #d4a373;
            margin-bottom: 30px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .input-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.85rem;
            color: #a8a29e;
        }

        input {
            width: 100%;
            padding: 14px;
            background: #292524;
            border: 1px solid #444;
            border-radius: 12px;
            color: white;
            outline: none;
            box-sizing: border-box;
            transition: 0.3s;
        }

        input:focus {
            border-color: #845c44;
            background: #1c1917;
        }

        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-wrapper input {
            padding-right: 45px;
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
            color: #d4a373;
        }

        button {
            width: 100%;
            padding: 15px;
            background: #845c44;
            border: none;
            color: white;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            transition: 0.3s;
        }

        button:hover {
            background: #d4a373;
            transform: translateY(-2px);
        }

        .error {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
            font-size: 0.9rem;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
    </style>
</head>
<body>

<div class="card">
    <a href="superadmin_dashboard.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>

    <h2><i class="fa-solid fa-user-plus"></i> New Admin</h2>

    <?php if(isset($error)){ ?>
        <div class="error"><?= $error ?></div>
    <?php } ?>

    <form method="POST" autocomplete="off">
        <input type="text" name="fake_user" style="display:none" aria-hidden="true">
        <input type="password" name="fake_pass" style="display:none" aria-hidden="true">

        <div class="input-group">
            <label>Full Name</label>
            <input type="text" name="name" placeholder="Enter Name" required autocomplete="off">
        </div>

        <div class="input-group">
            <label>Username</label>
            <input type="text" name="username" placeholder="Enter Username" required autocomplete="new-password">
        </div>

        <div class="input-group">
            <label>Password</label>
            <div class="password-wrapper">
                <input id="admin_password" name="password" type="password" placeholder="Enter Password" required autocomplete="new-password">
                <i class="fa-solid fa-eye toggle-icon" onclick="togglePassword()"></i>
            </div>
        </div>

        <button type="submit">Create Admin Account</button>
    </form>
</div>

<script>
function togglePassword() {
    const passField = document.getElementById("admin_password");
    const icon = document.querySelector(".toggle-icon");
    
    if (passField.type === "password") {
        passField.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        passField.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}
</script>

<?php if(isset($success) && $success){ ?>
<script>
    Swal.fire({
        title: 'Success!',
        text: 'New admin account has been created successfully.',
        icon: 'success',
        confirmButtonColor: '#845c44',
        background: '#1c1917',
        color: '#fafaf9'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'superadmin_dashboard.php';
        }
    });
</script>
<?php } ?>

</body>
</html>
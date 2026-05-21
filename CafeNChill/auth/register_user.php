<?php
include "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['full_name'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    $role = 'user'; 

 
    $stmt = $conn->prepare("INSERT INTO users (full_name, username, password, role, status) VALUES (?, ?, ?, ?, 'active')");
    $stmt->bind_param("ssss", $fullname, $username, $password, $role);

    if ($stmt->execute()) {
        echo "<script>alert('Registration Successful!'); window.location='login.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Staff Registration</title>
    <style>
        body { background: #0c0a09; color: white; font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .reg-card { background: #1c1917; padding: 30px; border-radius: 15px; border: 1px solid #845c44; width: 350px; }
        input { width: 100%; padding: 10px; margin: 10px 0; background: #0c0a09; border: 1px solid #333; color: white; border-radius: 5px; }
        .btn { width: 100%; background: #845c44; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="reg-card">
        <h2>Staff Registration</h2>
        <form method="POST">
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn">Create Account</button>
        </form>
        <p style="font-size: 12px; text-align: center; margin-top: 10px;">
            Already have an account? <a href="login.php" style="color: #845c44;">Login here</a>
        </p>
    </div>
</body>
</html>
<?php
include "../config/db.php";
session_start();


if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'superadmin'){
    die("ACCESS DENIED");
}

if(isset($_GET['id'])){
    $id = $_GET['id'];
    $status = "archived";

   
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'admin'");
    $stmt->bind_param("si", $status, $id);

    if($stmt->execute()){
     
        header("Location: superadmin_dashboard.php?archived=success");
    } else {
        echo "Error archiving admin.";
    }
} else {
    header("Location: superadmin_dashboard.php");
}
exit();
?>
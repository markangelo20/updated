<?php
include "../config/db.php";
session_start();

// SECURITY CHECK - Superadmin access only
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'superadmin'){
    die("ACCESS DENIED");
}

$msg_status = "";
$msg_text = "";

// === RESTORE HANDLER ===
if (isset($_GET['restore_id'])) {
    $restore_id = intval($_GET['restore_id']);
    
    // I-update ang status ng user pabalik sa 'active'
    $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ? AND role = 'admin'");
    $stmt->bind_param("i", $restore_id);
    
    if ($stmt->execute()) {
        $msg_status = "success";
        $msg_text = "Admin account has been successfully restored!";
    } else {
        $msg_status = "error";
        $msg_text = "Database Error: Unable to restore account.";
    }
    $stmt->close();
}

// Kunin muli ang mga natitirang naka-archive
$res = $conn->query("SELECT * FROM users WHERE role='admin' AND status='archived'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cafe N Chill | Archived Admins</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background: #0c0a09;
            color: #fafaf9;
            margin: 0;
            padding: 40px;
        }

        .container {
            max-width: 950px;
            margin: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        h2 { color: #f87171; margin: 0; }

        .back-btn {
            text-decoration: none;
            color: #a8a29e;
            font-size: 0.9rem;
            transition: 0.3s;
        }
        .back-btn:hover { color: #d4a373; }

        .table-container {
            background: #1c1917;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            padding: 15px;
            border-bottom: 2px solid #3f3f46;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #3f3f46;
            color: #d1d5db;
        }

        .status-archived {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        /* Kulay para sa bagong Restore Link/Button */
        .restore-link {
            color: #4ade80;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .restore-link:hover {
            text-decoration: underline;
            color: #22c55e;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2><i class="fa-solid fa-trash-can"></i> Archived Admins</h2>
        <a href="superadmin_dashboard.php" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Status</th>
                    <th>Date Archived</th>
                    <th>Action</th> </tr>
            </thead>
            <tbody>
                <?php while($row = $res->fetch_assoc()){ ?>
                <tr>
                    <td>
                        <i class="fa-solid fa-user-slash" style="margin-right: 10px; opacity: 0.5;"></i> 
                        <?= htmlspecialchars($row['username']) ?>
                    </td>
                    <td>
                        <span class="status-archived"><?= ucfirst($row['status']) ?></span>
                    </td>
                    <td style="font-size: 0.85rem; opacity: 0.6;">Recently</td>
                    <td>
                        <a class="restore-link" onclick="confirmRestore(<?= $row['id'] ?>, '<?= htmlspecialchars($row['username']) ?>')">
                            <i class="fa-solid fa-rotate-left"></i> Restore
                        </a>
                    </td>
                </tr>
                <?php } ?>
                
                <?php if($res->num_rows == 0){ ?>
                <tr>
                    <td colspan="4" style="text-align: center; padding: 30px; opacity: 0.5;">
                        No archived accounts found.
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// JavaScript function para magtanong muna bago mag-restore
function confirmRestore(userId, username) {
    Swal.fire({
        title: 'Restore Account?',
        text: "Do you want to restore the admin account of @" + username + "?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4ade80',
        cancelButtonColor: '#292524', 
        confirmButtonText: 'Yes, Restore it!',
        cancelButtonText: 'Cancel',
        background: '#1c1917',         
        color: '#fafaf9'          
    }).then((result) => {
        if (result.isConfirmed) {
            // Ire-refresh ang page na may kasamang ?restore_id sa URL para ma-trigger ang PHP handler
            window.location.href = "archived_admin.php?restore_id=" + userId;
        }
    })
}
</script>

<?php if(!empty($msg_status) && !empty($msg_text)): ?>
<script>
    Swal.fire({
        icon: '<?= $msg_status ?>',
        title: '<?= $msg_status == "success" ? "Restored!" : "Notice!" ?>',
        text: '<?= $msg_text ?>',
        confirmButtonColor: '#845c44',
        background: '#1c1917',
        color: '#fafaf9'
    }).then(() => {
        // Lilinisin ang URL parameter pagkatapos mag-click ng OK para hindi mag-loop ang alert
        window.location.href = "archived_admin.php";
    });
</script>
<?php endif; ?>

</body>
</html>

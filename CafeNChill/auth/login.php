<?php
session_start();
include "../config/db.php"; 

$error = "";

if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']); 
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $u = trim($_POST['username']); 
    $p = $_POST['password'];
    $selected_role = $_POST['role'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $u);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
    
        if ($row['status'] !== 'active') {
            $_SESSION['login_error'] = "Account Error: Your account status is '" . $row['status'] . "'.";
        } 
        else if (password_verify($p, $row['password'])) {
          
            if ($row['role'] === $selected_role) {
                $_SESSION['user'] = $row;

                if (isset($_POST['remember'])) {
                    setcookie("user_login", $u, time() + (30 * 24 * 60 * 60), "/");
                }
                
               
                if ($row['role'] === 'superadmin') {
                    header("Location: ../superadmin/superadmin_dashboard.php");
                } elseif ($row['role'] === 'admin') {
                    header("Location: ../admin/admin_dashboard.php");
                } elseif ($row['role'] === 'user') {
                    header("Location: ../user/user_home.php");
                } else {
                  
                    header("Location: login.php");
                }
                exit;
                // ---------------------------------

            } else {
                $_SESSION['login_error'] = "Role Error: Ang account mo ay '" . $row['role'] . "' pero ang pinili mo ay '" . $selected_role . "'.";
            }
        } else {
            $_SESSION['login_error'] = "Password Error: Hindi tugma ang password para kay " . $u . ".";
        }
    } else {
        $_SESSION['login_error'] = "User Error: Ang username na '" . $u . "' ay wala sa database.";
    }

    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <title>KATAMBAY | Management System</title>
    
    <style>
     
        * {
            margin: 0; padding: 0; box-sizing: border-box;
            text-decoration: none; border: none; outline: none;
            scroll-behavior: smooth; font-family: 'Poppins', sans-serif;
        }

        :root {
            --bg-color: #0c0a09;
            --second-bg-color: #1c1917;
            --text-color: #fafaf9;
            --main-color: #845c44;
            --accent-color: #d6d3d1;
        }

        html { font-size: 62.5%; } 

        body {
            background: var(--bg-color);
            color: var(--text-color);
            overflow-x: hidden;
        }

        
        header {
            position: fixed; top: 0; left: 0; width: 100%;
            padding: 1.5rem 9%; 
            background: rgba(12, 10, 9, 0.85);
            backdrop-filter: blur(10px); display: flex;
            justify-content: space-between; align-items: center; z-index: 100;
            border-bottom: 1px solid rgba(132, 92, 68, 0.1);
        }

        .logo { display: flex; align-items: center; }
        .logo img { height: 50px; width: auto; transition: 0.3s; margin-left: 160px; }
        .logo img:hover { transform: scale(1.05); }

       
        section { min-height: 100vh; padding: 10rem 9% 2rem; display: flex; align-items: center; justify-content: center; }

        .home { display: flex; align-items: center; justify-content: center; gap: 8rem; }
        
        .home-content h1 { font-size: 4.5rem; font-weight: 700; line-height: 1.2; margin-left: -2px; }
        .home-content h1 span { color: var(--main-color); }
        .home-content h3 { font-size: 2.4rem; margin-bottom: 1.5rem; color: var(--accent-color); }
        .home-content p { font-size: 1.4rem; margin-bottom: 3rem; color: #a8a29e; max-width: 500px; line-height: 1.6; }

       
        .btn {
            display: inline-block; padding: 1.2rem 3rem; background: var(--main-color);
            border-radius: 4rem; font-size: 1.5rem; color: white; border: 2px solid var(--main-color);
            font-weight: 600; transition: 0.3s ease; cursor: pointer;
        }
        .btn:hover { background: transparent; color: var(--main-color); transform: scale(1.05); }

       
        .home-img .img-box { width: 30vw; min-width: 300px; }
        .home-img .img-box img { width: 100%; filter: drop-shadow(0 0 15px rgba(132, 92, 68, 0.4)); }

      
        .modal {
            display: none; position: fixed; z-index: 2000;
            left: 0; top: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.85); backdrop-filter: blur(8px);
            align-items: center; justify-content: center;
        }

        .modal-content {
            background: var(--second-bg-color); padding: 45px 35px; border-radius: 24px;
            width: 400px; text-align: center; border: 1px solid var(--main-color);
            box-shadow: 0 10px 40px rgba(0,0,0,0.6); position: relative;
            animation: modalFade 0.4s ease;
        }

        @keyframes modalFade { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }

        #loginInputs { display: none; margin-top: 20px; }
        .back-btn { cursor: pointer; color: var(--main-color); position: absolute; left: 25px; top: 25px; font-size: 1.8rem; }

        .modal-content input[type="text"], 
        .modal-content input[type="password"] {
            width: 100%; padding: 14px; margin: 10px 0; background: #0c0a09;
            border: 1px solid #333; color: white; border-radius: 10px; font-size: 1.4rem;
        }

      
        .remember-me-container {
            display: flex; justify-content: space-between; align-items: center;
            margin: 10px 5px 20px; font-size: 1.2rem;
        }

        .remember-me-label { display: flex; align-items: center; gap: 8px; color: #a8a29e; cursor: pointer; }
        .remember-me-label input { accent-color: var(--main-color); width: 16px; height: 16px; }
        .forgot-link { color: var(--main-color); font-weight: 500; }

    
        .role-grid { display: grid; grid-template-columns: 1fr; gap: 12px; margin-top: 25px; }
        .role-btn {
            padding: 15px; background: #292524; border-radius: 10px;
            color: white; cursor: pointer; font-weight: 600; font-size: 1.4rem;
            transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .role-btn:hover { background: var(--main-color); transform: translateY(-2px); }

        .error-msg { background: rgba(239, 68, 68, 0.1); color: #f87171; padding: 10px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #ef4444; font-size: 1.2rem; }
        .close-modal { margin-top: 20px; display: inline-block; color: #78716c; cursor: pointer; font-size: 1.2rem; }

        @media (max-width: 991px) {
            .home { flex-direction: column-reverse; text-align: center; gap: 4rem; }
            header { padding: 1.5rem 5%; }
            section { padding: 10rem 5% 2rem; }
        }
    </style>
</head>
<body>
    <header>
        <a href="#" class="logo">
            <img src="../asset/logo.png" alt="KATAMBAY Logo">
        </a>
    </header>

    <section class="home" id="home">
        <div class="home-content">
            <h1>Welcome to <span></span></h1>
            <h3><span class="typewriter"></span></h3>
            <p>Your cozy corner for freshly brewed coffee, delicious treats, and meaningful moments. Sit back, sip slowly, and feel at home with us.</p>
            <button class="btn" onclick="openModal()">Get Started</button>
        </div>
        <div class="home-img">
            <div class="img-box">
                <img src="../asset/coffee.png" alt="Coffee Cup Splash">
            </div>
        </div>
    </section>

  
    <div id="loginModal" class="modal" <?php if($error != "") echo 'style="display:flex;"'; ?>>
        <div class="modal-content">
            <i class="fa-solid fa-arrow-left back-btn" id="backBtn" style="display:none;" onclick="showRoles()"></i>
            <h2 id="modalTitle" style="font-size: 2.2rem; color: var(--main-color); margin-bottom: 5px;">☕ Select Role</h2>
            <p id="modalSub" style="color: #a8a29e; font-size: 1.2rem;">Sino ang mag-lologin sa system?</p>

            <?php if($error != ""): ?>
                <div class="error-msg"><?= $error ?></div>
            <?php endif; ?>

            <div id="roleSelection" class="role-grid">
                <button type="button" class="role-btn" onclick="selectRole('superadmin', 'Super Admin')">
                    <i class="fa-solid fa-user-shield"></i> Super Admin
                </button>
                <button type="button" class="role-btn" onclick="selectRole('admin', 'Administrator')">
                    <i class="fa-solid fa-user-tie"></i> Admin
                </button>
                <button type="button" class="role-btn" onclick="selectRole('user', 'Staff Member')">
                    <i class="fa-solid fa-user-group"></i> Staff
                </button>
            </div>

            <form id="loginForm" method="POST">
                <div id="loginInputs">
                    <input type="text" name="username" placeholder="Username" value="<?php if(isset($_COOKIE['user_login'])) echo $_COOKIE['user_login']; ?>" required autocomplete="off">
                    <input type="password" name="password" placeholder="Password" required>
                    
                    <div class="remember-me-container">
                        <label class="remember-me-label">
                            <input type="checkbox" name="remember" id="remember" <?php if(isset($_COOKIE['user_login'])) echo "checked"; ?>>
                            <span>Remember me</span>
                        </label>
                        <a href="#" class="forgot-link">Forgot?</a>
                    </div>

                    <input type="hidden" name="role" id="roleField">
                    <button type="submit" class="btn" style="width: 100%;">Login Now</button>
                </div>
            </form>

            <span class="close-modal" onclick="closeModal()">Cancel</span>
        </div>
    </div>

    <script>
        const loginModal = document.getElementById('loginModal');
        const roleSelection = document.getElementById('roleSelection');
        const loginInputs = document.getElementById('loginInputs');
        const modalTitle = document.getElementById('modalTitle');
        const modalSub = document.getElementById('modalSub');
        const backBtn = document.getElementById('backBtn');
        const roleField = document.getElementById('roleField');

        function openModal() { loginModal.style.display = 'flex'; showRoles(); }
        function closeModal() { loginModal.style.display = 'none'; }

        function selectRole(role, roleName) {
            roleField.value = role;
            roleSelection.style.display = 'none';
            loginInputs.style.display = 'block';
            backBtn.style.display = 'block';
            modalTitle.innerText = "Login as " + roleName;
            modalSub.innerText = "Enter your credentials to continue.";
        }

        function showRoles() {
            roleSelection.style.display = 'grid';
            loginInputs.style.display = 'none';
            backBtn.style.display = 'none';
            modalTitle.innerText = "Welcome Back";
            modalSub.innerText = "Select your role to continue.";
        }

        const text = document.querySelector('.typewriter');
        const words = ["Café N Chill"];
        let wordIndex = 0; let charIndex = 0; let isDeleting = false;

        function type() {
            const currentWord = words[wordIndex];
            text.textContent = isDeleting ? currentWord.substring(0, charIndex - 1) : currentWord.substring(0, charIndex + 1);
            charIndex = isDeleting ? charIndex - 1 : charIndex + 1;
            let typeSpeed = isDeleting ? 100 : 150;
            if (!isDeleting && charIndex === currentWord.length) { isDeleting = true; typeSpeed = 2000; }
            else if (isDeleting && charIndex === 0) { isDeleting = false; wordIndex = (wordIndex + 1) % words.length; typeSpeed = 500; }
            setTimeout(type, typeSpeed);
        }
        document.addEventListener('DOMContentLoaded', type);
    </script>
</body>
</html>
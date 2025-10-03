<?php
session_start();
require_once "db_connect.php";

$login_error = "";
$register_error = "";
$register_success = "";
$show_register = false; // toggles which form to show

// ---------- LOGIN ----------
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if ($email === "" || $password === "") {
        $login_error = "Please enter email and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                header("Location: index.php");
                exit;
            } else {
                $login_error = "Invalid password.";
            }
        } else {
            $login_error = "User not found.";
        }
    }
}

// ---------- REGISTER ----------
if (isset($_POST['register'])) {
    $show_register = true; // stay on register form
    $name = trim($_POST['reg_name']);
    $email = trim($_POST['reg_email']);
    $password = trim($_POST['reg_password']);
    $confirm_password = trim($_POST['reg_confirm_password']);
    $role = trim($_POST['reg_role']);

    if ($name=="" || $email=="" || $password=="" || $confirm_password=="" || $role=="") {
        $register_error = "All fields are required.";
    } elseif ($role === "admin") {
        $register_error = "❌ You cannot register as admin.";
    } elseif ($password !== $confirm_password) {
        $register_error = "Passwords do not match.";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $register_error = "Email already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);
            if ($stmt->execute()) {
                $register_success = "Registration successful! You can now login.";
                $show_register = false; // switch to login after success
            } else {
                $register_error = "Registration failed. Try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background: #f8f9fa; display:flex; justify-content:center; align-items:center; height:100vh; }
.card-container { background:#fff; padding:2rem; border-radius:.75rem; box-shadow:0 .5rem 1rem rgba(0,0,0,.1); width:100%; max-width:400px; text-align:center; }
.logo { width:100px; margin-bottom:1rem; }
.eye-toggle { cursor:pointer; color:#6c757d; }
.eye-toggle:hover { color:#495057; }
.form-toggle { cursor:pointer; color:#0d6efd; }
</style>
</head>
<body>

<div class="card-container">
    <img src="logo.png" class="logo" alt="Logo">

    <!-- LOGIN FORM -->
    <div id="login-form" style="display: <?= $show_register ? 'none' : 'block' ?>;">
        <h3 class="mb-4">OSAS Disaster & Preparedness</h3>
        <?php if ($login_error): ?><div class="alert alert-danger"><?= $login_error ?></div><?php endif; ?>
        <?php if ($register_success): ?><div class="alert alert-success"><?= $register_success ?></div><?php endif; ?>
        <form method="POST">
            <div class="mb-3 text-start">
                <label>Email</label>
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>
            <div class="mb-3 text-start">
                <label>Password</label>
                <div class="d-flex align-items-center border rounded">
                    <input type="password" name="password" id="login-password" class="form-control border-0 ps-3" placeholder="Enter password" required>
                    <span class="px-3 eye-toggle" id="login-eye"><i class="bi bi-eye-fill"></i></span>
                </div>
            </div>
            <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
        </form>
        <div class="mt-3">
            <span class="form-toggle" onclick="toggleForms()">Don't have an account? Register</span>
        </div>
    </div>

    <!-- REGISTER FORM -->
    <div id="register-form" style="display: <?= $show_register ? 'block' : 'none' ?>;">
        <h3 class="mb-4">Register</h3>
        <?php if ($register_error): ?><div class="alert alert-danger"><?= $register_error ?></div><?php endif; ?>
        <form method="POST">
            <div class="mb-3 text-start">
                <label>Full Name</label>
                <input type="text" name="reg_name" class="form-control" required>
            </div>
            <div class="mb-3 text-start">
                <label>Email</label>
                <input type="email" name="reg_email" class="form-control" required>
            </div>
            <div class="mb-3 text-start">
                <label>Password</label>
                <div class="d-flex align-items-center border rounded">
                    <input type="password" name="reg_password" id="reg-password" class="form-control border-0 ps-3" required>
                    <span class="px-3 eye-toggle" id="reg-eye"><i class="bi bi-eye-fill"></i></span>
                </div>
            </div>
            <div class="mb-3 text-start">
                <label>Confirm Password</label>
                <div class="d-flex align-items-center border rounded">
                    <input type="password" name="reg_confirm_password" id="reg-confirm-password" class="form-control border-0 ps-3" required>
                    <span class="px-3 eye-toggle" id="reg-eye-confirm"><i class="bi bi-eye-fill"></i></span>
                </div>
            </div>
            <div class="mb-3 text-start">
                <label>Role</label>
                <select name="reg_role" class="form-select" required>
                    <option value="" disabled selected>Select role</option>
                    <option value="teacher">Teacher</option>
                    <option value="student">Student</option>
                </select>
            </div>
            <button type="submit" name="register" class="btn btn-success w-100">Register</button>
        </form>
        <div class="mt-3">
            <span class="form-toggle" onclick="toggleForms()">Already have an account? Login</span>
        </div>
    </div>
</div>

<script>
// Password toggles
function toggleEye(inputId, eyeId){
    document.getElementById(eyeId).addEventListener('click', function(){
        const input = document.getElementById(inputId);
        const icon = this.querySelector('i');
        if(input.type === 'password'){
            input.type='text'; icon.classList.replace('bi-eye-fill','bi-eye-slash-fill');
        } else { input.type='password'; icon.classList.replace('bi-eye-slash-fill','bi-eye-fill'); }
    });
}

toggleEye('login-password','login-eye');
toggleEye('reg-password','reg-eye');
toggleEye('reg-confirm-password','reg-eye-confirm');

function toggleForms(){
    const login = document.getElementById('login-form');
    const reg = document.getElementById('register-form');
    if(login.style.display === 'none'){ login.style.display='block'; reg.style.display='none'; }
    else { login.style.display='none'; reg.style.display='block'; }
}
</script>

</body>
</html>

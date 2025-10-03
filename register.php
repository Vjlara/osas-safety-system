<?php
session_start();
require_once "db_connect.php";

$login_error = "";
$register_error = "";
$register_success = "";
$show_register = false; // determines which form to display

// LOGIN
if (isset($_POST['login'])) {
    $show_register = false; // show login form
    $email = trim($_POST['email']);
    $password = md5(trim($_POST['password']));

    if ($email === "" || $password === "") {
        $login_error = "Please enter email and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if ($password === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                header("Location: dashboard.php");
                exit;
            } else {
                $login_error = "Invalid password.";
            }
        } else {
            $login_error = "User not found.";
        }
    }
}

// REGISTER
if (isset($_POST['register'])) {
    $show_register = true; // stay on register if error
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    if ($role === "admin") {
        $register_error = "❌ You cannot register as admin.";
    } elseif ($password !== $confirm_password) {
        $register_error = "⚠ Password and Confirm Password do not match.";
    } else {
        $hashed_password = md5($password);

        // Check if email exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $register_error = "⚠ Email already exists. Please use another.";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

            if ($stmt->execute()) {
                $register_success = "✅ Registration successful! You can now login.";
                $show_register = false; // switch to login after success
            } else {
                $register_error = "❌ Something went wrong. Try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login & Register - OSAS Disaster & Preparedness</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            display:flex;
            justify-content:center;
            align-items:center;
            height:100vh;
            background:#f8f9fa;
        }
        .card-container {
            width:420px;
            padding:30px;
            background:white;
            border-radius:10px;
            box-shadow:0px 2px 10px rgba(0,0,0,0.1);
            text-align:center;
        }
        .logo {
            display:block;
            margin:0 auto 20px;
            width:80px;
        }
        .eye-toggle {
            cursor:pointer;
            color:#6c757d;
        }
        .eye-toggle:hover { color:#495057; }
        .form-toggle {
            cursor:pointer;
            color:#0d6efd;
        }
    </style>
</head>
<body>

<div class="card-container">
    <img src="logo.png" alt="Logo" class="logo">

    <!-- LOGIN FORM -->
    <div id="login-form" style="display: <?= $show_register ? 'none' : 'block' ?>;">
        <h3 class="mb-4">Login</h3>
        <?php if ($login_error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($login_error) ?></div>
        <?php endif; ?>
        <?php if ($register_success): ?>
            <div class="alert alert-success"><?= $register_success ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="mb-3 text-start">
                <label>Email</label>
                <input type="email" name="email" id="login-email" class="form-control" placeholder="Enter your email" required>
            </div>

            <div class="mb-3 text-start">
                <label>Password</label>
                <div class="d-flex align-items-center border rounded">
                    <input type="password" name="password" id="login-password" class="form-control border-0 ps-3" placeholder="Enter your password" required>
                    <span class="px-3 eye-toggle" id="login-eye"><i class="bi bi-eye-fill"></i></span>
                </div>
            </div>

            <button type="submit" name="login" class="btn btn-primary w-100 mt-2">Login</button>
        </form>

        <div class="mt-3">
            <span class="form-toggle" onclick="toggleForms()">Don't have an account? Register</span>
        </div>
    </div>

    <!-- REGISTER FORM -->
    <div id="register-form" style="display: <?= $show_register ? 'block' : 'none' ?>;">
        <h3 class="mb-4">Register</h3>
        <?php if ($register_error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($register_error) ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="mb-3 text-start">
                <label>Full Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3 text-start">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3 text-start">
                <label>Password</label>
                <div class="d-flex align-items-center border rounded">
                    <input type="password" name="password" id="register-password" class="form-control border-0 ps-3" placeholder="Enter password" required>
                    <span class="px-3 eye-toggle" id="register-eye"><i class="bi bi-eye-fill"></i></span>
                </div>
            </div>
            <div class="mb-3 text-start">
                <label>Confirm Password</label>
                <div class="d-flex align-items-center border rounded">
                    <input type="password" name="confirm_password" id="register-confirm-password" class="form-control border-0 ps-3" placeholder="Confirm password" required>
                    <span class="px-3 eye-toggle" id="register-eye-confirm"><i class="bi bi-eye-fill"></i></span>
                </div>
            </div>
            <div class="mb-3 text-start">
                <label>Register as</label>
                <select name="role" class="form-control" required>
                    <option value="teacher">Teacher</option>
                    <option value="student">Student</option>
                </select>
            </div>

            <button type="submit" name="register" class="btn btn-success w-100 mt-2">Register</button>
        </form>

        <div class="mt-3">
            <span class="form-toggle" onclick="toggleForms()">Already have an account? Login</span>
        </div>
    </div>
</div>

<script>
// Login password toggle
document.getElementById('login-eye').addEventListener('click', function() {
    const password = document.getElementById('login-password');
    const icon = this.querySelector('i');
    password.type === 'password' ? (password.type='text', icon.classList.replace('bi-eye-fill','bi-eye-slash-fill')) : (password.type='password', icon.classList.replace('bi-eye-slash-fill','bi-eye-fill'));
});

// Register password toggle
document.getElementById('register-eye').addEventListener('click', function() {
    const password = document.getElementById('register-password');
    const icon = this.querySelector('i');
    password.type === 'password' ? (password.type='text', icon.classList.replace('bi-eye-fill','bi-eye-slash-fill')) : (password.type='password', icon.classList.replace('bi-eye-slash-fill','bi-eye-fill'));
});

// Register confirm password toggle
document.getElementById('register-eye-confirm').addEventListener('click', function() {
    const password = document.getElementById('register-confirm-password');
    const icon = this.querySelector('i');
    password.type === 'password' ? (password.type='text', icon.classList.replace('bi-eye-fill','bi-eye-slash-fill')) : (password.type='password', icon.classList.replace('bi-eye-slash-fill','bi-eye-fill'));
});

// Toggle forms manually
function toggleForms() {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    if(loginForm.style.display === "none") {
        loginForm.style.display = "block";
        registerForm.style.display = "none";
    } else {
        loginForm.style.display = "none";
        registerForm.style.display = "block";
    }
}
</script>

</body>
</html>

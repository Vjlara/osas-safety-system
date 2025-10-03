<?php
session_start();
require_once "db_connect.php";
require_once "vendor/GoogleAuthenticator.php";

if (!isset($_SESSION['pending_user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['pending_user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code']);

    // fetch user
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    // If Google TOTP secret exists, verify using TOTP
    if (!empty($user['twofa_secret'])) {
        if (GoogleAuthenticator::verifyCode($user['twofa_secret'], $code, 1)) {
            // success
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            unset($_SESSION['pending_user_id']);
            header("Location: index.php"); exit();
        } else {
            $error = "Invalid 2FA code.";
        }
    } else {
        // fallback: verify OTP stored in otp_codes table
        $stmt = $conn->prepare("SELECT * FROM otp_codes WHERE user_id=? AND code=? AND used=0 AND expires_at > NOW() ORDER BY id DESC LIMIT 1");
        $stmt->bind_param("is", $user_id, $code);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
            $row = $res->fetch_assoc();
            // mark used
            $conn->query("UPDATE otp_codes SET used=1 WHERE id=" . (int)$row['id']);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            unset($_SESSION['pending_user_id']);
            header("Location: index.php"); exit();
        } else {
            $error = "Invalid or expired OTP code.";
        }
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Verify 2FA</title></head>
<body>
<div style="max-width:420px;margin:40px auto">
  <?php if(isset($error)):?><div style="color:red"><?=$error?></div><?php endif; ?>
  <form method="post">
    <label>Enter 6-digit code</label>
    <input name="code" required pattern="\d{6}" class="form-control" />
    <button>Verify</button>
  </form>
</div>
</body>
</html>

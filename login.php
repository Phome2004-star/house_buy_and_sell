<?php
// Output buffering ကို စဖွင့်မယ် (Redirect error မတက်အောင်)
ob_start();
require_once 'db.php';
ensure_session(); 

$conn = db();
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars(trim($_POST['email']));
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        
        // Role ကို စာလုံးအသေးပြောင်းပြီး space တွေ ဖြတ်လိုက်မယ်
        $user_role = strtolower(trim($user['role']));

        // Session ထဲမှာ အချက်အလက်တွေ သိမ်းမယ်
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user_role 
        ];
        
        // Role အလိုက် Redirect လုပ်မယ့် logic
        if ($user_role === 'admin') {
            header("Location: admin_dashboard.php");
            exit; // header ပြီးရင် exit ပါရမယ်
        } else {
            header("Location: index.php");
            exit;
        }
    } else {
        $error = "အီးမေးလ် သို့မဟုတ် စကားဝှက် မှားယွင်းနေပါသည်။";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Real Estate</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .login-box { width: 350px; background: white; padding: 35px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #273c75; margin-bottom: 25px; }
        input { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-size: 14px; }
        .btn-login { width: 100%; padding: 12px; background: #273c75; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 16px; transition: 0.3s; }
        .btn-login:hover { background: #192a56; }
        .error-msg { background: #ffebee; color: #c62828; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 13px; text-align: center; }
        .footer-link { text-align: center; margin-top: 20px; font-size: 14px; color: #666; }
        .footer-link a { color: #273c75; font-weight: bold; text-decoration: none; }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Login ဝင်ပါ</h2>
    
    <?php if($error): ?>
        <div class="error-msg"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" class="btn-login">Login</button>
    </form>
    
    <div class="footer-link">
        အကောင့်မရှိသေးဘူးလား? <a href="register.php">အသစ်ဖွင့်ရန်</a>
    </div>
</div>

</body>
</html>
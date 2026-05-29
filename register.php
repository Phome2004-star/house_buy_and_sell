<?php
require_once 'db.php';
$conn = db();
$error = ""; $success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    
    $nrc = ($role === 'seller') ? $_POST['nrc'] : "";

    try {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, nrc) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $email, $password, $role, $nrc]);
        $success = "Registration successful! <a href='login.php'>Login here</a>";
    } catch (PDOException $e) {


        $error = ($e->getCode() == 23000) ? "ဒီ Email က အကောင့်ဖွင့်ပြီးသား ဖြစ်နေပါတယ်။" : "Error: " . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Real Estate</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .reg-box { width: 380px; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #273c75; margin-bottom: 20px; }
        input, select { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-size: 14px; }
        .btn-reg { width: 100%; padding: 12px; background: #273c75; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 16px; }
        .btn-reg:hover { background: #192a56; }
        .msg { padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 14px; text-align: center; }
        .error { background: #ffebee; color: #c62828; }
        .success { background: #e8f5e9; color: #2e7d32; }
        .login-link { text-align: center; margin-top: 15px; font-size: 14px; color: #666; }
        #nrcField { display: none; } 
    </style>
</head>
<body>

<div class="reg-box">
    <h2>Register</h2>
    
    <?php if($error): ?> <div class="msg error"><?php echo $error; ?></div> <?php endif; ?>
    <?php if($success): ?> <div class="msg success"><?php echo $success; ?></div> <?php endif; ?>
    
    <form method="POST">
        <input type="text" name="username" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        
        <label style="font-size: 13px; color: #666; margin-left: 5px;">အသုံးပြုမည့် ပုံစံကို ရွေးချယ်ပါ</label>
        <select name="role" id="roleSelect" onchange="toggleFields()" required>
            <option value="buyer">User (Buyer)</option>
            <option value="seller">Seller</option>
            <option value="admin">Administrator</option>
        </select>

        <div id="nrcField">
            <input type="text" name="nrc" placeholder="NRC Number (ဥပမာ- ၁၂/မရက...)" id="nrcInput">
        </div>

        <button type="submit" class="btn-reg">အကောင့်ဖွင့်မည်</button>
    </form>
    
    <div class="login-link">
        အကောင့်ရှိပြီးသားလား? <a href="login.php" style="color: #273c75; font-weight: bold;">Login ဝင်ပါ</a>
    </div>
</div>

<script>
    function toggleFields() {
        const role = document.getElementById('roleSelect').value;
        const nrcField = document.getElementById('nrcField');
        const nrcInput = document.getElementById('nrcInput');
        
        // Seller ဖြစ်မှသာ NRC field ကို ပြမယ်
        if (role === 'seller') {
            nrcField.style.display = 'block';
            nrcInput.setAttribute('required', 'required');
        } else {
            nrcField.style.display = 'none';
            nrcInput.removeAttribute('required');
        }
    }
</script>

</body>
</html>
<?php
require_once 'db.php';
ensure_session();
require_seller(); // Seller မဟုတ်ရင် index.php ကို ပြန်ပို့မယ့် function (db.php ထဲမှာ ရှိရပါမယ်)

$error = "";
$success = "";
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = db();
    
    // Form ကလာတဲ့ အချက်အလက်များ
    $title = $_POST['title'] ?? '';
    $type = $_POST['type'] ?? '';
    $address = $_POST['address'] ?? '';
    $area = $_POST['area'] ?? 0;
    $bedrooms = $_POST['bedrooms'] ?? 0;
    $bathrooms = $_POST['bathrooms'] ?? 0;
    $price = $_POST['price'] ?? 0;
    $description = $_POST['description'] ?? '';
    $seller_name = $user['username']; // Login ဝင်ထားတဲ့ နာမည်ကိုပဲ တိုက်ရိုက်ယူမယ်

    // Image Upload Handling
    $image_url = "";
    if (isset($_FILES['images']) && $_FILES['images']['error'][0] === 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $file_ext = pathinfo($_FILES["images"]["name"][0], PATHINFO_EXTENSION);
        $file_name = time() . '_' . uniqid() . '.' . $file_ext;
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["images"]["tmp_name"][0], $target_file)) {
            $image_url = $target_file;
        }
    }

    try {
        $stmt = $conn->prepare("INSERT INTO properties (title, type, address, area, bedrooms, bathrooms, price, description, seller_name, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $type, $address, $area, $bedrooms, $bathrooms, $price, $description, $seller_name, $image_url]);
        
        $success = "အိမ်ခြံမြေအချက်အလက်များ အောင်မြင်စွာ တင်ပြီးပါပြီ။ <a href='index.php' style='color:#166534; font-weight:bold;'>Home သို့သွားရန်</a>";
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Property - CL Rent & Buy</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; margin: 0; }
        .nav {
            background: #273c75; padding: 12px 5%; display: flex; 
            justify-content: space-between; align-items: center; color: white;
        }
        .nav a { color: white; text-decoration: none; font-size: 14px; margin-left: 15px; }
        
        .container { max-width: 600px; margin: 40px auto; padding: 20px; }
        .form-box {
            background: #fff; padding: 30px; border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        h2 { color: #273c75; margin-top: 0; text-align: center; }
        
        input, select, textarea {
            width: 100%; padding: 12px; margin: 10px 0; border-radius: 8px;
            border: 1px solid #ddd; box-sizing: border-box; font-size: 14px;
        }
        
        .upload-area {
            border: 2px dashed #273c75; padding: 20px; text-align: center;
            border-radius: 10px; cursor: pointer; color: #273c75; margin: 10px 0;
        }
        #imgPreview { width: 100%; max-height: 200px; object-fit: cover; border-radius: 8px; display: none; margin-top: 10px; }

        button {
            width: 100%; padding: 14px; background: #2ecc71; color: white;
            border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 16px; margin-top: 10px;
        }
        button:hover { background: #27ae60; }
        
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-size: 14px; }
        .alert-danger { background: #fee2e2; color: #b91c1c; }
        .alert-success { background: #dcfce7; color: #166534; }
    </style>
</head>
<body>

<div class="nav">
    <div style="font-weight: bold; font-size: 20px;">CL Rent & Buy</div>
    <div class="menu">
        <a href="index.php">Home</a>
        <span style="margin-left:15px; font-size: 13px; color: #f1c40f;">Seller: <?php echo htmlspecialchars($user['username']); ?></span>
        <a href="logout.php" style="color:#ff7675;">Logout</a>
    </div>
</div>

<div class="container">
    <div class="form-box">
        <h2>🏡 Add New Property</h2>

        <?php if ($error): ?> <div class="alert alert-danger"><?php echo $error; ?></div> <?php endif; ?>
        <?php if ($success): ?> <div class="alert alert-success"><?php echo $success; ?></div> <?php endif; ?>

        <form action="" method="post" enctype="multipart/form-data">
            <input type="text" name="title" placeholder="Property Title (ဥပမာ- ရန်ကင်းမြို့နယ်ရှိ Condo ရောင်းရန်ရှိသည်)" required>
            
            <select name="type" required>
                <option value="">Select Property Type</option>
                <option value="House">House (လုံးချင်းအိမ်)</option>
                <option value="Apartment">Apartment (တိုက်ခန်း)</option>
                <option value="Condo">Condo (ကွန်ဒို)</option>
                <option value="Land">Land (မြေကွက်)</option>
            </select>
            
            <input type="text" name="address" placeholder="တည်နေရာ အပြည့်အစုံ" required>
            
            <div style="display: flex; gap: 10px;">
                <input type="number" name="area" placeholder="Sq. Feet" required>
                <input type="number" name="price" placeholder="Price (သိန်းပေါင်း)" required>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <input type="number" name="bedrooms" placeholder="အိပ်ခန်းအရေအတွက်" required>
                <input type="number" name="bathrooms" placeholder="ရေချိုးခန်းအရေအတွက်" required>
            </div>
            
            <textarea name="description" rows="4" placeholder="အိမ်ခြံမြေအကြောင်း အသေးစိတ်ဖော်ပြချက်..." required></textarea>
            
            <div class="upload-area" onclick="document.getElementById('fileInput').click()">
                <span>📷 ပုံတင်ရန် ဤနေရာကို နှိပ်ပါ</span>
                <input type="file" name="images[]" id="fileInput" style="display: none;" onchange="previewImage(event)" required>
                <img id="imgPreview">
            </div>
            
            <button type="submit">Publish Property</button>
        </form>
    </div>
</div>

<script>
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const output = document.getElementById('imgPreview');
            output.src = reader.result;
            output.style.display = 'block';
        }
        reader.readAsDataURL(event.target.files[0]);
    }
</script>

</body>
</html>
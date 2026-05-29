<?php
require_once 'db.php';
ensure_session();
$conn = db();

$user = current_user();

// အိမ်ခြံမြေစာရင်းများကို ဆွဲထုတ်ခြင်း
$stmt = $conn->query("SELECT * FROM properties ORDER BY created_at DESC");
$properties = $stmt->fetchAll();

// Admin ဟုတ်မဟုတ် စစ်ဆေးခြင်း
$is_admin = ($user && isset($user['role']) && strtolower($user['role']) === 'admin');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CL Rent & Buy | Real Estate</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; margin: 0; }
        .nav {
            display: flex; justify-content: space-between; align-items: center;
            background: #273c75; padding: 12px 5%; color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .nav .logo { font-weight: bold; font-size: 22px; text-decoration: none; color: white; }
        .nav .menu { display: flex; align-items: center; gap: 15px; }
        .nav .menu a { color: white; text-decoration: none; font-size: 14px; transition: 0.3s; }
        .nav .menu a:hover { color: #f1c40f; }
        
        /* Buttons Style */
        .btn-add { background: #2ecc71 !important; color: white !important; padding: 6px 12px; border-radius: 5px; font-weight: bold; }
        .btn-admin { background: #f1c40f !important; color: #273c75 !important; padding: 6px 12px; border-radius: 5px; font-weight: bold; }
        .btn-msg { background: #f1c40f !important; color: #273c75 !important; padding: 6px 12px; border-radius: 5px; font-weight: bold; }
        .btn-reg { background: white !important; color: #273c75 !important; padding: 6px 12px; border-radius: 5px; font-weight: bold; }

        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; }

        .card {
            background: #fff; border-radius: 12px; overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.3s ease;
            position: relative;
        }
        .card:hover { transform: translateY(-5px); }
        .card img { width: 100%; height: 200px; object-fit: cover; }
        
        .status-badge {
            position: absolute; top: 10px; right: 10px; padding: 4px 12px;
            border-radius: 5px; font-size: 11px; font-weight: bold; color: white;
            text-transform: uppercase; z-index: 10;
        }
        .status-available { background: #2ecc71; }
        .status-sold { background: #ff4757; }

        .card-body { padding: 18px; }
        .card-body h3 { margin: 0 0 10px; color: #273c75; font-size: 18px; line-height: 1.4; }
        .card-body .price { font-weight: bold; color: #e84118; margin-bottom: 10px; font-size: 17px; }
        .card-body .info { font-size: 13px; color: #777; margin-bottom: 15px; }
        
        .view-btn {
            display: block; text-align: center; padding: 10px; background: #273c75; 
            color: white; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 500;
        }
        .view-btn:hover { background: #192a56; }

        .admin-actions { display: flex; gap: 8px; margin-top: 10px; }
        .btn-edit { flex: 1; background: #f1c40f; color: #273c75; text-align: center; padding: 8px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: bold; }
        .btn-delete { flex: 1; background: #ff7675; color: white; text-align: center; padding: 8px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: bold; }
        
        .btn-status {
            display: block; width: 100%; margin-top: 8px; padding: 8px;
            border-radius: 6px; text-decoration: none; font-size: 12px;
            text-align: center; font-weight: bold; color: white;
        }
        .bg-sold { background: #57606f; }
        .bg-available { background: #3498db; }
    </style>
</head>

<body>

<div class="nav">
    <a href="index.php" class="logo">CL Rent & Buy</a>
    <div class="menu">
        <a href="propertysearch.php">Search</a>

        <?php if ($user): ?>
            <?php if ($is_admin): ?>
                <a href="admin_dashboard.php" class="btn-admin">Admin Panel</a>
                <a href="admin_inquiries.php" class="btn-msg">✉️ Messages</a>
            <?php else: ?>
                <a href="message.php?seller=Customer+Support">Support</a>
            <?php endif; ?>

            <?php if (is_seller()): ?>
                <a href="addproperty.php" class="btn-add">+ Add Property</a>
            <?php endif; ?>

            <span style="color: #f1c40f; font-size: 13px; border-left: 1px solid #555; padding-left: 10px; margin-left: 5px;">
                Hi, <b><?php echo htmlspecialchars($user['username']); ?></b> 
            </span>
            <a href="logout.php" style="color:#ff7675; margin-left: 10px;">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a class="btn-reg" href="register.php">Register</a>
        <?php endif; ?>
    </div>
</div>

<div class="container">
    <h2 style="color: #273c75; margin-bottom: 30px; border-left: 5px solid #2ecc71; padding-left: 15px;">
        Yangon Property Market
    </h2>

    <div class="grid">
        <?php if (count($properties) > 0): ?>
            <?php foreach ($properties as $row): ?>
                <div class="card">
                    <?php 
                        $status = $row['status'] ?? 'available';
                        $status_class = ($status === 'sold') ? 'status-sold' : 'status-available';
                        $status_label = ($status === 'sold') ? 'Sold Out' : 'Available';
                    ?>
                    <div class="status-badge <?php echo $status_class; ?>">
                        <?php echo $status_label; ?>
                    </div>

                    <img src="<?php echo !empty($row['image_url']) ? $row['image_url'] : 'https://via.placeholder.com/400x250'; ?>" alt="Property">
                    
                    <div class="card-body">
                        <div class="price"><?php echo number_format($row['price']); ?> MMK</div>
                        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                        <div class="info">
                            📍 <?php echo htmlspecialchars($row['address']); ?><br>
                            🏠 <?php echo htmlspecialchars($row['type']); ?> | 👤 <?php echo htmlspecialchars($row['seller_name']); ?>
                        </div>
                        
                        <a class="view-btn" href="viewproperty.php?id=<?php echo $row['id']; ?>">View Details</a>

                        <?php 
                        $is_owner = ($user && $user['username'] === $row['seller_name']);
                        if ($is_admin || $is_owner): 
                        ?>
                            <div class="admin-actions">
                                <a href="edit_property.php?id=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                                <a href="delete_property.php?id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('ဒီ post ကို ဖျက်မှာ သေချာလား?')">Delete</a>
                            </div>

                            <?php if ($status === 'available'): ?>
                                <a href="update_status.php?id=<?php echo $row['id']; ?>&set=sold" class="btn-status bg-sold">Mark as Sold</a>
                            <?php else: ?>
                                <a href="update_status.php?id=<?php echo $row['id']; ?>&set=available" class="btn-status bg-available">Mark as Available</a>
                            <?php endif; ?>

                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 50px; color: #888;">
                <p>လက်ရှိတွင် အိမ်ခြံမြေစာရင်းများ မရှိသေးပါ။</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
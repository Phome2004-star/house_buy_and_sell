<?php
require_once 'db.php';
ensure_session();

// Admin မဟုတ်ရင် ဝင်ခွင့်မပြုဘူး
$user = current_user();
if (!$user || strtolower($user['role']) !== 'admin') {
    header("Location: login.php");
    exit;
}

$conn = db();

// အခြေခံစာရင်းအင်းများ တွက်ချက်ခြင်း
$user_count = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$property_count = $conn->query("SELECT COUNT(*) FROM properties")->fetchColumn();
$available_count = $conn->query("SELECT COUNT(*) FROM properties WHERE status = 'available'")->fetchColumn();
$sold_count = $conn->query("SELECT COUNT(*) FROM properties WHERE status = 'sold'")->fetchColumn();

// User အားလုံးကို ဆွဲထုတ်ခြင်း
$all_users = $conn->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();

// Property အားလုံးကို ဆွဲထုတ်ခြင်း
$all_properties = $conn->query("SELECT * FROM properties ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CL Rent & Buy</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; margin: 0; display: flex; }
        
        /* Sidebar Style */
        .sidebar { width: 250px; background: #273c75; color: white; height: 100vh; position: fixed; padding: 20px; box-sizing: border-box; }
        .sidebar h2 { font-size: 20px; border-bottom: 1px solid #3d5aab; padding-bottom: 15px; margin-bottom: 20px; }
        .sidebar a { display: block; color: #d1d8e0; text-decoration: none; padding: 12px 15px; font-size: 15px; border-radius: 8px; transition: 0.3s; }
        .sidebar a:hover { background: rgba(255,255,255,0.1); color: #f1c40f; }
        .sidebar a.active { background: #f1c40f; color: #273c75; font-weight: bold; }

        /* Main Content */
        .main-content { margin-left: 250px; padding: 40px; width: calc(100% - 250px); box-sizing: border-box; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border-left: 5px solid #273c75; }
        .stat-card h3 { margin: 0; color: #888; font-size: 13px; text-transform: uppercase; }
        .stat-card p { font-size: 24px; font-weight: bold; color: #273c75; margin: 10px 0 0; }
        
        /* Tables */
        .card-box { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .card-box h3 { color: #273c75; margin-top: 0; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px; margin-bottom: 20px; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px; }
        th { background: #f8f9fa; color: #273c75; font-weight: 600; }
        
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .role-admin { background: #ffeaa7; color: #d6a017; }
        .role-seller { background: #dff9fb; color: #130f40; }
        .role-buyer { background: #f1f2f6; color: #57606f; }
        
        .status-sold { color: #ff7675; font-weight: bold; }
        .status-available { color: #2ecc71; font-weight: bold; }

        .btn-del { color: #ff7675; text-decoration: none; font-weight: bold; }
        .btn-del:hover { text-decoration: underline; }
        
        .prop-img { width: 40px; height: 40px; border-radius: 4px; object-fit: cover; vertical-align: middle; margin-right: 10px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="index.php">🏠 Back to Home</a>
    <a href="admin_dashboard.php" class="active">📊 Statistics</a>
    <a href="#manage-properties">🏢 Manage Properties</a>
    <a href="admin_inquiries.php">✉️ Buyer Inquiries</a>
    <a href="logout.php" style="margin-top: 50px; color: #ff7675;">🚪 Logout</a>
</div>

<div class="main-content">
    <h2>Dashboard Overview</h2>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Users</h3>
            <p><?php echo $user_count; ?></p>
        </div>
        <div class="stat-card" style="border-left-color: #f1c40f;">
            <h3>Total Properties</h3>
            <p><?php echo $property_count; ?></p>
        </div>
        <div class="stat-card" style="border-left-color: #2ecc71;">
            <h3>Available</h3>
            <p><?php echo $available_count; ?></p>
        </div>
        <div class="stat-card" style="border-left-color: #ff7675;">
            <h3>Sold Out</h3>
            <p><?php echo $sold_count; ?></p>
        </div>
    </div>

    <div class="card-box">
        <h3>User Management</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>NRC (Sellers)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_users as $u): ?>
                <tr>
                    <td>#<?php echo $u['id']; ?></td>
                    <td><b><?php echo htmlspecialchars($u['username']); ?></b><br><small><?php echo htmlspecialchars($u['email']); ?></small></td>
                    <td><span class="badge role-<?php echo strtolower($u['role']); ?>"><?php echo $u['role']; ?></span></td>
                    <td><?php echo $u['nrc'] ? htmlspecialchars($u['nrc']) : '-'; ?></td>
                    <td>
                        <?php if ($u['id'] != $user['id']): ?>
                            <a href="delete_user.php?id=<?php echo $u['id']; ?>" class="btn-del" onclick="return confirm('ဒီ user ကို ဖျက်မှာ သေချာလား?')">Remove</a>
                        <?php else: ?>
                            <small style="color:#ccc;">(You)</small>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card-box" id="manage-properties">
        <h3>Manage Properties</h3>
        <table>
            <thead>
                <tr>
                    <th>Property</th>
                    <th>Price</th>
                    <th>Seller</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($all_properties) > 0): ?>
                    <?php foreach ($all_properties as $p): ?>
                    <tr>
                        <td>
                            <img src="<?php echo $p['image_url']; ?>" class="prop-img" onerror="this.src='https://via.placeholder.com/40'">
                            <b><?php echo htmlspecialchars($p['title']); ?></b>
                        </td>
                        <td><?php echo number_format($p['price']); ?> MMK</td>
                        <td><?php echo htmlspecialchars($p['seller_name']); ?></td>
                        <td>
                            <span class="status-<?php echo $p['status']; ?>">
                                <?php echo strtoupper($p['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="delete_property.php?id=<?php echo $p['id']; ?>" class="btn-del" onclick="return confirm('ဒီ Property Post ကို ဖျက်မှာ သေჩာလား?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center;">အိမ်ခြံမြေစာရင်း မရှိသေးပါ။</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
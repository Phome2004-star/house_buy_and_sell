<?php
require_once 'db.php';
ensure_session();
$user = current_user();

// Admin မဟုတ်ရင် မောင်းထုတ်မယ်
if (!$user || strtolower($user['role']) !== 'admin') {
    header("Location: index.php");
    exit;
}

$conn = db();

// Message များကို ဆွဲထုတ်ခြင်း (Admin ဆီရောက်လာသော စာများကိုသာ စုပြမည်)
// Group By သုံးထားခြင်းဖြင့် User တစ်ယောက်ချင်းစီရဲ့ နောက်ဆုံးစာကိုပဲ list မှာ အရင်ပြပေးမှာပါ
$stmt = $conn->prepare("
    SELECT m.*, u.username as sender_name, u.email as sender_email 
    FROM messages m 
    JOIN users u ON m.sender_id = u.id 
    WHERE m.receiver_id = ? OR m.receiver_name = 'Customer Support'
    ORDER BY m.created_at DESC
");
$stmt->execute([$user['id']]);
$inquiries = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Inquiries</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; margin: 0; display: flex; }
        
        /* Sidebar */
        .sidebar { width: 260px; background: #273c75; color: white; height: 100vh; position: fixed; padding: 25px 20px; box-sizing: border-box; }
        .sidebar h2 { font-size: 20px; margin-bottom: 30px; border-bottom: 1px solid #3d5a80; padding-bottom: 15px; }
        .sidebar a { display: block; color: #d1d8e0; text-decoration: none; padding: 12px 15px; border-radius: 8px; margin-bottom: 5px; transition: 0.3s; }
        .sidebar a:hover { background: rgba(255,255,255,0.1); color: white; }
        .sidebar a.active { background: #f1c40f; color: #273c75; font-weight: bold; }
        
        /* Main Content */
        .main-content { margin-left: 260px; padding: 40px; width: calc(100% - 260px); box-sizing: border-box; }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header-flex h2 { margin: 0; color: #273c75; }

        /* Table Style */
        .card-table { background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden; }
        .msg-table { width: 100%; border-collapse: collapse; }
        .msg-table th { background: #f8f9fa; padding: 18px 20px; text-align: left; font-size: 14px; color: #777; border-bottom: 2px solid #edf2f7; }
        .msg-table td { padding: 18px 20px; border-bottom: 1px solid #edf2f7; font-size: 14px; vertical-align: middle; }
        
        .user-info b { color: #2d3436; font-size: 15px; }
        .user-info small { color: #636e72; }
        
        .msg-text { color: #2d3436; max-width: 400px; line-height: 1.5; }
        .date-text { color: #b2bec3; font-size: 12px; }

        .reply-btn { 
            display: inline-block; background: #273c75; color: white; padding: 8px 18px; 
            border-radius: 20px; text-decoration: none; font-size: 13px; font-weight: 600; transition: 0.3s;
        }
        .reply-btn:hover { background: #192a56; transform: scale(1.05); }
        
        .empty-state { text-align: center; padding: 60px; color: #b2bec3; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin_dashboard.php">📊 Statistics</a>
    <a href="index.php">🏠 Back to Home</a>
    <a href="admin_inquiries.php" class="active">✉️ Buyer Inquiries</a>
    <a href="logout.php" style="margin-top: 50px; color: #ff7675;">🚪 Logout</a>
</div>

<div class="main-content">
    <div class="header-flex">
        <div>
            <h2>Buyer Inquiries</h2>
            <p style="color: #636e72; margin: 5px 0 0;">Customer Support ဆီသို့ ပေးပို့ထားသော စာများကို စီမံခန့်ခွဲရန်</p>
        </div>
    </div>

    <div class="card-table">
        <table class="msg-table">
            <thead>
                <tr>
                    <th>From User</th>
                    <th>Latest Message</th>
                    <th>Date & Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($inquiries) > 0): ?>
                    <?php foreach ($inquiries as $msg): ?>
                    <tr>
                        <td class="user-info">
                            <b><?php echo htmlspecialchars($msg['sender_name']); ?></b><br>
                            <small><?php echo htmlspecialchars($msg['sender_email']); ?></small>
                        </td>
                        <td class="msg-text">
                            <?php 
                                $preview = htmlspecialchars($msg['message']);
                                echo (strlen($preview) > 80) ? substr($preview, 0, 80) . '...' : $preview; 
                            ?>
                        </td>
                        <td class="date-text">
                            <?php echo date('d M Y', strtotime($msg['created_at'])); ?><br>
                            <?php echo date('h:i A', strtotime($msg['created_at'])); ?>
                        </td>
                        <td>
                            <a href="message.php?seller=<?php echo urlencode($msg['sender_name']); ?>" class="reply-btn">Reply</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="empty-state">
                            <div style="font-size: 40px; margin-bottom: 10px;">📩</div>
                            <p>မေးမြန်းထားသော စာသစ်များ မရှိသေးပါ။</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
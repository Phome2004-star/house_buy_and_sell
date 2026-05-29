<?php
require_once 'db.php';
ensure_session();
require_auth();

$conn = db();
$user = current_user();
$is_admin = ($user && strtolower($user['role']) === 'admin');

// ၁။ URL ကနေ Target (စာလက်ခံမယ့်သူ) အချက်အလက်ယူမယ်
$target_name = $_GET['seller'] ?? 'Customer Support';

// ၂။ လက်ခံမယ့်သူရဲ့ ID ကို ရှာမယ် (Admin ဆိုရင် Role နဲ့ရှာမယ်၊ Seller ဆိုရင် Username နဲ့ရှာမယ်)
if ($target_name === 'Customer Support') {
    $stmt = $conn->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
    $stmt->execute();
} else {
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$target_name]);
}
$target = $stmt->fetch();
$target_id = $target['id'] ?? 0;

// ၃။ စာပို့လိုက်ရင် Database ထဲသိမ်းမယ့် Logic (AJAX သုံးရင် ပိုကောင်းပေမဲ့ ရိုးရိုး POST နဲ့အရင်လုပ်ပေးမယ်)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $msg_text = htmlspecialchars(trim($_POST['message']));
    if (!empty($msg_text)) {
        $ins = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, receiver_name, message) VALUES (?, ?, ?, ?)");
        $ins->execute([$user['id'], $target_id, $target_name, $msg_text]);
        
        // စာပို့ပြီးရင် chat အောက်ဆုံးရောက်အောင် refresh ပြန်လုပ်မယ်
        header("Location: message.php?seller=" . urlencode($target_name));
        exit;
    }
}

// ၄။ Chat History ကို Database ကနေ ပြန်ဆွဲထုတ်မယ်
$stmt = $conn->prepare("
    SELECT * FROM messages 
    WHERE (sender_id = ? AND receiver_id = ?) 
    OR (sender_id = ? AND receiver_id = ?) 
    ORDER BY created_at ASC
");
$stmt->execute([$user['id'], $target_id, $target_id, $user['id']]);
$chats = $stmt->fetchAll();

$avatar_letter = strtoupper(substr($target_name, 0, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with <?php echo htmlspecialchars($target_name); ?></title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; margin: 0; }
        .header { background: white; padding: 10px 15px; display: flex; align-items: center; border-bottom: 1px solid #ddd; position: sticky; top: 0; z-index: 100; }
        .avatar { width: 40px; height: 40px; background: #273c75; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 10px; font-weight: bold; }
        .chat-area { max-width: 600px; margin: auto; height: calc(100vh - 130px); padding: 15px; overflow-y: auto; display: flex; flex-direction: column; box-sizing: border-box; }
        .bubble { max-width: 75%; padding: 10px 15px; border-radius: 18px; margin-bottom: 8px; font-size: 14px; line-height: 1.4; position: relative; }
        .received { background: white; align-self: flex-start; border-bottom-left-radius: 2px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
        .sent { background: #273c75; color: white; align-self: flex-end; border-bottom-right-radius: 2px; }
        .time { font-size: 10px; opacity: 0.7; margin-top: 5px; display: block; text-align: right; }
        
        .footer { background: white; padding: 10px; position: fixed; bottom: 0; width: 100%; max-width: 600px; left: 50%; transform: translateX(-50%); display: flex; gap: 10px; box-shadow: 0 -2px 10px rgba(0,0,0,0.05); }
        .footer form { display: flex; width: 100%; gap: 10px; }
        .footer input { flex: 1; border: none; background: #f0f2f5; padding: 12px 15px; border-radius: 25px; outline: none; }
        .footer button { border: none; background: none; color: #273c75; font-weight: bold; cursor: pointer; padding: 0 10px; }
    </style>
</head>
<body>

<div class="header">
    <a href="index.php" style="text-decoration: none; margin-right: 15px; color: #333; font-size: 20px;">←</a>
    <div class="avatar"><?php echo $avatar_letter; ?></div>
    <div>
        <h3 style="margin: 0; font-size: 16px;"><?php echo htmlspecialchars($target_name); ?></h3>
        <small style="color: green;">● Online</small>
    </div>
</div>

<div class="chat-area" id="chatWindow">
    <div class="bubble received">
        မင်္ဂလာပါ <b><?php echo htmlspecialchars($user['username']); ?></b>။ <br>
        CL Rent & Buy Support ကနေ ကြိုဆိုပါတယ်။ ဘာများ ကူညီပေးရမလဲခင်ဗျာ။
    </div>

    <?php foreach ($chats as $chat): ?>
        <div class="bubble <?php echo ($chat['sender_id'] == $user['id']) ? 'sent' : 'received'; ?>">
            <?php echo htmlspecialchars($chat['message']); ?>
            <span class="time"><?php echo date('h:i A', strtotime($chat['created_at'])); ?></span>
        </div>
    <?php endforeach; ?>
</div>

<div class="footer">
    <form method="POST" action="">
        <input type="text" name="message" id="msgInput" placeholder="မေးမြန်းလိုသည်များကို ရိုက်ထည့်ပါ..." required autocomplete="off">
        <button type="submit">ပို့မည်</button>
    </form>
</div>

<script>
    // Chat window ကို အမြဲတမ်း အောက်ဆုံး (နောက်ဆုံးစာ) ဆီ ပို့ထားမယ်
    const chatWindow = document.getElementById('chatWindow');
    chatWindow.scrollTop = chatWindow.scrollHeight;
</script>

</body>
</html>
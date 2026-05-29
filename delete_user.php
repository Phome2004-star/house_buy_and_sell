<?php
require_once 'db.php';
ensure_session();

$user = current_user();
// Admin ဟုတ်မဟုတ် အရင်စစ်မယ်
if (!$user || $user['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $conn = db();
    
    // ကိုယ့်အကောင့်ကိုယ် မဖျက်မိအောင် ထပ်စစ်မယ်
    if ($id != $user['id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
    }
}

header("Location: admin_dashboard.php");
exit;
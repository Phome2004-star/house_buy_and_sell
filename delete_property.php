<?php
require_once 'db.php';
ensure_session();
$user = current_user();

// Admin ဖြစ်မှ ဖျက်ခွင့်ပေးမယ်
if (!$user || $user['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $conn = db();
    $stmt = $conn->prepare("DELETE FROM properties WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: index.php");
exit;
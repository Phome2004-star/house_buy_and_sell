<?php
require_once 'db.php';
ensure_session();
$user = current_user();

if (!$user || ($user['role'] !== 'admin' && $user['role'] !== 'seller')) {
    header("Location: index.php");
    exit;
}

if (isset($_GET['id']) && isset($_GET['set'])) {
    $id = $_GET['id'];
    $status = $_GET['set']; // 'available' သို့မဟုတ် 'sold'
    
    $conn = db();
    $stmt = $conn->prepare("UPDATE properties SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
}

header("Location: index.php");
exit;
<?php
function db() {
    static $pdo = null;
    if ($pdo === null) {
        $host = '127.0.0.1';
        $user = 'root';
        $pass = ''; 
        $dbname = 'cl_rent_buy';

        try {
            $temp_pdo = new PDO("mysql:host=$host", $user, $pass);
            $temp_pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            create_tables($pdo);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    return $pdo;
}

function create_tables($pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('buyer', 'seller') DEFAULT 'buyer',
        nrc VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS properties (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        type VARCHAR(50),
        address TEXT,
        price DECIMAL(15, 2),
        area INT,
        bedrooms INT,
        bathrooms INT,
        seller_name VARCHAR(100),
        description TEXT,
        image_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
}

function ensure_session() {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
}

// User တစ်ယောက်လုံးရဲ့ အချက်အလက် (id, role, username) ကို Session ကနေ ယူဖို့
function current_user() {
    ensure_session();
    return $_SESSION['user'] ?? null;
}

// Seller ဟုတ်မဟုတ် စစ်ဆေးပေးမည့် Function အသစ်
function is_seller() {
    $user = current_user();
    return $user && isset($user['role']) && $user['role'] === 'seller';
}

function require_auth() {
    if (!current_user()) { header('Location: login.php'); exit; }
}

// Seller မဟုတ်ရင် ဝင်ခွင့်မပေးတဲ့ အကာအကွယ် Function
function require_seller() {
    if (!is_seller()) {
        header('Location: index.php');
        exit;
    }
}
?>
<?php
require_once 'db.php';
ensure_session();
$conn = db();

$id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT * FROM properties WHERE id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) { header("Location: index.php"); exit; }

$seller_phone = $p['seller_phone'] ?? '09xxxxxxxxx';
$seller_name = $p['seller_name'] ?? 'Unknown';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($p['title']); ?> - Details</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; margin: 0; color: #333; }
        .nav { display: flex; justify-content: space-between; align-items: center; background:#273c75; padding: 12px 20px; color: white; }
        .nav .logo { font-weight: bold; font-size: 24px; text-decoration: none; color: white; }
        
        .container { max-width: 900px; margin: 30px auto; padding: 20px; }
        .property-card { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        
        .image-container { width: 100%; height: 450px; background: #eee; }
        .property-img { width: 100%; height: 100%; object-fit: cover; }
        
        .content { padding: 30px; }
        .price { font-size: 30px; font-weight: bold; color: #e84118; margin-bottom: 10px; }
        
        /* Location Box & Map Button Styling */
        .location-box { 
            display: flex; 
            align-items: center; 
            justify-content: space-between; /* စာနဲ့ ခလုတ်ကို ဘယ်/ညာ ခွဲထုတ်ရန် */
            background: #fdf2f2; 
            color: #e84118; 
            padding: 12px 15px; 
            border-radius: 8px; 
            margin-bottom: 25px; 
            border-left: 5px solid #e84118; 
        }
        .btn-map {
            background: #e84118;
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            transition: 0.3s;
        }
        .btn-map:hover { background: #c0392b; }

        .specs-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 30px; }
        .spec-item { background: #f8f9fa; padding: 15px; border-radius: 10px; text-align: center; border: 1px solid #eee; }
        .spec-item span { display: block; color: #777; font-size: 12px; margin-bottom: 5px; text-transform: uppercase; }
        .spec-item strong { font-size: 15px; color: #273c75; }

        .btn-group { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 35px; }
        .btn-call { background: #2ecc71; color: white; text-align: center; padding: 16px; text-decoration: none; border-radius: 10px; font-weight: bold; transition: 0.3s; }
        .btn-chat { background: #0084ff; color: white; text-align: center; padding: 16px; text-decoration: none; border-radius: 10px; font-weight: bold; transition: 0.3s; }
        .btn-call:hover { opacity: 0.9; }
        .btn-chat:hover { opacity: 0.9; }

        .section-title { font-size: 18px; color: #273c75; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px; margin-bottom: 15px; font-weight: bold; }
        .description-text { line-height: 1.8; color: #444; background: #fafafa; padding: 20px; border-radius: 10px; border: 1px dashed #ccc; }
    </style>
</head>
<body>

<div class="nav">
    <a href="index.php" class="logo">CL Rent & Buy</a>
</div>

<div class="container">
    <div class="property-card">
        <div class="image-container">
            <img src="<?php echo !empty($p['image_url']) ? $p['image_url'] : 'https://via.placeholder.com/900x450'; ?>" class="property-img">
        </div>

        <div class="content">
            <h1 style="margin:0 0 10px 0; color:#273c75;"><?php echo htmlspecialchars($p['title']); ?></h1>
            
            <div class="price"><?php echo number_format($p['price']); ?> MMK</div>

            <div class="location-box">
                <span>📍 <strong>Location:</strong> <?php echo htmlspecialchars($p['address'] ?? 'Not Specified'); ?></span>
                
                <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($p['address'] . ' ' . $p['title']); ?>" 
                   target="_blank" 
                   class="btn-map">
                   🗺️ View on Map
                </a>
            </div>

            <div class="specs-grid">
                <div class="spec-item">
                    <span>Type</span>
                    <strong>🏠 <?php echo htmlspecialchars($p['type'] ?? '-'); ?></strong>
                </div>
                <div class="spec-item">
                    <span>Bedrooms</span>
                    <strong>🛏️ <?php echo htmlspecialchars($p['bedrooms'] ?? '0'); ?> BR</strong>
                </div>
                <div class="spec-item">
                    <span>Size</span>
                    <strong>📏 <?php echo htmlspecialchars($p['area'] ?? '-'); ?> Sqft</strong>
                </div>
                <div class="spec-item">
                    <span>Seller</span>
                    <strong>👤 <?php echo htmlspecialchars($seller_name); ?></strong>
                </div>
            </div>

            <div class="btn-group">
                <a href="tel:<?php echo htmlspecialchars($seller_phone); ?>" class="btn-call">📞 Call Now</a>
                <a href="message.php?seller=<?php echo urlencode($seller_name); ?>&phone=<?php echo urlencode($seller_phone); ?>&property=<?php echo urlencode($p['title']); ?>" class="btn-chat">💬 Message</a>
            </div>

            <div class="section-title">Property Description</div>
            <div class="description-text">
                <?php echo nl2br(htmlspecialchars($p['description'] ?? 'No description available for this property.')); ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
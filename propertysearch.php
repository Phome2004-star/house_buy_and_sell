<?php
require_once 'db.php';
ensure_session();
$conn = db();


$user = current_user();

$results = [];
$search_performed = false;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET)) {
    $search_performed = true;
    

    $type = $_GET['type'] ?? '';
    $priceMin = !empty($_GET['priceMin']) ? $_GET['priceMin'] : 0;
    $priceMax = !empty($_GET['priceMax']) ? $_GET['priceMax'] : 9999999999;
    $bedrooms = $_GET['bedrooms'] ?? '';

    
    $query = "SELECT * FROM properties WHERE price >= ? AND price <= ?";
    $params = [$priceMin, $priceMax];

    if ($type) {
        $query .= " AND type = ?";
        $params[] = $type;
    }

    if ($bedrooms !== '') {
        $query .= " AND bedrooms = ?";
        $params[] = $bedrooms;
    }

    $query .= " ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Property Search - CL Rent & Buy</title>
<link rel="stylesheet" href="style.css">
<style>
  body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f4f4; margin: 0; }
  

  .nav { display: flex; justify-content: space-between; align-items: center; background: #273c75; color: white; padding: 12px 20px; }
  .nav .logo { font-size: 24px; font-weight: bold; }
  .nav .menu { display: flex; align-items: center; }
  .nav .menu a { color: white; text-decoration: none; margin-left: 15px; font-size: 14px; }
  .nav .menu a.btn { background: white; color: #273c75; padding: 5px 12px; border-radius: 5px; }


  .container { max-width: 1000px; margin: 30px auto; padding: 20px; }
  
  
  .search-card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); margin-bottom: 40px; }
  .search-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
  .search-form input, .search-form select { padding: 12px; border-radius: 5px; border: 1px solid #ddd; outline: none; }
  .search-btn { grid-column: span 1; padding: 12px; background: #e84118; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
  .search-btn:hover { background: #c23616; }


  .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
  .card { background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 3px 10px rgba(0,0,0,0.1); transition: 0.3s; }
  .card:hover { transform: translateY(-5px); }
  .card img { width: 100%; height: 180px; object-fit: cover; }
  .card-body { padding: 15px; }
  .card-body h4 { margin: 0 0 10px; color: #273c75; }
  .price { color: #e84118; font-weight: bold; font-size: 18px; margin-bottom: 10px; }
  .view-link { display: block; text-align: center; background: #273c75; color: white; padding: 10px; text-decoration: none; border-radius: 5px; margin-top: 10px; }
</style>
</head>
<body>

<div class="nav">
  <div class="logo">CL Rent & Buy</div>
  <div class="menu">
    <a href="index.php">Home</a>
    <a href="propertysearch.php">Search</a>
    <?php if ($user): ?>
        <a href="addproperty.php">Add Property</a>
        <a href="message.php">Support</a>
        <span style="margin-left:15px; color: #f1c40f;">Hi, <?php echo htmlspecialchars($user['username']); ?></span>
        <a href="logout.php" style="color:#ff7675;">Logout</a>
    <?php else: ?>
        <a href="login.php">Login</a>
        <a class="btn" href="register.php">Register</a>
    <?php endif; ?>
  </div>
</div>

<div class="container">
  <div class="search-card">
    <h2 style="margin-top:0; color: #273c75;">Find Your Perfect Home</h2>
    <form class="search-form" method="GET" action="">
      <select name="type">
        <option value="">Property Type (All)</option>
        <option value="house" <?php if(isset($_GET['type']) && $_GET['type'] == 'house') echo 'selected'; ?>>House</option>
        <option value="apartment" <?php if(isset($_GET['type']) && $_GET['type'] == 'apartment') echo 'selected'; ?>>Apartment</option>
        <option value="condo" <?php if(isset($_GET['type']) && $_GET['type'] == 'condo') echo 'selected'; ?>>Condo</option>
      </select>
      
      <input type="number" name="bedrooms" placeholder="Bedrooms" value="<?php echo htmlspecialchars($_GET['bedrooms'] ?? ''); ?>">
      <input type="number" name="priceMin" placeholder="Min Price (MMK)" value="<?php echo htmlspecialchars($_GET['priceMin'] ?? ''); ?>">
      <input type="number" name="priceMax" placeholder="Max Price (MMK)" value="<?php echo htmlspecialchars($_GET['priceMax'] ?? ''); ?>">
      
      <button type="submit" class="search-btn">SEARCH NOW</button>
    </form>
  </div>

  <?php if ($search_performed): ?>
    <h3 style="color: #555;">Found <?php echo count($results); ?> Results</h3>
    <div class="grid">
      <?php foreach ($results as $row): ?>
        <div class="card">
          <img src="<?php echo !empty($row['image_url']) ? $row['image_url'] : 'https://via.placeholder.com/400x250'; ?>">
          <div class="card-body">
            <h4><?php echo htmlspecialchars($row['title']); ?></h4>
            <div class="price"><?php echo number_format($row['price']); ?> MMK</div>
            <p style="font-size: 13px; color: #777;">
                <?php echo htmlspecialchars($row['type']); ?> | <?php echo htmlspecialchars($row['bedrooms']); ?> BR | <?php echo htmlspecialchars($row['address']); ?>
            </p>
            <a href="viewproperty.php?id=<?php echo $row['id']; ?>" class="view-link">View Details</a>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (count($results) === 0): ?>
        <div style="grid-column: 1/-1; text-align: center; padding: 40px; background: #fff; border-radius: 10px;">
            <p>စိတ်မရှိပါနဲ့ခင်ဗျာ။ လူကြီးမင်းရှာဖွေနေတဲ့ အချက်အလက်နဲ့ ကိုက်ညီတဲ့ အိမ်ခြံမြေ မတွေ့ရှိပါ။</p>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

</body>
</html>

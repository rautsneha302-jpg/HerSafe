<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$success = "";

// Like karo
if (isset($_GET['like'])) {
    $id = (int)$_GET['like'];
    mysqli_query($conn, "UPDATE safe_routes SET likes = likes + 1 WHERE id = $id");
    header("Location: safe_routes.php"); exit();
}

// Naya route add karo
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $route_name  = mysqli_real_escape_string($conn, $_POST['route_name']);
    $from_place  = mysqli_real_escape_string($conn, $_POST['from_place']);
    $to_place    = mysqli_real_escape_string($conn, $_POST['to_place']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $user_id     = $_SESSION['user_id'];

    $sql = "INSERT INTO safe_routes (route_name, from_location, to_location, description, user_id, date)
            VALUES ('$route_name', '$from_place', '$to_place', '$description', '$user_id', NOW())";

    if (mysqli_query($conn, $sql)) {
        $success = "✅ Route shared successfully!";
    }
}

// Search
$search = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $routes = mysqli_query($conn, "SELECT * FROM safe_routes WHERE route_name LIKE '%$search%' OR from_location LIKE '%$search%' OR to_location LIKE '%$search%' ORDER BY likes DESC");
} else {
    $routes = mysqli_query($conn, "SELECT * FROM safe_routes ORDER BY likes DESC, date DESC");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Safe Routes — HerSafe</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DM Sans', sans-serif; background: #f5f0fb; min-height: 100vh; }

    nav {
      background: #6C3483;
      padding: 0 40px;
      height: 65px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .logo { font-family: 'Playfair Display', serif; font-size: 24px; color: white; text-decoration: none; }
    .logo span { color: #F1948A; }
    .back-btn { background: rgba(255,255,255,0.15); color: white; padding: 8px 18px; border-radius: 20px; text-decoration: none; font-size: 13px; }

    .container { max-width: 860px; margin: 40px auto; padding: 0 20px; }

    .page-title { font-family: 'Playfair Display', serif; font-size: 28px; color: #2C1A3E; margin-bottom: 6px; }
    .page-sub { color: #888; font-size: 14px; margin-bottom: 24px; }

    /* Search Bar */
    .search-bar {
      display: flex;
      gap: 10px;
      margin-bottom: 24px;
    }
    .search-bar input {
      flex: 1;
      padding: 12px 18px;
      border: 1.5px solid #e8e0f0;
      border-radius: 12px;
      font-size: 14px;
      font-family: 'DM Sans', sans-serif;
      outline: none;
      background: white;
    }
    .search-bar input:focus { border-color: #9B59B6; }
    .search-bar button {
      background: #6C3483;
      color: white;
      border: none;
      padding: 12px 22px;
      border-radius: 12px;
      font-size: 14px;
      cursor: pointer;
      font-family: 'DM Sans', sans-serif;
    }
    .search-bar button:hover { background: #512E6E; }

    /* Add Form */
    .form-card {
      background: white;
      border-radius: 16px;
      padding: 28px;
      margin-bottom: 30px;
      box-shadow: 0 2px 12px rgba(108,52,131,0.08);
    }
    .form-card h3 { font-family: 'Playfair Display', serif; font-size: 18px; color: #2C1A3E; margin-bottom: 18px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }
    .form-group { margin-bottom: 14px; }
    label { display: block; font-size: 12px; font-weight: 500; color: #2C1A3E; margin-bottom: 6px; }
    input, textarea {
      width: 100%;
      padding: 11px 14px;
      border: 1.5px solid #e8e0f0;
      border-radius: 10px;
      font-size: 14px;
      font-family: 'DM Sans', sans-serif;
      color: #2C1A3E;
      background: #faf8fc;
      outline: none;
      transition: border 0.2s;
    }
    input:focus, textarea:focus { border-color: #9B59B6; background: white; }
    textarea { resize: vertical; height: 80px; }
    .btn-submit {
      background: linear-gradient(135deg, #6C3483, #9B59B6);
      color: white;
      border: none;
      padding: 12px 28px;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      font-family: 'DM Sans', sans-serif;
    }
    .btn-submit:hover { opacity: 0.9; }

    .success-msg { background: #eafaf1; color: #1e8449; padding: 12px 16px; border-radius: 10px; font-size: 13px; margin-bottom: 16px; }

    /* Stats bar */
    .stats-bar {
      display: flex;
      gap: 16px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }
    .stat-pill {
      background: white;
      border-radius: 20px;
      padding: 8px 16px;
      font-size: 13px;
      color: #6C3483;
      box-shadow: 0 2px 8px rgba(108,52,131,0.08);
      font-weight: 500;
    }

    /* Route Cards */
    .routes-title { font-family: 'Playfair Display', serif; font-size: 20px; color: #2C1A3E; margin-bottom: 16px; }

    .route-card {
      background: white;
      border-radius: 16px;
      padding: 22px 24px;
      margin-bottom: 14px;
      box-shadow: 0 2px 12px rgba(108,52,131,0.06);
      border-left: 4px solid #9B59B6;
      transition: all 0.2s;
    }
    .route-card:hover { transform: translateX(4px); box-shadow: 0 4px 20px rgba(108,52,131,0.12); }

    .route-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; flex-wrap: wrap; gap: 8px; }
    .route-name { font-weight: 600; font-size: 16px; color: #2C1A3E; }

    .route-badges { display: flex; gap: 8px; align-items: center; }
    .badge-safe { background: #eafaf1; color: #1e8449; font-size: 11px; padding: 3px 10px; border-radius: 20px; }

    .route-path { font-size: 13px; color: #9B59B6; margin-bottom: 8px; font-weight: 500; }
    .route-desc { font-size: 13px; color: #888; line-height: 1.6; margin-bottom: 14px; }

    .route-footer { display: flex; align-items: center; justify-content: space-between; }
    .like-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: #f5f0fb;
      color: #6C3483;
      padding: 7px 16px;
      border-radius: 20px;
      text-decoration: none;
      font-size: 13px;
      font-weight: 500;
      transition: all 0.2s;
      border: 1.5px solid #e8e0f0;
    }
    .like-btn:hover { background: #6C3483; color: white; border-color: #6C3483; }
    .route-date { font-size: 11px; color: #bbb; }

    .empty-state { text-align: center; padding: 60px 20px; color: #aaa; }
    .empty-state div { font-size: 48px; margin-bottom: 12px; }

    /* Search result info */
    .search-info { font-size: 13px; color: #888; margin-bottom: 16px; }
    .search-info a { color: #9B59B6; text-decoration: none; }
  </style>
</head>
<body>

<nav>
  <a href="dashboard.php" class="logo">Her<span>Safe</span></a>
  <a href="dashboard.php" class="back-btn">← Back</a>
</nav>

<div class="container">

  <h2 class="page-title">🗺️ Safe Routes</h2>
  <p class="page-sub">Share aur discover karo safe routes apne area mein</p>

  <!-- Search Bar -->
  <form method="GET" action="safe_routes.php" class="search-bar">
    <input type="text" name="search" placeholder="🔍 Route search karo... (e.g. College, Bus Stand)" value="<?php echo htmlspecialchars($search); ?>">
    <button type="submit">Search</button>
  </form>

  <?php if ($search): ?>
    <p class="search-info">
      "<?php echo $search; ?>" ke liye results — <a href="safe_routes.php">Clear search</a>
    </p>
  <?php endif; ?>

  <!-- Add Route Form -->
  <div class="form-card">
    <h3>➕ Safe Route Share Karo</h3>

    <?php if ($success): ?>
      <div class="success-msg"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" action="safe_routes.php">
      <div class="form-group">
        <label>Route Name</label>
        <input type="text" name="route_name" placeholder="e.g. College to Bus Stand" required>
      </div>
      <div class="form-row">
        <div>
          <label>From</label>
          <input type="text" name="from_place" placeholder="Starting point" required>
        </div>
        <div>
          <label>To</label>
          <input type="text" name="to_place" placeholder="Ending point" required>
        </div>
      </div>
      <div class="form-group">
        <label>Kyun safe hai ye route?</label>
        <textarea name="description" placeholder="Well lit road, CCTV cameras, police chowki nearby..."></textarea>
      </div>
      <button type="submit" class="btn-submit">🗺️ Share Route</button>
    </form>
  </div>

  <!-- Routes List -->
  <?php
    $total_routes = mysqli_num_rows($routes);
  ?>
  <div class="stats-bar">
    <div class="stat-pill">🗺️ <?php echo $total_routes; ?> Routes</div>
    <div class="stat-pill">✅ Community Verified</div>
  </div>

  <h3 class="routes-title">📍 Community Safe Routes</h3>

  <?php if ($total_routes > 0): ?>
    <?php while ($route = mysqli_fetch_assoc($routes)): ?>
      <div class="route-card">
        <div class="route-header">
          <span class="route-name">🗺️ <?php echo htmlspecialchars($route['route_name']); ?></span>
          <div class="route-badges">
            <span class="badge-safe">✅ Safe</span>
          </div>
        </div>
        <div class="route-path">
          📍 <?php echo htmlspecialchars($route['from_location']); ?> → <?php echo htmlspecialchars($route['to_location']); ?>
        </div>
        <?php if (!empty($route['description'])): ?>
          <div class="route-desc"><?php echo htmlspecialchars($route['description']); ?></div>
        <?php endif; ?>
        <div class="route-footer">
          <a href="?like=<?php echo $route['id']; ?>" class="like-btn">
            👍 <?php echo $route['likes'] ?? 0; ?> Helpful
          </a>
          <span class="route-date">
            🗓️ <?php echo !empty($route['date']) ? date('d M Y', strtotime($route['date'])) : 'Recently'; ?>
          </span>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <div class="empty-state">
      <div>🗺️</div>
      <p><?php echo $search ? "Koi route nahi mila '$search' ke liye!" : "Koi route nahi mila. Pehli safe route share karo!"; ?></p>
    </div>
  <?php endif; ?>

</div>
</body>
</html>
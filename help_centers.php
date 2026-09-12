<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$centers = mysqli_query($conn, "SELECT * FROM help_centers ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Help Centers — HerSafe</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'DM Sans', sans-serif;
      background: #f5f0fb;
      min-height: 100vh;
    }
    nav {
      background: #6C3483;
      padding: 0 40px;
      height: 65px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .logo {
      font-family: 'Playfair Display', serif;
      font-size: 24px;
      color: white;
      text-decoration: none;
    }
    .logo span { color: #F1948A; }
    .back-btn {
      background: rgba(255,255,255,0.15);
      color: white;
      padding: 8px 18px;
      border-radius: 20px;
      text-decoration: none;
      font-size: 13px;
    }
    .back-btn:hover { background: rgba(255,255,255,0.25); }

    .container {
      max-width: 900px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .page-title {
      font-family: 'Playfair Display', serif;
      font-size: 28px;
      color: #2C1A3E;
      margin-bottom: 6px;
    }
    .page-sub {
      color: #888;
      font-size: 14px;
      margin-bottom: 30px;
    }

    /* Emergency strip */
    .emergency-strip {
      background: linear-gradient(135deg, #E74C3C, #C0392B);
      border-radius: 14px;
      padding: 18px 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 30px;
      flex-wrap: wrap;
      gap: 12px;
    }
    .emergency-strip p {
      color: white;
      font-size: 14px;
      font-weight: 500;
    }
    .emergency-numbers {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }
    .emergency-numbers a {
      background: rgba(255,255,255,0.2);
      color: white;
      padding: 6px 16px;
      border-radius: 20px;
      text-decoration: none;
      font-size: 13px;
      font-weight: 500;
      transition: background 0.2s;
    }
    .emergency-numbers a:hover { background: rgba(255,255,255,0.35); }

    /* Filter tabs */
    .filter-tabs {
      display: flex;
      gap: 8px;
      margin-bottom: 24px;
      flex-wrap: wrap;
    }
    .tab {
      padding: 8px 20px;
      border-radius: 20px;
      border: 1.5px solid #e8e0f0;
      background: white;
      font-size: 13px;
      cursor: pointer;
      transition: all 0.2s;
      font-family: 'DM Sans', sans-serif;
      color: #666;
    }
    .tab.active, .tab:hover {
      background: #6C3483;
      color: white;
      border-color: #6C3483;
    }

    /* Cards Grid */
    .centers-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
      gap: 16px;
    }

    .center-card {
      background: white;
      border-radius: 16px;
      padding: 22px;
      box-shadow: 0 2px 12px rgba(108,52,131,0.07);
      transition: all 0.2s;
      border-top: 4px solid #9B59B6;
    }
    .center-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 24px rgba(108,52,131,0.13);
    }
    .center-card.police { border-top-color: #2980B9; }
    .center-card.hospital { border-top-color: #E74C3C; }
    .center-card.ngo { border-top-color: #27AE60; }

    .card-icon { font-size: 32px; margin-bottom: 12px; }

    .card-type {
      font-size: 10px;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      color: #9B59B6;
      margin-bottom: 6px;
      font-weight: 500;
    }
    .center-card.police .card-type { color: #2980B9; }
    .center-card.hospital .card-type { color: #E74C3C; }
    .center-card.ngo .card-type { color: #27AE60; }

    .card-name {
      font-family: 'Playfair Display', serif;
      font-size: 17px;
      color: #2C1A3E;
      margin-bottom: 8px;
    }
    .card-address {
      font-size: 12px;
      color: #888;
      margin-bottom: 14px;
      line-height: 1.5;
    }
    .card-phone {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: #f5f0fb;
      color: #6C3483;
      padding: 7px 14px;
      border-radius: 20px;
      text-decoration: none;
      font-size: 13px;
      font-weight: 500;
      transition: all 0.2s;
    }
    .card-phone:hover {
      background: #6C3483;
      color: white;
    }

    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: #aaa;
      grid-column: 1/-1;
    }
    .empty-state div { font-size: 48px; margin-bottom: 12px; }
  </style>
</head>
<body>

<nav>
  <a href="dashboard.php" class="logo">Her<span>Safe</span></a>
  <a href="dashboard.php" class="back-btn">← Back</a>
</nav>

<div class="container">

  <h2 class="page-title">🏥 Help Centers</h2>
  <p class="page-sub">Find nearby police stations, hospitals and NGOs</p>

  <!-- Emergency Strip -->
  <div class="emergency-strip">
    <p>🚨 Immediate Emergency?</p>
    <div class="emergency-numbers">
      <a href="tel:112">📞 112 Police</a>
      <a href="tel:1091">📞 1091 Women</a>
      <a href="tel:108">📞 108 Ambulance</a>
    </div>
  </div>

  <!-- Filter Tabs -->
  <div class="filter-tabs">
    <button class="tab active" onclick="filterCards('all', this)">All Centers</button>
    <button class="tab" onclick="filterCards('police', this)">🚔 Police</button>
    <button class="tab" onclick="filterCards('hospital', this)">🏥 Hospital</button>
    <button class="tab" onclick="filterCards('ngo', this)">💚 NGO</button>
  </div>

  <!-- Centers Grid -->
  <div class="centers-grid" id="centersGrid">

    <?php if (mysqli_num_rows($centers) > 0): ?>
      <?php while ($c = mysqli_fetch_assoc($centers)): ?>
        <?php
          $type  = strtolower($c['type'] ?? 'ngo');
          $icons = ['police' => '🚔', 'hospital' => '🏥', 'ngo' => '💚', 'helpline' => '📞'];
          $icon  = $icons[$type] ?? '🏢';
        ?>
        <div class="center-card <?php echo $type; ?>" data-type="<?php echo $type; ?>">
          <div class="card-icon"><?php echo $icon; ?></div>
          <div class="card-type"><?php echo strtoupper($type); ?></div>
          <div class="card-name"><?php echo $c['name']; ?></div>
          <div class="card-address">📍 <?php echo $c['address'] ?? 'Address not available'; ?></div>
          <?php if (!empty($c['phone'])): ?>
            <a href="tel:<?php echo $c['phone']; ?>" class="card-phone">📞 <?php echo $c['phone']; ?></a>
          <?php endif; ?>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="empty-state">
        <div>🏥</div>
        <p>Koi help center nahi mila abhi.</p>
      </div>
    <?php endif; ?>

  </div>
</div>

<script>
function filterCards(type, btn) {
  // Active tab
  document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  btn.classList.add('active');

  // Filter cards
  document.querySelectorAll('.center-card').forEach(card => {
    if (type === 'all' || card.dataset.type === type) {
      card.style.display = 'block';
    } else {
      card.style.display = 'none';
    }
  });
}
</script>

</body>
</html>
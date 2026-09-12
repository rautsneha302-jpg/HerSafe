<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$tips = mysqli_query($conn, "SELECT * FROM selfdefense_tips ORDER BY step_number ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Self Defense — HerSafe</title>
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

    .container {
      max-width: 800px;
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
      margin-bottom: 10px;
    }

    /* Warning Banner */
    .warning-banner {
      background: #FEF9E7;
      border: 1px solid #F9CA24;
      border-radius: 12px;
      padding: 14px 18px;
      font-size: 13px;
      color: #7D6608;
      margin-bottom: 30px;
    }

    /* Tip Cards */
    .tip-card {
      background: white;
      border-radius: 16px;
      padding: 24px 28px;
      margin-bottom: 16px;
      box-shadow: 0 2px 12px rgba(108,52,131,0.07);
      display: flex;
      gap: 20px;
      align-items: flex-start;
      transition: all 0.2s;
      cursor: pointer;
    }
    .tip-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 24px rgba(108,52,131,0.13);
    }
    .tip-number {
      width: 48px;
      height: 48px;
      background: linear-gradient(135deg, #c0547a, #9B59B6);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 18px;
      font-weight: 700;
      flex-shrink: 0;
    }
    .tip-content { flex: 1; }
    .tip-title {
      font-family: 'Playfair Display', serif;
      font-size: 18px;
      color: #2C1A3E;
      margin-bottom: 8px;
    }
    .tip-desc {
      font-size: 14px;
      color: #666;
      line-height: 1.7;
    }
    .tip-expand {
      font-size: 12px;
      color: #9B59B6;
      margin-top: 10px;
      font-weight: 500;
    }

    /* Emergency reminder */
    .emergency-reminder {
      background: linear-gradient(135deg, #E74C3C, #C0392B);
      border-radius: 14px;
      padding: 20px 24px;
      color: white;
      text-align: center;
      margin-top: 30px;
    }
    .emergency-reminder h3 {
      font-family: 'Playfair Display', serif;
      font-size: 20px;
      margin-bottom: 8px;
    }
    .emergency-reminder p {
      font-size: 13px;
      opacity: 0.9;
      margin-bottom: 14px;
    }
    .emergency-reminder a {
      background: white;
      color: #E74C3C;
      padding: 10px 24px;
      border-radius: 20px;
      text-decoration: none;
      font-weight: 600;
      font-size: 14px;
    }
  </style>
</head>
<body>

<nav>
  <a href="dashboard.php" class="logo">Her<span>Safe</span></a>
  <a href="dashboard.php" class="back-btn">← Back</a>
</nav>

<div class="container">

  <h2 class="page-title">💪 Self Defense Tips</h2>
  <p class="page-sub">Learn practical techniques to protect yourself</p>

  <div class="warning-banner">
    ⚠️ <strong>Remember:</strong> Pehli priority hamesha bhagna aur help maangna hai. Self defense sirf last resort ke liye hai.
  </div>

  <?php if (mysqli_num_rows($tips) > 0): ?>
    <?php while ($tip = mysqli_fetch_assoc($tips)): ?>
      <div class="tip-card">
        <div class="tip-number"><?php echo $tip['step_number']; ?></div>
        <div class="tip-content">
          <div class="tip-title"><?php echo $tip['title']; ?></div>
          <div class="tip-desc"><?php echo $tip['description']; ?></div>
          <div class="tip-expand">💡 Practice karo regularly</div>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p style="text-align:center;color:#aaa;padding:40px">Koi tips nahi mili abhi.</p>
  <?php endif; ?>

  <!-- Emergency Reminder -->
  <div class="emergency-reminder">
    <h3>🚨 Emergency mein?</h3>
    <p>Self defense ke saath turant police ko call karo!</p>
    <a href="sos_alert.php">SOS Alert Bhejo</a>
  </div>

</div>

</body>
</html>
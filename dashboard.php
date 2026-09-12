<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Language setup
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = $_SESSION['lang'] ?? 'en';

$text = [
    'en' => ['welcome'=>'Welcome', 'safe'=>'You are safe with HerSafe. What do you need today?', 'sos'=>'SOS Alert', 'routes'=>'Safe Routes', 'help'=>'Help Centers', 'stories'=>'Stories', 'defense'=>'Self Defense', 'report'=>'Report Area', 'contacts'=>'Emergency Contacts', 'journey'=>'Safe Journey', 'location'=>'Share Location', 'profile'=>'My Profile', 'logout'=>'Logout'],
    'hi' => ['welcome'=>'स्वागत है', 'safe'=>'आप HerSafe के साथ सुरक्षित हैं। आज क्या चाहिए?', 'sos'=>'SOS अलर्ट', 'routes'=>'सुरक्षित मार्ग', 'help'=>'सहायता केंद्र', 'stories'=>'कहानियाँ', 'defense'=>'आत्मरक्षा', 'report'=>'क्षेत्र रिपोर्ट', 'contacts'=>'आपातकालीन संपर्क', 'journey'=>'सुरक्षित यात्रा', 'location'=>'लोकेशन शेयर करें', 'profile'=>'मेरी प्रोफाइल', 'logout'=>'लॉगआउट'],
    'mr' => ['welcome'=>'स्वागत आहे', 'safe'=>'तुम्ही HerSafe सोबत सुरक्षित आहात. आज काय हवे?', 'sos'=>'SOS अलर्ट', 'routes'=>'सुरक्षित मार्ग', 'help'=>'मदत केंद्रे', 'stories'=>'कथा', 'defense'=>'स्वसंरक्षण', 'report'=>'क्षेत्र अहवाल', 'contacts'=>'आपत्कालीन संपर्क', 'journey'=>'सुरक्षित प्रवास', 'location'=>'लोकेशन शेअर करा', 'profile'=>'माझी प्रोफाइल', 'logout'=>'लॉगआउट'],
];
$t = $text[$lang];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — HerSafe</title>
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
    .nav-right { display: flex; align-items: center; gap: 12px; }
    .nav-right span { color: rgba(255,255,255,0.85); font-size: 14px; }
    .logout-btn { background: rgba(255,255,255,0.15); color: white; padding: 8px 18px; border-radius: 20px; font-size: 13px; text-decoration: none; }

    .lang-btns { display: flex; gap: 6px; }
    .lang-btn {
      padding: 5px 12px;
      border-radius: 20px;
      text-decoration: none;
      font-size: 12px;
      font-weight: 600;
      transition: all 0.2s;
    }
    .lang-btn.active { background: white; color: #6C3483; }
    .lang-btn.inactive { background: rgba(255,255,255,0.2); color: white; }
    .lang-btn:hover { background: white; color: #6C3483; }

    .container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
    .welcome-card {
      background: linear-gradient(135deg, #6C3483, #c0547a);
      border-radius: 20px;
      padding: 36px;
      color: white;
      margin-bottom: 30px;
    }
    .welcome-card h2 { font-family: 'Playfair Display', serif; font-size: 28px; margin-bottom: 8px; }
    .welcome-card p { opacity: 0.85; font-size: 15px; }
    .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; }
    .card {
      background: white;
      border-radius: 16px;
      padding: 28px 20px;
      text-align: center;
      text-decoration: none;
      transition: all 0.2s;
      box-shadow: 0 2px 12px rgba(108,52,131,0.08);
    }
    .card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(108,52,131,0.15); }
    .card-icon { font-size: 36px; margin-bottom: 12px; }
    .card h3 { font-size: 15px; color: #2C1A3E; font-weight: 500; }

    /* Highlight location card */
    .card-location {
      background: linear-gradient(135deg, #f5f0fb, #ede0f7);
      border: 2px solid #9B59B6;
    }
    .card-location h3 { color: #6C3483; font-weight: 600; }
  </style>
</head>
<body>

<nav>
  <a href="dashboard.php" class="logo">Her<span>Safe</span></a>
  <div class="nav-right">
    <div class="lang-btns">
      <a href="?lang=en" class="lang-btn <?php echo $lang=='en'?'active':'inactive'; ?>">EN</a>
      <a href="?lang=hi" class="lang-btn <?php echo $lang=='hi'?'active':'inactive'; ?>">हि</a>
      <a href="?lang=mr" class="lang-btn <?php echo $lang=='mr'?'active':'inactive'; ?>">म</a>
    </div>
    <span>👤 <?php echo $_SESSION['user_name']; ?></span>
    <a href="logout.php" class="logout-btn"><?php echo $t['logout']; ?></a>
  </div>
</nav>

<div class="container">

  <div class="welcome-card">
    <h2><?php echo $t['welcome']; ?>, <?php echo $_SESSION['user_name']; ?>! 🌸</h2>
    <p><?php echo $t['safe']; ?></p>
  </div>

  <div class="grid">
    <a href="sos_alert.php" class="card">
      <div class="card-icon">🚨</div>
      <h3><?php echo $t['sos']; ?></h3>
    </a>
    <a href="safe_routes.php" class="card">
      <div class="card-icon">🗺️</div>
      <h3><?php echo $t['routes']; ?></h3>
    </a>
    <a href="help_centers.php" class="card">
      <div class="card-icon">🏥</div>
      <h3><?php echo $t['help']; ?></h3>
    </a>
    <a href="stories.php" class="card">
      <div class="card-icon">💗</div>
      <h3><?php echo $t['stories']; ?></h3>
    </a>
    <a href="selfdefense.php" class="card">
      <div class="card-icon">💪</div>
      <h3><?php echo $t['defense']; ?></h3>
    </a>
    <a href="report_area.php" class="card">
      <div class="card-icon">⚠️</div>
      <h3><?php echo $t['report']; ?></h3>
    </a>
    <a href="emergency_contacts.php" class="card">
      <div class="card-icon">📞</div>
      <h3><?php echo $t['contacts']; ?></h3>
    </a>
    <a href="safe_journey.php" class="card">
      <div class="card-icon">🌙</div>
      <h3><?php echo $t['journey']; ?></h3>
    </a>
    <a href="location_share.php" class="card card-location">
      <div class="card-icon">📍</div>
      <h3><?php echo $t['location']; ?></h3>
    </a>
    <a href="profile.php" class="card">
      <div class="card-icon">👤</div>
      <h3><?php echo $t['profile']; ?></h3>
    </a>
  </div>

</div>

</body>
</html>
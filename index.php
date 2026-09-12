<?php
session_start();
$contactNames = [];
require_once 'includes/db.php';
if (isset($_SESSION['user_id'])) {
    $uid = intval($_SESSION['user_id']);
} else {
    $uid = 1;
}
$res = mysqli_query($conn, "SELECT contact1_name, contact2_name, contact3_name FROM emergency_contacts WHERE user_id = $uid LIMIT 1");
if ($res && $row = mysqli_fetch_assoc($res)) {
    if (!empty($row['contact1_name'])) $contactNames[] = $row['contact1_name'];
    if (!empty($row['contact2_name'])) $contactNames[] = $row['contact2_name'];
    if (!empty($row['contact3_name'])) $contactNames[] = $row['contact3_name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HerSafe — Women Safety Platform</title>

  <!-- PWA -->
<link rel="manifest" href="/herSafe/manifest.json">
  <meta name="theme-color" content="#6C3483">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-title" content="HerSafe">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <link rel="apple-touch-icon" href="/herSafe/icon-192.png">

  <script>
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', function() {
        navigator.serviceWorker.register('/herSafe/sw.js')
          .then(reg => console.log('SW registered!'))
          .catch(err => console.log('SW error:', err));
      });
    }
  </script>

  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

  <style>
    :root {
      --purple: #6C3483;
      --purple-dark: #512E6E;
      --purple-light: #9B59B6;
      --pink: #F1948A;
      --red: #E74C3C;
      --white: #FFFFFF;
      --light: #F9F0FF;
      --muted: #8E6BA8;
      --text: #2C1A3E;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DM Sans', sans-serif; background: var(--white); color: var(--text); overflow-x: hidden; }

    .emergency-bar {
      background: var(--red);
      color: white;
      text-align: center;
      padding: 8px 20px;
      font-size: 13px;
      letter-spacing: 1px;
      animation: pulse-bar 2s infinite;
    }
    @keyframes pulse-bar {
      0%, 100% { background: #E74C3C; }
      50%       { background: #C0392B; }
    }
    .emergency-bar a {
      color: white; text-decoration: none; font-weight: 500;
      margin: 0 12px;
      border-bottom: 1px solid rgba(255,255,255,0.5);
      transition: opacity .2s;
    }
    .emergency-bar a:hover { opacity: .75; }

    nav {
      background: var(--purple);
      padding: 0 40px;
      display: flex; align-items: center; justify-content: space-between;
      height: 65px;
      position: sticky; top: 0; z-index: 100;
      box-shadow: 0 2px 20px rgba(108,52,131,0.3);
    }
    .logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
    .logo-icon { width: 38px; height: 38px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; }
    .logo-text { font-family: 'Playfair Display', serif; font-size: 24px; color: white; font-weight: 700; }
    .logo-text span { color: var(--pink); }
    .nav-links { display: flex; gap: 6px; list-style: none; align-items: center; }
    .nav-links a { color: rgba(255,255,255,0.85); text-decoration: none; font-size: 13px; padding: 7px 14px; border-radius: 20px; transition: all 0.2s; }
    .nav-links a:hover { background: rgba(255,255,255,0.15); color: white; }
    .nav-sos {
      background: var(--red) !important;
      color: white !important;
      font-weight: 600 !important;
      animation: sos-pulse 1.2s infinite;
      cursor: pointer;
    }
    @keyframes sos-pulse {
      0%, 100% { box-shadow: 0 0 0 0 rgba(231,76,60,.6); }
      50%       { box-shadow: 0 0 0 10px rgba(231,76,60,0); }
    }

    .hero {
      background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple) 50%, #8E44AD 100%);
      min-height: 88vh;
      display: flex; align-items: center;
      position: relative; overflow: hidden;
      padding: 60px 40px;
    }
    .hero::before {
      content: '';
      position: absolute; inset: 0;
      background:
        radial-gradient(circle at 20% 50%, rgba(241,148,138,0.15) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(255,255,255,0.08) 0%, transparent 40%);
    }
    .hero::after {
      content: '🛡️';
      position: absolute; right: 8%; top: 50%;
      transform: translateY(-50%);
      font-size: 280px; opacity: 0.06;
      animation: float 6s ease-in-out infinite;
    }
    @keyframes float {
      0%, 100% { transform: translateY(-50%) rotate(-5deg); }
      50%       { transform: translateY(-55%) rotate(5deg); }
    }
    .hero-content { position: relative; z-index: 1; max-width: 620px; }
    .hero-badge { display: inline-block; background: rgba(241,148,138,0.2); border: 1px solid rgba(241,148,138,0.4); color: var(--pink); font-size: 12px; letter-spacing: 2px; text-transform: uppercase; padding: 6px 16px; border-radius: 20px; margin-bottom: 24px; }
    .hero h1 { font-family: 'Playfair Display', serif; font-size: clamp(38px,6vw,64px); color: white; line-height: 1.15; margin-bottom: 20px; }
    .hero h1 em { color: var(--pink); font-style: italic; }
    .hero p { font-size: 17px; color: rgba(255,255,255,0.8); line-height: 1.7; margin-bottom: 36px; max-width: 500px; }
    .hero-buttons { display: flex; gap: 14px; flex-wrap: wrap; }
    .btn-primary {
      background: var(--pink); color: white;
      padding: 14px 32px; border: none; border-radius: 30px;
      font-size: 15px; font-weight: 500; cursor: pointer;
      text-decoration: none; transition: all 0.2s;
      box-shadow: 0 4px 20px rgba(241,148,138,0.4);
      display: inline-block;
    }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(241,148,138,0.5); }
    .btn-outline { background: transparent; color: white; padding: 14px 32px; border: 1px solid rgba(255,255,255,0.4); border-radius: 30px; font-size: 15px; cursor: pointer; text-decoration: none; transition: all 0.2s; display: inline-block; }
    .btn-outline:hover { background: rgba(255,255,255,0.1); border-color: white; }

    .sos-float {
      position: fixed; bottom: 30px; right: 30px; z-index: 999;
      width: 76px; height: 76px;
      background: var(--red); color: white;
      border: 3px solid rgba(255,255,255,0.3);
      border-radius: 50%;
      font-size: 13px; font-weight: 700; letter-spacing: 1px;
      cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      flex-direction: column; gap: 2px;
      text-decoration: none;
      animation: sos-float-pulse 1.2s infinite;
    }
    .sos-float span:first-child { font-size: 22px; }
    @keyframes sos-float-pulse {
      0%   { box-shadow: 0 4px 20px rgba(231,76,60,.5), 0 0 0 0   rgba(231,76,60,.5); }
      50%  { box-shadow: 0 4px 20px rgba(231,76,60,.5), 0 0 0 18px rgba(231,76,60,0); }
      100% { box-shadow: 0 4px 20px rgba(231,76,60,.5), 0 0 0 0   rgba(231,76,60,.5); }
    }
    .sos-float::after {
      content: 'Emergency SOS';
      position: absolute;
      right: 84px; top: 50%;
      transform: translateY(-50%);
      background: #111;
      color: #fff;
      font-size: 12px; font-weight: 500;
      padding: 6px 12px; border-radius: 8px;
      white-space: nowrap;
      opacity: 0; pointer-events: none;
      transition: opacity .2s;
    }
    .sos-float:hover::after { opacity: 1; }

    /* ══ SOS MODAL — Purple Theme ══ */
    .sos-overlay {
      position: fixed; inset: 0; z-index: 9999;
      background: rgba(44,26,62,0.85);
      backdrop-filter: blur(6px);
      display: flex; align-items: center; justify-content: center;
      opacity: 0; pointer-events: none;
      transition: opacity .3s;
    }
    .sos-overlay.active { opacity: 1; pointer-events: all; }
    .sos-modal {
      background: linear-gradient(160deg, #512E6E 0%, #6C3483 100%);
      border: 1.5px solid rgba(255,255,255,0.2);
      border-radius: 24px;
      padding: 36px 32px;
      max-width: 360px; width: 90%;
      text-align: center;
      animation: modalPop .35s ease;
    }
    @keyframes modalPop {
      from { transform: scale(.85); opacity: 0; }
      to   { transform: scale(1);   opacity: 1; }
    }
    .modal-sos-icon {
      width: 90px; height: 90px;
      background: rgba(241,148,138,0.2);
      border: 2px solid rgba(241,148,138,0.45);
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 38px;
      margin: 0 auto 20px;
      box-shadow: 0 0 0 16px rgba(241,148,138,0.08);
      animation: modalRing 1.2s infinite;
    }
    @keyframes modalRing {
      0%,100% { box-shadow: 0 0 0 16px rgba(241,148,138,0.08); }
      50%      { box-shadow: 0 0 0 28px rgba(241,148,138,0); }
    }
    .modal-title {
      font-family: 'Playfair Display', serif;
      font-size: 24px; color: white; margin-bottom: 8px;
    }
    .modal-sub { font-size: 14px; color: rgba(255,255,255,.6); margin-bottom: 24px; line-height: 1.6; }

    .modal-countdown {
      position: relative; width: 80px; height: 80px;
      margin: 0 auto 24px;
    }
    .modal-countdown svg { transform: rotate(-90deg); }
    .cd-track { fill: none; stroke: rgba(255,255,255,.12); stroke-width: 5; }
    .cd-prog  {
      fill: none; stroke: #F1948A; stroke-width: 5;
      stroke-linecap: round;
      stroke-dasharray: 220;
      stroke-dashoffset: 0;
      transition: stroke-dashoffset 1s linear;
    }
    .cd-num {
      position: absolute; inset: 0;
      display: flex; align-items: center; justify-content: center;
      font-family: 'Playfair Display', serif;
      font-size: 26px; font-weight: 700; color: #F1948A;
    }

    .modal-alert-label { font-size: 11px; color: rgba(255,255,255,.4); letter-spacing: .1em; text-transform: uppercase; margin-bottom: 10px; }
    .modal-contacts {
      display: flex; gap: 8px; justify-content: center;
      flex-wrap: wrap; margin-bottom: 28px;
    }
    .modal-contact-chip {
      background: rgba(241,148,138,0.15);
      border: 1px solid rgba(241,148,138,0.35);
      color: #F1948A; font-size: 12px;
      padding: 5px 14px; border-radius: 999px;
    }

    .modal-actions { display: flex; gap: 10px; }
    .modal-go-btn {
      flex: 1; background: #F1948A; color: white;
      border: none; border-radius: 12px;
      font-family: 'Playfair Display', serif;
      font-size: 15px; font-weight: 700;
      padding: 14px; cursor: pointer;
      transition: background .2s;
      box-shadow: 0 4px 16px rgba(241,148,138,0.35);
    }
    .modal-go-btn:hover { background: #E07B6F; }
    .modal-cancel-btn {
      flex: 1; background: rgba(255,255,255,.08);
      border: 1px solid rgba(255,255,255,.15);
      color: rgba(255,255,255,.65);
      border-radius: 12px; font-size: 14px;
      padding: 14px; cursor: pointer;
      transition: all .2s;
    }
    .modal-cancel-btn:hover { background: rgba(255,255,255,.15); color: white; }

    .features { padding: 80px 40px; background: var(--light); }
    .section-label { text-align: center; font-size: 11px; letter-spacing: 3px; text-transform: uppercase; color: var(--muted); margin-bottom: 12px; }
    .section-title { font-family: 'Playfair Display', serif; font-size: clamp(28px,4vw,40px); text-align: center; color: var(--purple-dark); margin-bottom: 56px; }
    .features-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px; max-width: 1100px; margin: 0 auto; }
    .feature-card { background: white; border-radius: 16px; padding: 32px 28px; box-shadow: 0 2px 20px rgba(108,52,131,.08); transition: all .2s; }
    .feature-card:hover { transform: translateY(-4px); box-shadow: 0 8px 32px rgba(108,52,131,.15); }
    .feature-icon { width: 54px; height: 54px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 26px; margin-bottom: 18px; }
    .feature-card h3 { font-family: 'Playfair Display', serif; font-size: 19px; color: var(--purple-dark); margin-bottom: 10px; }
    .feature-card p { font-size: 14px; color: #666; line-height: 1.65; }
    .feature-card a { display: inline-block; margin-top: 16px; color: var(--purple); font-size: 13px; font-weight: 500; text-decoration: none; border-bottom: 1px solid var(--purple-light); padding-bottom: 2px; transition: color .2s; }
    .feature-card a:hover { color: var(--pink); border-color: var(--pink); }

    .age-section { padding: 80px 40px; background: white; }
    .age-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px,1fr)); gap: 16px; max-width: 1000px; margin: 0 auto; }
    .age-card { background: var(--light); border-radius: 14px; padding: 28px 20px; text-align: center; border: 2px solid transparent; transition: all .2s; cursor: pointer; }
    .age-card:hover { border-color: var(--purple-light); background: white; box-shadow: 0 8px 24px rgba(108,52,131,.1); }
    .age-emoji { font-size: 40px; margin-bottom: 12px; }
    .age-card h4 { font-family: 'Playfair Display', serif; font-size: 16px; color: var(--purple-dark); margin-bottom: 6px; }
    .age-card p { font-size: 12px; color: var(--muted); }

    .emergency-section { background: var(--purple-dark); padding: 60px 40px; text-align: center; }
    .emergency-section h2 { font-family: 'Playfair Display', serif; font-size: 32px; color: white; margin-bottom: 8px; }
    .emergency-section p { color: rgba(255,255,255,.6); margin-bottom: 40px; font-size: 15px; }
    .numbers-grid { display: flex; gap: 20px; justify-content: center; flex-wrap: wrap; }
    .number-card { background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.15); border-radius: 14px; padding: 24px 36px; min-width: 160px; transition: all .2s; cursor: pointer; text-decoration: none; }
    .number-card:hover { background: rgba(255,255,255,.15); transform: translateY(-3px); }
    .number-card .num { font-family: 'Playfair Display', serif; font-size: 36px; color: var(--pink); font-weight: 700; }
    .number-card .label { font-size: 12px; color: rgba(255,255,255,.6); margin-top: 6px; }

    footer { background: var(--text); color: rgba(255,255,255,.6); text-align: center; padding: 28px 40px; font-size: 13px; }
    footer strong { color: var(--pink); }

    @media (max-width: 600px) {
      nav { padding: 0 16px; }
      .nav-links { display: none; }
      .hero { padding: 40px 20px; min-height: auto; }
      .features, .age-section, .emergency-section { padding: 50px 20px; }
      .sos-modal { padding: 28px 20px; }
    }
  </style>
</head>
<body>

<!-- SOS MODAL -->
<div class="sos-overlay" id="sosOverlay">
  <div class="sos-modal">
    <div class="modal-sos-icon">🚨</div>
    <div class="modal-title">Sending SOS Alert</div>
    <div class="modal-sub">
      Your emergency contacts will be notified<br>with your live location via WhatsApp & SMS.
    </div>
    <div class="modal-countdown">
      <svg viewBox="0 0 80 80" width="80" height="80">
        <circle class="cd-track" cx="40" cy="40" r="35"/>
        <circle class="cd-prog"  cx="40" cy="40" r="35" id="cdRing"/>
      </svg>
      <div class="cd-num" id="cdNum">5</div>
    </div>
    <div class="modal-alert-label">Alerting contacts</div>
    <div class="modal-contacts" id="modalContacts">
      <?php if (!empty($contactNames)): ?>
        <?php foreach ($contactNames as $name): ?>
          <div class="modal-contact-chip"><?php echo htmlspecialchars($name); ?></div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="modal-contact-chip" style="color:rgba(255,255,255,0.4);border-color:rgba(255,255,255,0.15)">No contacts saved</div>
      <?php endif; ?>
    </div>
    <div class="modal-actions">
      <button class="modal-go-btn"     onclick="goToSOS()">Go to SOS Now</button>
      <button class="modal-cancel-btn" onclick="cancelSOS()">✕ Cancel</button>
    </div>
  </div>
</div>

<!-- Emergency Bar -->
<div class="emergency-bar">
  🚨 Emergency Numbers:
  <a href="tel:112">112 — Police</a>
  <a href="tel:1091">1091 — Women Helpline</a>
  <a href="tel:1098">1098 — Childline</a>
  <a href="tel:108">108 — Ambulance</a>
</div>

<!-- Navbar -->
<nav>
  <a href="index.php" class="logo">
    <div class="logo-icon">🛡️</div>
    <div class="logo-text">Her<span>Safe</span></div>
  </a>
  <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="report_area.php">Report Area</a></li>
    <li><a href="help_centers.php">Help Centers</a></li>
    <li><a href="safe_routes.php">Safe Routes</a></li>
    <li><a href="stories.php">Stories</a></li>
    <li><a href="selfdefense.php">Self Defense</a></li>
    <li>
      <a href="#" class="nav-sos" onclick="triggerSOS(); return false;">🚨 SOS</a>
    </li>
    <?php if (isset($_SESSION['user_id'])): ?>
      <li><a href="dashboard.php" style="background:rgba(255,255,255,0.15);border-radius:20px;color:white">
        👤 <?php echo htmlspecialchars($_SESSION['user_name']); ?>
      </a></li>
    <?php else: ?>
      <li><a href="login.php" style="background:rgba(255,255,255,0.15);border-radius:20px;color:white">Login</a></li>
    <?php endif; ?>
  </ul>
</nav>

<!-- Hero -->
<section class="hero">
  <div class="hero-content">
    <div class="hero-badge">🛡️ Women Safety Platform</div>
    <h1>You Are Never<br><em>Alone</em> — We Are<br>Always Here</h1>
    <p>HerSafe is a community-driven safety platform designed for every female — from small girls to elderly women. Report unsafe areas, find help, share safe routes and get emergency support instantly.</p>
    <div class="hero-buttons">
      <a href="#" class="btn-primary" onclick="triggerSOS(); return false;">🚨 Get Help Now</a>
      <a href="register.php" class="btn-outline">📋 Register Free</a>
    </div>
  </div>
</section>

<!-- Features -->
<section class="features">
  <div class="section-label">What We Offer</div>
  <h2 class="section-title">Features of HerSafe</h2>
  <div class="features-grid">
    <div class="feature-card">
      <div class="feature-icon" style="background:#FDEDEC">🚨</div>
      <h3>SOS Emergency Alert</h3>
      <p>One tap sends emergency alert and auto-calls your 3 saved contacts with your live location.</p>
      <a href="#" onclick="triggerSOS(); return false;">Get Help Now →</a>
    </div>
    <div class="feature-card">
      <div class="feature-icon" style="background:#EAF9F0">🌙</div>
      <h3>Safe Journey Mode</h3>
      <p>Night shift pe akele ho? Journey track karo — Silent SOS se family aur police ko live location milegi.</p>
      <a href="safe_journey.php">Start Journey →</a>
    </div>
    <div class="feature-card">
      <div class="feature-icon" style="background:#F4ECF7">⚠️</div>
      <h3>Report Unsafe Area</h3>
      <p>Report dangerous locations in your city. Help other women see and avoid unsafe places near them.</p>
      <a href="report_area.php">Report Area →</a>
    </div>
    <div class="feature-card">
      <div class="feature-icon" style="background:#EAF2FF">🏥</div>
      <h3>Find Help Centers</h3>
      <p>Find nearby police stations, hospitals, NGOs and women helplines with one click contact details.</p>
      <a href="help_centers.php">Find Help →</a>
    </div>
    <div class="feature-card">
      <div class="feature-icon" style="background:#E9F7EF">🗺️</div>
      <h3>Safe Route Sharing</h3>
      <p>Share and discover safe routes in your area. Know which paths are well lit and safe before you travel.</p>
      <a href="safe_routes.php">View Routes →</a>
    </div>
    <div class="feature-card">
      <div class="feature-icon" style="background:#FEF9E7">💪</div>
      <h3>Self Defense Tips</h3>
      <p>Learn practical self defense techniques with step by step instructions.</p>
      <a href="selfdefense.php">Learn Tips →</a>
    </div>
    <div class="feature-card">
      <div class="feature-icon" style="background:#FDF2F8">💗</div>
      <h3>Motivational Stories</h3>
      <p>Read real courage stories from women in our community. Share your own story to inspire others.</p>
      <a href="stories.php">Read Stories →</a>
    </div>
    <div class="feature-card">
      <div class="feature-icon" style="background:#EBF5FB">📞</div>
      <h3>Emergency Contacts</h3>
      <p>Save 3 trusted contacts — SOS button pe automatically inhe call jayega aur location share hogi.</p>
      <a href="emergency_contacts.php">Add Contacts →</a>
    </div>
  </div>
</section>

<!-- Age Groups -->
<section class="age-section">
  <div class="section-label">For Everyone</div>
  <h2 class="section-title" style="margin-bottom:40px">Designed For Every Age</h2>
  <div class="age-grid">
    <div class="age-card"><div class="age-emoji">👧</div><h4>Small Girls</h4><p>Ages 6–12<br>Kids Safety Corner<br>Childline 1098</p></div>
    <div class="age-card"><div class="age-emoji">🧒</div><h4>Teenage Girls</h4><p>Ages 13–17<br>School Safety Tips<br>Cyberbullying Help</p></div>
    <div class="age-card"><div class="age-emoji">👩</div><h4>College Women</h4><p>Ages 18–25<br>All Features<br>Full Access</p></div>
    <div class="age-card"><div class="age-emoji">👩‍💼</div><h4>Working Women</h4><p>Ages 25+<br>Night Journey Safety<br>POSH Act Help</p></div>
    <div class="age-card"><div class="age-emoji">👵</div><h4>Elderly Women</h4><p>Ages 60+<br>Large Text<br>Medical Emergency</p></div>
  </div>
</section>

<!-- Emergency Numbers -->
<section class="emergency-section">
  <h2>Emergency Numbers</h2>
  <p>Save these numbers — available 24 hours, 7 days a week</p>
  <div class="numbers-grid">
    <a href="tel:112"  class="number-card"><div class="num">112</div><div class="label">🚔 Police Emergency</div></a>
    <a href="tel:1091" class="number-card"><div class="num">1091</div><div class="label">👩 Women Helpline</div></a>
    <a href="tel:1098" class="number-card"><div class="num">1098</div><div class="label">👧 Childline</div></a>
    <a href="tel:108"  class="number-card"><div class="num">108</div><div class="label">🚑 Ambulance</div></a>
    <a href="tel:181"  class="number-card"><div class="num">181</div><div class="label">💬 Women in Distress</div></a>
  </div>
</section>

<!-- Floating SOS -->
<a href="#" class="sos-float" onclick="triggerSOS(); return false;">
  <span>🚨</span>
  <span>SOS</span>
</a>

<footer>
  <p>Made with 💗 for women's safety — <strong>HerSafe</strong> © 2026</p>
</footer>

<script>
const COUNTDOWN_SECS = 5;
const CIRCUMFERENCE  = 220;
let cdInterval = null;
let cdLeft     = COUNTDOWN_SECS;

function triggerSOS() {
  cdLeft = COUNTDOWN_SECS;
  document.getElementById('cdNum').textContent = cdLeft;
  document.getElementById('cdRing').style.strokeDashoffset = 0;
  document.getElementById('sosOverlay').classList.add('active');
  document.body.style.overflow = 'hidden';

  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(pos) {
      sessionStorage.setItem('sos_lat', pos.coords.latitude);
      sessionStorage.setItem('sos_lng', pos.coords.longitude);
    });
  }

  cdInterval = setInterval(function() {
    cdLeft--;
    document.getElementById('cdNum').textContent = cdLeft;
    const offset = CIRCUMFERENCE * (1 - cdLeft / COUNTDOWN_SECS);
    document.getElementById('cdRing').style.strokeDashoffset = offset;
    if (cdLeft <= 0) {
      clearInterval(cdInterval);
      goToSOS();
    }
  }, 1000);
}

function cancelSOS() {
  clearInterval(cdInterval);
  document.getElementById('sosOverlay').classList.remove('active');
  document.body.style.overflow = '';
}

function goToSOS() {
  clearInterval(cdInterval);
  window.location.href = 'sos_alert.php';
}

document.getElementById('sosOverlay').addEventListener('click', function(e) {
  if (e.target === this) cancelSOS();
});

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') cancelSOS();
});
</script>

</body>
</html>
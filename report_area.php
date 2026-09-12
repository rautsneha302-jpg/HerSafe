<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$success = "";
$error   = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $location    = mysqli_real_escape_string($conn, $_POST['location']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $reported_by = $_SESSION['user_id'];

    $sql = "INSERT INTO unsafe_areas (location, description, reported_by, status, date)
            VALUES ('$location', '$description', '$reported_by', 'pending', NOW())";

    if (mysqli_query($conn, $sql)) {
        $success = "✅ Area reported successfully! Admin review karega.";
    } else {
        $error = "❌ Something went wrong!";
    }
}

$areas = mysqli_query($conn, "SELECT * FROM unsafe_areas ORDER BY date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Report Area — HerSafe</title>
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
      margin-bottom: 30px;
    }

    /* Form */
    .form-card {
      background: white;
      border-radius: 16px;
      padding: 28px;
      margin-bottom: 30px;
      box-shadow: 0 2px 12px rgba(108,52,131,0.08);
    }
    .form-card h3 {
      font-family: 'Playfair Display', serif;
      font-size: 18px;
      color: #2C1A3E;
      margin-bottom: 18px;
    }
    .form-group { margin-bottom: 16px; }
    label {
      display: block;
      font-size: 12px;
      font-weight: 500;
      color: #2C1A3E;
      margin-bottom: 6px;
    }
    input, textarea, select {
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
    input:focus, textarea:focus {
      border-color: #9B59B6;
      background: white;
    }
    textarea { height: 100px; resize: vertical; }
    .btn {
      background: linear-gradient(135deg, #E74C3C, #C0392B);
      color: white;
      border: none;
      padding: 12px 28px;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      font-family: 'DM Sans', sans-serif;
      transition: opacity 0.2s;
    }
    .btn:hover { opacity: 0.9; }

    .success-msg {
      background: #eafaf1;
      color: #1e8449;
      padding: 12px 16px;
      border-radius: 10px;
      font-size: 13px;
      margin-bottom: 16px;
    }
    .error-msg {
      background: #fdecea;
      color: #c0392b;
      padding: 12px 16px;
      border-radius: 10px;
      font-size: 13px;
      margin-bottom: 16px;
    }

    /* Reports List */
    .reports-title {
      font-family: 'Playfair Display', serif;
      font-size: 22px;
      color: #2C1A3E;
      margin-bottom: 16px;
    }

    .report-card {
      background: white;
      border-radius: 14px;
      padding: 20px 24px;
      margin-bottom: 14px;
      box-shadow: 0 2px 12px rgba(108,52,131,0.06);
      border-left: 4px solid #E74C3C;
      transition: all 0.2s;
    }
    .report-card:hover {
      transform: translateX(4px);
      box-shadow: 0 4px 20px rgba(231,76,60,0.12);
    }
    .report-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 8px;
    }
    .report-location {
      font-weight: 600;
      font-size: 15px;
      color: #2C1A3E;
    }
    .status-badge {
      font-size: 11px;
      padding: 3px 12px;
      border-radius: 20px;
      font-weight: 500;
    }
    .status-pending {
      background: #FEF9E7;
      color: #9A7D0A;
    }
    .status-approved {
      background: #eafaf1;
      color: #1e8449;
    }
    .report-desc {
      font-size: 13px;
      color: #777;
      line-height: 1.6;
      margin-bottom: 8px;
    }
    .report-date {
      font-size: 11px;
      color: #bbb;
    }

    .empty-state {
      text-align: center;
      padding: 40px;
      color: #aaa;
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

  <h2 class="page-title">⚠️ Report Unsafe Area</h2>
  <p class="page-sub">Apne area mein unsafe jagah report karo — doosri mahilaon ki madad karo</p>

  <!-- Report Form -->
  <div class="form-card">
    <h3>📍 New Report Karo</h3>

    <?php if ($success): ?>
      <div class="success-msg"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="error-msg"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="report_area.php">
      <div class="form-group">
        <label>Location / Area ka Naam</label>
        <input type="text" name="location" placeholder="e.g. Shivajinagar Bus Stop, Pune" required>
      </div>
      <div class="form-group">
        <label>Kya hua? Kyun unsafe hai?</label>
        <textarea name="description" placeholder="Yahan kya problem hai? Raat ko andhera hai, gunda element hai, CCTV nahi hai..." required></textarea>
      </div>
      <button type="submit" class="btn">⚠️ Report Karo</button>
    </form>
  </div>

  <!-- Reports List -->
  <h3 class="reports-title">📋 Reported Areas</h3>

  <?php if (mysqli_num_rows($areas) > 0): ?>
    <?php while ($area = mysqli_fetch_assoc($areas)): ?>
      <div class="report-card">
        <div class="report-header">
          <span class="report-location">📍 <?php echo $area['location']; ?></span>
          <span class="status-badge status-<?php echo $area['status']; ?>">
            <?php echo $area['status'] == 'pending' ? '⏳ Under Review' : '✅ Confirmed'; ?>
          </span>
        </div>
        <div class="report-desc"><?php echo $area['description']; ?></div>
        <div class="report-date">🗓️ <?php echo date('d M Y', strtotime($area['date'])); ?></div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <div class="empty-state">
      <div>⚠️</div>
      <p>Koi report nahi mili abhi. Pehli report karo!</p>
    </div>
  <?php endif; ?>

</div>

</body>
</html>
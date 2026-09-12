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
    $title   = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $author  = $_SESSION['user_name'];

    $sql = "INSERT INTO stories (title, content, author, date, status)
            VALUES ('$title', '$content', '$author', NOW(), 'approved')";

    if (mysqli_query($conn, $sql)) {
        $success = "✅ Story shared successfully!";
    } else {
        $error = "❌ Something went wrong!";
    }
}

$stories = mysqli_query($conn, "SELECT * FROM stories WHERE status='approved' ORDER BY date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Stories — HerSafe</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
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

    /* Share Story Form */
    .form-card {
      background: white;
      border-radius: 16px;
      padding: 28px;
      margin-bottom: 36px;
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
    input:focus, textarea:focus {
      border-color: #9B59B6;
      background: white;
    }
    textarea { height: 120px; resize: vertical; }
    .btn {
      background: linear-gradient(135deg, #c0547a, #9B59B6);
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

    /* Story Cards */
    .stories-title {
      font-family: 'Playfair Display', serif;
      font-size: 22px;
      color: #2C1A3E;
      margin-bottom: 20px;
    }

    .story-card {
      background: white;
      border-radius: 16px;
      padding: 28px;
      margin-bottom: 20px;
      box-shadow: 0 2px 12px rgba(108,52,131,0.07);
      border-left: 4px solid #F1948A;
      transition: all 0.2s;
    }
    .story-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 24px rgba(108,52,131,0.13);
    }
    .story-title {
      font-family: 'Playfair Display', serif;
      font-size: 20px;
      color: #2C1A3E;
      margin-bottom: 12px;
    }
    .story-content {
      font-size: 14px;
      color: #555;
      line-height: 1.8;
      margin-bottom: 16px;
    }
    .story-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 12px;
      color: #aaa;
    }
    .story-author {
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .author-avatar {
      width: 28px;
      height: 28px;
      background: linear-gradient(135deg, #c0547a, #9B59B6);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 12px;
      font-weight: 600;
    }

    .empty-state {
      text-align: center;
      padding: 60px 20px;
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

  <h2 class="page-title">💗 Courage Stories</h2>
  <p class="page-sub">Real stories from real women — share yours to inspire others</p>

  <!-- Share Story Form -->
  <div class="form-card">
    <h3>✍️ Share Your Story</h3>

    <?php if ($success): ?>
      <div class="success-msg"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="error-msg"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="stories.php">
      <div class="form-group">
        <label>Story Title</label>
        <input type="text" name="title" placeholder="Give your story a title..." required>
      </div>
      <div class="form-group">
        <label>Your Story</label>
        <textarea name="content" placeholder="Share your experience, how you stayed safe, or how you overcame a difficult situation..." required></textarea>
      </div>
      <button type="submit" class="btn">Share Story 💗</button>
    </form>
  </div>

  <!-- Stories List -->
  <h3 class="stories-title">🌸 Community Stories</h3>

  <?php if (mysqli_num_rows($stories) > 0): ?>
    <?php while ($story = mysqli_fetch_assoc($stories)): ?>
      <div class="story-card">
        <div class="story-title"><?php echo $story['title']; ?></div>
        <div class="story-content"><?php echo nl2br($story['content']); ?></div>
        <div class="story-footer">
          <div class="story-author">
            <div class="author-avatar">
              <?php echo strtoupper(substr($story['author'], 0, 1)); ?>
            </div>
            <span><?php echo $story['author']; ?></span>
          </div>
          <span>🗓️ <?php echo date('d M Y', strtotime($story['date'])); ?></span>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <div class="empty-state">
      <div>💗</div>
      <p>Koi story nahi mili abhi. Pehli story share karo!</p>
    </div>
  <?php endif; ?>

</div>

</body>
</html>
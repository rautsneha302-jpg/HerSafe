<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = "";
$error   = "";

// Profile update karo
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name  = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $age   = mysqli_real_escape_string($conn, $_POST['age']);

    // Password change karna hai?
    if (!empty($_POST['new_password'])) {
        $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $sql = "UPDATE users SET name='$name', phone='$phone', age='$age', password='$new_password' WHERE id=$user_id";
    } else {
        $sql = "UPDATE users SET name='$name', phone='$phone', age='$age' WHERE id=$user_id";
    }

    if (mysqli_query($conn, $sql)) {
        $_SESSION['user_name'] = $name;
        $success = "✅ Profile updated successfully!";
    } else {
        $error = "❌ Error updating profile!";
    }
}

// User data fetch karo
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id"));
$contacts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM emergency_contacts WHERE user_id = $user_id"));

// Stats
$sos_count     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM sos_alerts WHERE user_name = '{$user['name']}'"))['c'];
$stories_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM stories WHERE author = '{$user['name']}'"))['c'];
$routes_count  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM safe_routes WHERE user_id = $user_id"))['c'];
$reports_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM unsafe_areas WHERE reported_by = $user_id"))['c'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile — HerSafe</title>
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

    .container { max-width: 750px; margin: 36px auto; padding: 0 20px; }

    /* Profile Header */
    .profile-header {
      background: linear-gradient(135deg, #6C3483, #c0547a);
      border-radius: 20px;
      padding: 32px;
      display: flex;
      align-items: center;
      gap: 24px;
      margin-bottom: 24px;
      color: white;
    }
    .avatar {
      width: 80px;
      height: 80px;
      background: rgba(255,255,255,0.2);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 36px;
      font-weight: 700;
      border: 3px solid rgba(255,255,255,0.4);
      flex-shrink: 0;
      font-family: 'Playfair Display', serif;
    }
    .profile-info h2 {
      font-family: 'Playfair Display', serif;
      font-size: 24px;
      margin-bottom: 4px;
    }
    .profile-info p { font-size: 14px; opacity: 0.85; }
    .admin-badge {
      background: #F1948A;
      color: white;
      font-size: 11px;
      padding: 3px 12px;
      border-radius: 20px;
      display: inline-block;
      margin-top: 6px;
      font-weight: 500;
    }

    /* Stats */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 12px;
      margin-bottom: 24px;
    }
    .stat-card {
      background: white;
      border-radius: 14px;
      padding: 18px 12px;
      text-align: center;
      box-shadow: 0 2px 12px rgba(108,52,131,0.07);
    }
    .stat-icon { font-size: 24px; margin-bottom: 8px; }
    .stat-num { font-family: 'Playfair Display', serif; font-size: 28px; color: #6C3483; font-weight: 700; }
    .stat-label { font-size: 11px; color: #888; margin-top: 3px; }

    /* Form Card */
    .form-card {
      background: white;
      border-radius: 16px;
      padding: 28px;
      margin-bottom: 20px;
      box-shadow: 0 2px 12px rgba(108,52,131,0.08);
    }
    .form-card h3 {
      font-family: 'Playfair Display', serif;
      font-size: 18px;
      color: #2C1A3E;
      margin-bottom: 20px;
      padding-bottom: 12px;
      border-bottom: 1px solid #f0e8f8;
    }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
    .form-group { margin-bottom: 16px; }
    label { display: block; font-size: 12px; font-weight: 500; color: #666; margin-bottom: 6px; }
    input {
      width: 100%;
      padding: 12px 14px;
      border: 1.5px solid #e8e0f0;
      border-radius: 10px;
      font-size: 14px;
      font-family: 'DM Sans', sans-serif;
      color: #2C1A3E;
      background: #faf8fc;
      outline: none;
      transition: border 0.2s;
    }
    input:focus { border-color: #9B59B6; background: white; }
    input:disabled { background: #f0f0f0; color: #999; cursor: not-allowed; }

    .btn {
      background: linear-gradient(135deg, #6C3483, #9B59B6);
      color: white;
      border: none;
      padding: 13px 28px;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      font-family: 'DM Sans', sans-serif;
      transition: opacity 0.2s;
    }
    .btn:hover { opacity: 0.9; }

    .success-msg { background: #eafaf1; color: #1e8449; padding: 12px 16px; border-radius: 10px; font-size: 13px; margin-bottom: 16px; }
    .error-msg   { background: #fdecea; color: #c0392b; padding: 12px 16px; border-radius: 10px; font-size: 13px; margin-bottom: 16px; }

    /* Contacts Card */
    .contacts-card {
      background: white;
      border-radius: 16px;
      padding: 24px;
      box-shadow: 0 2px 12px rgba(108,52,131,0.07);
      margin-bottom: 20px;
    }
    .contacts-card h3 {
      font-family: 'Playfair Display', serif;
      font-size: 18px;
      color: #2C1A3E;
      margin-bottom: 16px;
      padding-bottom: 12px;
      border-bottom: 1px solid #f0e8f8;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .contacts-card h3 a {
      font-size: 12px;
      color: #9B59B6;
      text-decoration: none;
      font-family: 'DM Sans', sans-serif;
      font-weight: 500;
    }
    .contact-item { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f5f0fb; }
    .contact-item:last-child { border-bottom: none; }
    .c-num { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; color: white; flex-shrink: 0; }
    .c1 { background: #E74C3C; }
    .c2 { background: #E67E22; }
    .c3 { background: #27AE60; }
    .contact-info { flex: 1; }
    .contact-name { font-size: 14px; font-weight: 500; color: #2C1A3E; }
    .contact-phone { font-size: 12px; color: #888; }

    @media (max-width: 600px) {
      .stats-grid { grid-template-columns: repeat(2, 1fr); }
      .form-row { grid-template-columns: 1fr; }
      .profile-header { flex-direction: column; text-align: center; }
    }
  </style>
</head>
<body>

<nav>
  <a href="dashboard.php" class="logo">Her<span>Safe</span></a>
  <a href="dashboard.php" class="back-btn">← Back</a>
</nav>

<div class="container">

  <!-- Profile Header -->
  <div class="profile-header">
    <div class="avatar">
      <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
    </div>
    <div class="profile-info">
      <h2><?php echo $user['name']; ?></h2>
      <p>📧 <?php echo $user['email']; ?></p>
      <p>📞 <?php echo $user['phone'] ?? 'Not added'; ?></p>
      <?php if ($user['is_admin']): ?>
        <span class="admin-badge">👑 Admin</span>
      <?php endif; ?>
    </div>
  </div>

  <!-- Stats -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon">🚨</div>
      <div class="stat-num"><?php echo $sos_count; ?></div>
      <div class="stat-label">SOS Alerts</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">💗</div>
      <div class="stat-num"><?php echo $stories_count; ?></div>
      <div class="stat-label">Stories</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">🗺️</div>
      <div class="stat-num"><?php echo $routes_count; ?></div>
      <div class="stat-label">Routes</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">⚠️</div>
      <div class="stat-num"><?php echo $reports_count; ?></div>
      <div class="stat-label">Reports</div>
    </div>
  </div>

  <!-- Edit Profile Form -->
  <div class="form-card">
    <h3>✏️ Edit Profile</h3>

    <?php if ($success): ?>
      <div class="success-msg"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="error-msg"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="profile.php">
      <div class="form-row">
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="name" value="<?php echo $user['name']; ?>" required>
        </div>
        <div class="form-group">
          <label>Email (change nahi hoga)</label>
          <input type="email" value="<?php echo $user['email']; ?>" disabled>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Phone Number</label>
          <input type="tel" name="phone" value="<?php echo $user['phone'] ?? ''; ?>" placeholder="Phone number">
        </div>
        <div class="form-group">
          <label>Age</label>
          <input type="number" name="age" value="<?php echo $user['age'] ?? ''; ?>" placeholder="Your age">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>New Password (khali chodo agar change nahi karna)</label>
          <input type="password" name="new_password" placeholder="New password">
        </div>
        <div class="form-group">
          <label>Member Since</label>
          <input type="text" value="<?php echo date('d M Y', strtotime($user['created_at'])); ?>" disabled>
        </div>
      </div>
      <button type="submit" class="btn">💾 Save Profile</button>
    </form>
  </div>

  <!-- Emergency Contacts -->
  <div class="contacts-card">
    <h3>
      📞 Emergency Contacts
      <a href="emergency_contacts.php">Edit →</a>
    </h3>
    <?php if ($contacts): ?>
      <?php if (!empty($contacts['contact1_phone'])): ?>
      <div class="contact-item">
        <div class="c-num c1">1</div>
        <div class="contact-info">
          <div class="contact-name"><?php echo $contacts['contact1_name']; ?></div>
          <div class="contact-phone"><?php echo $contacts['contact1_phone']; ?></div>
        </div>
      </div>
      <?php endif; ?>
      <?php if (!empty($contacts['contact2_phone'])): ?>
      <div class="contact-item">
        <div class="c-num c2">2</div>
        <div class="contact-info">
          <div class="contact-name"><?php echo $contacts['contact2_name']; ?></div>
          <div class="contact-phone"><?php echo $contacts['contact2_phone']; ?></div>
        </div>
      </div>
      <?php endif; ?>
      <?php if (!empty($contacts['contact3_phone'])): ?>
      <div class="contact-item">
        <div class="c-num c3">3</div>
        <div class="contact-info">
          <div class="contact-name"><?php echo $contacts['contact3_name']; ?></div>
          <div class="contact-phone"><?php echo $contacts['contact3_phone']; ?></div>
        </div>
      </div>
      <?php endif; ?>
    <?php else: ?>
      <p style="color:#888;font-size:13px;text-align:center;padding:16px">
        Emergency contacts nahi hain. <a href="emergency_contacts.php" style="color:#6C3483">Add karo →</a>
      </p>
    <?php endif; ?>
  </div>

</div>
</body>
</html>
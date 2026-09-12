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

// Contacts save karo
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $c1_name  = mysqli_real_escape_string($conn, $_POST['contact1_name']);
    $c1_phone = mysqli_real_escape_string($conn, $_POST['contact1_phone']);
    $c2_name  = mysqli_real_escape_string($conn, $_POST['contact2_name']);
    $c2_phone = mysqli_real_escape_string($conn, $_POST['contact2_phone']);
    $c3_name  = mysqli_real_escape_string($conn, $_POST['contact3_name']);
    $c3_phone = mysqli_real_escape_string($conn, $_POST['contact3_phone']);

    // Check karo already exists hai ya nahi
    $check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM emergency_contacts WHERE user_id = $user_id"));

    if ($check) {
        // Update karo
        $sql = "UPDATE emergency_contacts SET
                contact1_name='$c1_name', contact1_phone='$c1_phone',
                contact2_name='$c2_name', contact2_phone='$c2_phone',
                contact3_name='$c3_name', contact3_phone='$c3_phone'
                WHERE user_id = $user_id";
    } else {
        // Insert karo
        $sql = "INSERT INTO emergency_contacts
                (user_id, contact1_name, contact1_phone, contact2_name, contact2_phone, contact3_name, contact3_phone)
                VALUES ('$user_id','$c1_name','$c1_phone','$c2_name','$c2_phone','$c3_name','$c3_phone')";
    }

    if (mysqli_query($conn, $sql)) {
        $success = "✅ Emergency contacts saved!";
    } else {
        $error = "❌ Error saving contacts!";
    }
}

// Existing contacts fetch karo
$contacts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM emergency_contacts WHERE user_id = $user_id"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Emergency Contacts — HerSafe</title>
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

    .container { max-width: 700px; margin: 40px auto; padding: 0 20px; }

    .page-title { font-family: 'Playfair Display', serif; font-size: 28px; color: #2C1A3E; margin-bottom: 6px; }
    .page-sub { color: #888; font-size: 14px; margin-bottom: 30px; }

    .info-box {
      background: linear-gradient(135deg, #6C3483, #9B59B6);
      border-radius: 14px;
      padding: 18px 22px;
      color: white;
      font-size: 14px;
      margin-bottom: 28px;
      line-height: 1.7;
    }
    .info-box strong { display: block; font-size: 16px; margin-bottom: 6px; }

    .form-card {
      background: white;
      border-radius: 16px;
      padding: 28px;
      box-shadow: 0 2px 12px rgba(108,52,131,0.08);
      margin-bottom: 24px;
    }
    .contact-section {
      border: 1.5px solid #f0e8f8;
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 20px;
    }
    .contact-section:last-of-type { margin-bottom: 0; }

    .contact-header {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 16px;
    }
    .contact-num {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 14px;
      color: white;
    }
    .num-1 { background: #E74C3C; }
    .num-2 { background: #E67E22; }
    .num-3 { background: #27AE60; }

    .contact-header span { font-weight: 500; color: #2C1A3E; font-size: 15px; }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .form-group { margin-bottom: 0; }
    label { display: block; font-size: 12px; font-weight: 500; color: #666; margin-bottom: 6px; }
    input {
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
    input:focus { border-color: #9B59B6; background: white; }

    .btn {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, #E74C3C, #C0392B);
      color: white;
      border: none;
      border-radius: 12px;
      font-size: 15px;
      font-weight: 500;
      cursor: pointer;
      font-family: 'DM Sans', sans-serif;
      margin-top: 20px;
      transition: opacity 0.2s;
    }
    .btn:hover { opacity: 0.9; }

    .success-msg { background: #eafaf1; color: #1e8449; padding: 12px 16px; border-radius: 10px; font-size: 13px; margin-bottom: 16px; text-align: center; }
    .error-msg   { background: #fdecea; color: #c0392b; padding: 12px 16px; border-radius: 10px; font-size: 13px; margin-bottom: 16px; text-align: center; }

    /* How it works */
    .how-card {
      background: white;
      border-radius: 16px;
      padding: 24px;
      box-shadow: 0 2px 12px rgba(108,52,131,0.07);
    }
    .how-card h3 { font-family: 'Playfair Display', serif; font-size: 18px; color: #2C1A3E; margin-bottom: 16px; }
    .step {
      display: flex;
      gap: 12px;
      align-items: flex-start;
      padding: 10px 0;
      border-bottom: 1px solid #f5f0fb;
      font-size: 13px;
      color: #555;
      line-height: 1.5;
    }
    .step:last-child { border-bottom: none; }
    .step-icon { font-size: 20px; flex-shrink: 0; }
  </style>
</head>
<body>

<nav>
  <a href="dashboard.php" class="logo">Her<span>Safe</span></a>
  <a href="dashboard.php" class="back-btn">← Back</a>
</nav>

<div class="container">

  <h2 class="page-title">📞 Emergency Contacts</h2>
  <p class="page-sub">3 trusted contacts save karo — SOS mein automatically call jayega</p>

  <div class="info-box">
    <strong>🚨 Ye kaise kaam karta hai?</strong>
    SOS button dabate hi pehle Contact 1 pe call jayega. Agar received nahi hua toh Contact 2, phir Contact 3. Aur tumhari live location automatically share hogi!
  </div>

  <div class="form-card">

    <?php if ($success): ?>
      <div class="success-msg"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="error-msg"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="emergency_contacts.php">

      <!-- Contact 1 -->
      <div class="contact-section">
        <div class="contact-header">
          <div class="contact-num num-1">1</div>
          <span>Primary Contact — Pehle isko call jayega</span>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Name</label>
            <input type="text" name="contact1_name" placeholder="e.g. Aai, Papa" value="<?php echo $contacts['contact1_name'] ?? ''; ?>" required>
          </div>
          <div class="form-group">
            <label>Phone Number</label>
            <input type="tel" name="contact1_phone" placeholder="e.g. 9876543210" value="<?php echo $contacts['contact1_phone'] ?? ''; ?>" required>
          </div>
        </div>
      </div>

      <!-- Contact 2 -->
      <div class="contact-section">
        <div class="contact-header">
          <div class="contact-num num-2">2</div>
          <span>Second Contact — Agar Contact 1 nahi utha toh</span>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Name</label>
            <input type="text" name="contact2_name" placeholder="e.g. Bhai, Didi" value="<?php echo $contacts['contact2_name'] ?? ''; ?>">
          </div>
          <div class="form-group">
            <label>Phone Number</label>
            <input type="tel" name="contact2_phone" placeholder="e.g. 9876543210" value="<?php echo $contacts['contact2_phone'] ?? ''; ?>">
          </div>
        </div>
      </div>

      <!-- Contact 3 -->
      <div class="contact-section">
        <div class="contact-header">
          <div class="contact-num num-3">3</div>
          <span>Third Contact — Last backup</span>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Name</label>
            <input type="text" name="contact3_name" placeholder="e.g. Friend, Neighbor" value="<?php echo $contacts['contact3_name'] ?? ''; ?>">
          </div>
          <div class="form-group">
            <label>Phone Number</label>
            <input type="tel" name="contact3_phone" placeholder="e.g. 9876543210" value="<?php echo $contacts['contact3_phone'] ?? ''; ?>">
          </div>
        </div>
      </div>

      <button type="submit" class="btn">💾 Save Emergency Contacts</button>

    </form>
  </div>

  <!-- How it works -->
  <div class="how-card">
    <h3>⚡ SOS mein kya hoga?</h3>
    <div class="step"><span class="step-icon">1️⃣</span><span>Tum SOS button dabao — tumhari live location detect hogi.</span></div>
    <div class="step"><span class="step-icon">2️⃣</span><span>Automatically Contact 1 pe call jayega.</span></div>
    <div class="step"><span class="step-icon">3️⃣</span><span>Agar 30 sec mein received nahi hua — Contact 2 pe call.</span></div>
    <div class="step"><span class="step-icon">4️⃣</span><span>Agar wo bhi nahi utha — Contact 3 pe call.</span></div>
    <div class="step"><span class="step-icon">5️⃣</span><span>Jab wo call back karenge — tumhari live Google Maps location unhe milegi!</span></div>
  </div>

</div>

</body>
</html>
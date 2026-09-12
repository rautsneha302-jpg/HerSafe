<?php
session_start();
include 'includes/db.php';

// Language setup
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = $_SESSION['lang'] ?? 'en';

$text = [
    'en' => [
        'subtitle'  => 'Welcome back 🌸 Login to your account',
        'email'     => 'Email Address',
        'password'  => 'Password',
        'btn'       => 'Login ✦',
        'register'  => "Don't have an account? Register here",
        'wrong_pass'=> '❌ Wrong password!',
        'not_found' => '❌ Email not found!',
    ],
    'hi' => [
        'subtitle'  => 'वापस स्वागत है 🌸 अपने खाते में लॉगिन करें',
        'email'     => 'ईमेल पता',
        'password'  => 'पासवर्ड',
        'btn'       => 'लॉगिन करें ✦',
        'register'  => 'खाता नहीं है? यहाँ रजिस्टर करें',
        'wrong_pass'=> '❌ गलत पासवर्ड!',
        'not_found' => '❌ ईमेल नहीं मिला!',
    ],
    'mr' => [
        'subtitle'  => 'परत स्वागत आहे 🌸 आपल्या खात्यात लॉगिन करा',
        'email'     => 'ईमेल पत्ता',
        'password'  => 'पासवर्ड',
        'btn'       => 'लॉगिन करा ✦',
        'register'  => 'खाते नाही? येथे नोंदणी करा',
        'wrong_pass'=> '❌ चुकीचा पासवर्ड!',
        'not_found' => '❌ ईमेल सापडला नाही!',
    ],
];
$t = $text[$lang];

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql    = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['email']     = $user['email'];
            $_SESSION['is_admin']  = $user['is_admin'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = $t['wrong_pass'];
        }
    } else {
        $error = $t['not_found'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — HerSafe</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'DM Sans', sans-serif;
      min-height: 100vh;
      background: #f5f0fb;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 30px 16px;
    }

    /* Language Buttons */
    .lang-bar {
      position: fixed;
      top: 16px;
      right: 20px;
      display: flex;
      gap: 6px;
      z-index: 100;
    }
    .lang-btn {
      padding: 6px 14px;
      border-radius: 20px;
      text-decoration: none;
      font-size: 12px;
      font-weight: 600;
      transition: all 0.2s;
    }
    .lang-btn.active { background: #6C3483; color: white; }
    .lang-btn.inactive { background: #e8d8f8; color: #6C3483; }
    .lang-btn:hover { background: #6C3483; color: white; }

    .card {
      background: white;
      border-radius: 20px;
      padding: 48px 44px;
      width: 100%;
      max-width: 460px;
      box-shadow: 0 8px 40px rgba(108,52,131,0.10);
    }
    .logo {
      text-align: center;
      margin-bottom: 6px;
    }
    .logo h1 {
      font-family: 'Playfair Display', serif;
      font-size: 32px;
      color: #2C1A3E;
    }
    .logo h1 span { color: #c0547a; }
    .subtitle {
      text-align: center;
      color: #888;
      font-size: 14px;
      margin-bottom: 32px;
    }
    .error-msg {
      background: #fdecea;
      color: #c0392b;
      padding: 10px 16px;
      border-radius: 10px;
      font-size: 13px;
      margin-bottom: 20px;
      text-align: center;
    }
    .form-group { margin-bottom: 20px; }
    label {
      display: block;
      font-size: 13px;
      font-weight: 500;
      color: #2C1A3E;
      margin-bottom: 8px;
    }
    input {
      width: 100%;
      padding: 13px 16px;
      border: 1.5px solid #e8e0f0;
      border-radius: 12px;
      font-size: 14px;
      font-family: 'DM Sans', sans-serif;
      color: #2C1A3E;
      background: #faf8fc;
      outline: none;
      transition: border 0.2s;
    }
    input:focus { border-color: #9B59B6; background: white; }
    input::placeholder { color: #bbb; }
    .btn {
      width: 100%;
      padding: 15px;
      background: linear-gradient(135deg, #c0547a, #9B59B6);
      color: white;
      border: none;
      border-radius: 12px;
      font-size: 15px;
      font-weight: 500;
      font-family: 'DM Sans', sans-serif;
      cursor: pointer;
      margin-top: 8px;
      transition: opacity 0.2s, transform 0.2s;
    }
    .btn:hover { opacity: 0.92; transform: translateY(-1px); }
    .bottom-link {
      text-align: center;
      margin-top: 22px;
      font-size: 13px;
      color: #888;
    }
    .bottom-link a { color: #c0547a; text-decoration: none; font-weight: 500; }
    .back-link {
      text-align: center;
      margin-bottom: 20px;
    }
    .back-link a {
      color: #9B59B6;
      text-decoration: none;
      font-size: 13px;
      font-weight: 500;
    }
  </style>
</head>
<body>

<!-- Language Buttons -->
<div class="lang-bar">
  <a href="?lang=en" class="lang-btn <?php echo $lang=='en'?'active':'inactive'; ?>">EN</a>
  <a href="?lang=hi" class="lang-btn <?php echo $lang=='hi'?'active':'inactive'; ?>">हि</a>
  <a href="?lang=mr" class="lang-btn <?php echo $lang=='mr'?'active':'inactive'; ?>">म</a>
</div>

<!-- Language Buttons -->
<div style="position:fixed;top:16px;right:20px;display:flex;gap:6px;z-index:100">
</div>

<div class="card">
  <div class="back-link">
    <a href="index.php">← Back to Home</a>
  </div>

  <div class="logo">
    <h1>HerSafe<span>.</span></h1>
  </div>
  <p class="subtitle"><?php echo $t['subtitle']; ?></p>

  <?php if ($error): ?>
    <div class="error-msg"><?php echo $error; ?></div>
  <?php endif; ?>

  <form method="POST" action="login.php">
    <div class="form-group">
      <label><?php echo $t['email']; ?></label>
      <input type="email" name="email" placeholder="Enter your email" required>
    </div>
    <div class="form-group">
      <label><?php echo $t['password']; ?></label>
      <input type="password" name="password" placeholder="Enter your password" required>
    </div>
    <button type="submit" class="btn"><?php echo $t['btn']; ?></button>
  </form>

  <p class="bottom-link">
    <?php
      $reg_parts = explode('?', $t['register']);
      echo '<a href="register.php">' . $t['register'] . '</a>';
    ?>
  </p>
</div>

</body>
</html>
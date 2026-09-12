<?php
session_start();
include 'includes/db.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    // Check passwords match
    if ($password !== $confirm) {
        $error = "❌ Passwords do not match!";
    } else {
        // Check if email already exists
        $check = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = "❌ Email already registered!";
        } else {
            // Hash password and save
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$hashed')";
            if (mysqli_query($conn, $sql)) {
                $success = "✅ Account created! You can now login.";
            } else {
                $error = "❌ Something went wrong. Try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register — HerSafe</title>
  <style>
    body {
      background-color: #f3edf9;
      font-family: 'Poppins', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .container {
      background-color: #fff;
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
      text-align: center;
      width: 350px;
    }
    h2 { color: #4b2a7b; margin-bottom: 10px; }
    p { color: #777; margin-bottom: 25px; }
    input {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 14px;
    }
    .btn {
      background: linear-gradient(to right, #d16ba5, #c777b9, #ba83ca);
      color: white;
      border: none;
      padding: 10px;
      width: 100%;
      border-radius: 8px;
      cursor: pointer;
      font-size: 16px;
      transition: 0.3s;
    }
    .btn:hover { opacity: 0.9; }
    a { color: #a64ac9; text-decoration: none; }
    a:hover { text-decoration: underline; }
    .back-link { display: block; margin-bottom: 15px; color: #a64ac9; font-size: 14px; }
    .error { background: #fdecea; color: #c0392b; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 13px; }
    .success { background: #eafaf1; color: #1e8449; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 13px; }
  </style>
</head>
<body>
  <div class="container">
    <a href="index.php" class="back-link">← Back to Home</a>
    <h2>HerSafe.</h2>
    <p>Join us 🌸 Create your account</p>

    <?php if ($error): ?>
      <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form action="register.php" method="POST">
      <input type="text" name="fullname" placeholder="Full Name" required />
      <input type="email" name="email" placeholder="Email Address" required />
      <input type="password" name="password" placeholder="Password" required />
      <input type="password" name="confirm_password" placeholder="Confirm Password" required />
      <button type="submit" class="btn">Register ✦</button>
    </form>

    <p>Already have an account? <a href="login.php">Login here</a></p>
  </div>
</body>
</html>
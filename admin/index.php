<?php
session_start();
include '../includes/db.php';

// Admin check
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php");
    exit();
}

// Stats
$total_users   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users"))['c'];
$total_reports = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM unsafe_areas"))['c'];
$total_alerts  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM sos_alerts"))['c'];
$total_stories = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM stories"))['c'];
$pending       = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM unsafe_areas WHERE status='pending'"))['c'];

// Story approve/delete
if (isset($_GET['approve_story'])) {
    mysqli_query($conn, "UPDATE stories SET status='approved' WHERE id=" . (int)$_GET['approve_story']);
    header("Location: index.php"); exit();
}
if (isset($_GET['delete_story'])) {
    mysqli_query($conn, "DELETE FROM stories WHERE id=" . (int)$_GET['delete_story']);
    header("Location: index.php"); exit();
}
if (isset($_GET['approve_report'])) {
    mysqli_query($conn, "UPDATE unsafe_areas SET status='approved' WHERE id=" . (int)$_GET['approve_report']);
    header("Location: index.php"); exit();
}
if (isset($_GET['delete_report'])) {
    mysqli_query($conn, "DELETE FROM unsafe_areas WHERE id=" . (int)$_GET['delete_report']);
    header("Location: index.php"); exit();
}

$users   = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
$reports = mysqli_query($conn, "SELECT * FROM unsafe_areas ORDER BY date DESC LIMIT 10");
$alerts  = mysqli_query($conn, "SELECT * FROM sos_alerts ORDER BY timestamp DESC LIMIT 10");
$stories = mysqli_query($conn, "SELECT * FROM stories ORDER BY date DESC LIMIT 10");
?>

<!DOCTYPE html>f
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel — HerSafe</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DM Sans', sans-serif; background: #f5f0fb; min-height: 100vh; }
    nav {
      background: #2C1A3E;
      padding: 0 40px;
      height: 65px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .logo { font-family: 'Playfair Display', serif; font-size: 22px; color: white; }
    .logo span { color: #F1948A; }
    .nav-right { display: flex; align-items: center; gap: 14px; }
    .nav-right span { color: rgba(255,255,255,0.7); font-size: 13px; }
    .logout { background: rgba(255,255,255,0.1); color: white; padding: 7px 16px; border-radius: 20px; text-decoration: none; font-size: 13px; }
    .container { max-width: 1100px; margin: 36px auto; padding: 0 20px; }
    .page-title { font-family: 'Playfair Display', serif; font-size: 26px; color: #2C1A3E; margin-bottom: 24px; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 16px; margin-bottom: 36px; }
    .stat-card { background: white; border-radius: 14px; padding: 22px; text-align: center; box-shadow: 0 2px 12px rgba(108,52,131,0.08); }
    .stat-icon { font-size: 32px; margin-bottom: 10px; }
    .stat-num { font-family: 'Playfair Display', serif; font-size: 36px; color: #6C3483; font-weight: 700; }
    .stat-label { font-size: 12px; color: #888; margin-top: 4px; }
    .section { background: white; border-radius: 16px; padding: 24px; margin-bottom: 24px; box-shadow: 0 2px 12px rgba(108,52,131,0.07); }
    .section h3 { font-family: 'Playfair Display', serif; font-size: 18px; color: #2C1A3E; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid #f0e8f8; }
    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    th { text-align: left; padding: 10px 12px; background: #f8f4fc; color: #6C3483; font-weight: 500; font-size: 11px; letter-spacing: 0.5px; text-transform: uppercase; }
    td { padding: 10px 12px; border-bottom: 1px solid #f5f0fb; color: #444; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: #faf8fc; }
    .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 500; }
    .badge-pending  { background: #FEF9E7; color: #9A7D0A; }
    .badge-approved { background: #eafaf1; color: #1e8449; }
    .badge-admin    { background: #F4ECF7; color: #6C3483; }
    .badge-user     { background: #EBF5FB; color: #2980B9; }
    .action-btns { display: flex; gap: 6px; }
    .btn-approve { background: #eafaf1; color: #1e8449; border: none; padding: 5px 12px; border-radius: 8px; font-size: 11px; cursor: pointer; text-decoration: none; }
    .btn-delete  { background: #fdecea; color: #c0392b; border: none; padding: 5px 12px; border-radius: 8px; font-size: 11px; cursor: pointer; text-decoration: none; }
    .btn-approve:hover { background: #27AE60; color: white; }
    .btn-delete:hover  { background: #E74C3C; color: white; }
    .empty { text-align: center; padding: 24px; color: #aaa; font-size: 13px; }
  </style>
</head>
<body>

<nav>
  <div class="logo">Her<span>Safe</span> — Admin</div>
  <div class="nav-right">
    <span>👑 <?php echo $_SESSION['user_name']; ?></span>
    <a href="../logout.php" class="logout">Logout</a>
  </div>
</nav>

<div class="container">
  <h2 class="page-title">📊 Admin Dashboard</h2>

  <div class="stats-grid">
    <div class="stat-card"><div class="stat-icon">👥</div><div class="stat-num"><?php echo $total_users; ?></div><div class="stat-label">Total Users</div></div>
    <div class="stat-card"><div class="stat-icon">⚠️</div><div class="stat-num"><?php echo $total_reports; ?></div><div class="stat-label">Area Reports</div></div>
    <div class="stat-card"><div class="stat-icon">🚨</div><div class="stat-num"><?php echo $total_alerts; ?></div><div class="stat-label">SOS Alerts</div></div>
    <div class="stat-card"><div class="stat-icon">💗</div><div class="stat-num"><?php echo $total_stories; ?></div><div class="stat-label">Stories</div></div>
    <div class="stat-card"><div class="stat-icon">⏳</div><div class="stat-num"><?php echo $pending; ?></div><div class="stat-label">Pending Reports</div></div>
  </div>

  <!-- Users -->
  <div class="section">
    <h3>👥 All Users</h3>
    <table>
      <tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th></tr>
      <?php while ($u = mysqli_fetch_assoc($users)): ?>
      <tr>
        <td><?php echo $u['id']; ?></td>
        <td><?php echo $u['name']; ?></td>
        <td><?php echo $u['email']; ?></td>
        <td><?php echo $u['phone'] ?? '-'; ?></td>
        <td><span class="badge <?php echo $u['is_admin'] ? 'badge-admin' : 'badge-user'; ?>"><?php echo $u['is_admin'] ? '👑 Admin' : '👤 User'; ?></span></td>
      </tr>
      <?php endwhile; ?>
    </table>
  </div>

  <!-- Reports -->
  <div class="section">
    <h3>⚠️ Reported Unsafe Areas</h3>
    <?php if (mysqli_num_rows($reports) > 0): ?>
    <table>
      <tr><th>#</th><th>Location</th><th>Description</th><th>Status</th><th>Action</th></tr>
      <?php while ($r = mysqli_fetch_assoc($reports)): ?>
      <tr>
        <td><?php echo $r['id']; ?></td>
        <td><?php echo $r['location']; ?></td>
        <td><?php echo substr($r['description'], 0, 50) . '...'; ?></td>
        <td><span class="badge badge-<?php echo $r['status']; ?>"><?php echo $r['status']; ?></span></td>
        <td>
          <div class="action-btns">
            <?php if ($r['status'] == 'pending'): ?>
            <a href="?approve_report=<?php echo $r['id']; ?>" class="btn-approve">✅ Approve</a>
            <?php endif; ?>
            <a href="?delete_report=<?php echo $r['id']; ?>" class="btn-delete" onclick="return confirm('Delete?')">🗑️ Delete</a>
          </div>
        </td>
      </tr>
      <?php endwhile; ?>
    </table>
    <?php else: ?><div class="empty">Koi report nahi</div><?php endif; ?>
  </div>

  <!-- SOS Alerts -->
  <div class="section">
    <h3>🚨 SOS Alerts</h3>
    <?php if (mysqli_num_rows($alerts) > 0): ?>
    <table>
      <tr><th>#</th><th>User</th><th>Phone</th><th>Message</th><th>Time</th></tr>
      <?php while ($a = mysqli_fetch_assoc($alerts)): ?>
      <tr>
        <td><?php echo $a['id']; ?></td>
        <td><?php echo $a['user_name']; ?></td>
        <td><?php echo $a['user_phone'] ?? '-'; ?></td>
        <td><?php echo $a['message']; ?></td>
        <td><?php echo date('d M Y H:i', strtotime($a['timestamp'])); ?></td>
      </tr>
      <?php endwhile; ?>
    </table>
    <?php else: ?><div class="empty">Koi alert nahi</div><?php endif; ?>
  </div>

  <!-- Stories -->
  <div class="section">
    <h3>💗 Stories Manage karo</h3>
    <?php if (mysqli_num_rows($stories) > 0): ?>
    <table>
      <tr><th>#</th><th>Title</th><th>Author</th><th>Status</th><th>Action</th></tr>
      <?php while ($s = mysqli_fetch_assoc($stories)): ?>
      <tr>
        <td><?php echo $s['id']; ?></td>
        <td><?php echo $s['title']; ?></td>
        <td><?php echo $s['author']; ?></td>
        <td><span class="badge badge-<?php echo $s['status']; ?>"><?php echo $s['status']; ?></span></td>
        <td>
          <div class="action-btns">
            <?php if ($s['status'] != 'approved'): ?>
            <a href="?approve_story=<?php echo $s['id']; ?>" class="btn-approve">✅ Approve</a>
            <?php endif; ?>
            <a href="?delete_story=<?php echo $s['id']; ?>" class="btn-delete" onclick="return confirm('Delete?')">🗑️ Delete</a>
          </div>
        </td>
      </tr>
      <?php endwhile; ?>
    </table>
    <?php else: ?><div class="empty">Koi story nahi</div><?php endif; ?>
  </div>

</div>
</body>
</html>
<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id   = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Emergency contacts fetch karo
$contacts = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM emergency_contacts WHERE user_id = $user_id"));

// Journey start karo
if (isset($_POST['start_journey'])) {
    $lat  = mysqli_real_escape_string($conn, $_POST['lat']);
    $lng  = mysqli_real_escape_string($conn, $_POST['lng']);
    $mode = mysqli_real_escape_string($conn, $_POST['mode'] ?? 'walk');
    $c1   = $contacts['contact1_phone'] ?? '';
    $c2   = $contacts['contact2_phone'] ?? '';
    $c3   = $contacts['contact3_phone'] ?? '';

    mysqli_query($conn, "UPDATE journey_alerts SET status='ended' WHERE user_id=$user_id AND status='active'");

    $sql = "INSERT INTO journey_alerts (user_id, user_name, start_location, last_location, last_lat, last_lng, status, contact1_phone, contact2_phone, contact3_phone)
            VALUES ('$user_id', '$user_name', 'Lat:$lat,Lng:$lng', 'Lat:$lat,Lng:$lng', '$lat', '$lng', 'active', '$c1', '$c2', '$c3')";
    mysqli_query($conn, $sql);
    echo json_encode(['status' => 'started']);
    exit();
}

// Location update
if (isset($_POST['update_location'])) {
    $lat = mysqli_real_escape_string($conn, $_POST['lat']);
    $lng = mysqli_real_escape_string($conn, $_POST['lng']);
    mysqli_query($conn, "UPDATE journey_alerts SET last_lat='$lat', last_lng='$lng', last_location='Lat:$lat,Lng:$lng', last_update=NOW() WHERE user_id=$user_id AND status='active'");
    echo json_encode(['status' => 'updated']);
    exit();
}

// Nearby active users fetch
if (isset($_POST['get_nearby'])) {
    $lat = floatval($_POST['lat']);
    $lng = floatval($_POST['lng']);
    $nearby = mysqli_query($conn, "SELECT user_name, last_lat, last_lng FROM journey_alerts WHERE status='active' AND user_id != $user_id AND last_lat != '' ORDER BY last_update DESC LIMIT 10");
    $users = [];
    while ($row = mysqli_fetch_assoc($nearby)) {
        $users[] = $row;
    }
    echo json_encode($users);
    exit();
}

// I'm Safe
if (isset($_POST['end_journey'])) {
    mysqli_query($conn, "UPDATE journey_alerts SET status='safe' WHERE user_id=$user_id AND status='active'");
    echo json_encode(['status' => 'ended']);
    exit();
}

// SOS trigger
if (isset($_POST['sos_trigger'])) {
    $lat = mysqli_real_escape_string($conn, $_POST['lat']);
    $lng = mysqli_real_escape_string($conn, $_POST['lng']);
    mysqli_query($conn, "UPDATE journey_alerts SET status='danger', last_lat='$lat', last_lng='$lng' WHERE user_id=$user_id AND status='active'");
    $msg = "DANGER! Night Journey SOS Alert! Last Location: https://maps.google.com/?q=$lat,$lng";
    $msg = mysqli_real_escape_string($conn, $msg);
    mysqli_query($conn, "INSERT INTO sos_alerts (user_name, user_phone, message, timestamp) VALUES ('$user_name', '', '$msg', NOW())");
    echo json_encode(['status' => 'sos_sent']);
    exit();
}

$active_journey = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM journey_alerts WHERE user_id=$user_id AND status='active' ORDER BY id DESC LIMIT 1"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Safe Journey — HerSafe</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DM Sans', sans-serif; background: #f5f0fb; min-height: 100vh; }
    nav { background: #6C3483; padding: 0 40px; height: 65px; display: flex; align-items: center; justify-content: space-between; }
    .logo { font-family: 'Playfair Display', serif; font-size: 24px; color: white; text-decoration: none; }
    .logo span { color: #F1948A; }
    .back-btn { background: rgba(255,255,255,0.15); color: white; padding: 8px 18px; border-radius: 20px; text-decoration: none; font-size: 13px; }
    .container { max-width: 700px; margin: 30px auto; padding: 0 20px; }
    .page-title { font-family: 'Playfair Display', serif; font-size: 26px; color: #2C1A3E; margin-bottom: 6px; }
    .page-sub { color: #888; font-size: 14px; margin-bottom: 24px; }

    /* Travel Mode */
    .mode-card { background: white; border-radius: 16px; padding: 20px 24px; margin-bottom: 20px; box-shadow: 0 2px 12px rgba(108,52,131,0.08); }
    .mode-card h4 { font-size: 14px; font-weight: 600; color: #2C1A3E; margin-bottom: 14px; }
    .mode-grid { display: flex; gap: 10px; flex-wrap: wrap; }
    .mode-btn {
      padding: 10px 18px;
      border: 2px solid #e8e0f0;
      border-radius: 25px;
      background: #faf8fc;
      color: #6C3483;
      font-size: 13px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s;
      font-family: 'DM Sans', sans-serif;
    }
    .mode-btn.active { background: #6C3483; color: white; border-color: #6C3483; }
    .mode-btn:hover { background: #6C3483; color: white; border-color: #6C3483; }

    /* Map */
    .map-card { background: white; border-radius: 16px; overflow: hidden; margin-bottom: 20px; box-shadow: 0 2px 12px rgba(108,52,131,0.08); }
    .map-card-header { padding: 16px 20px; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid #f0e8f8; }
    .map-card-header h4 { font-size: 14px; font-weight: 600; color: #2C1A3E; }
    #journeyMap { width: 100%; height: 280px; }
    .map-legend { padding: 10px 20px; display: flex; gap: 16px; font-size: 12px; color: #888; border-top: 1px solid #f0e8f8; flex-wrap: wrap; }
    .legend-item { display: flex; align-items: center; gap: 6px; }
    .legend-dot { width: 10px; height: 10px; border-radius: 50%; }

    /* Nearby users */
    .nearby-card { background: white; border-radius: 16px; padding: 18px 20px; margin-bottom: 20px; box-shadow: 0 2px 12px rgba(108,52,131,0.08); }
    .nearby-card h4 { font-size: 14px; font-weight: 600; color: #2C1A3E; margin-bottom: 12px; }
    .nearby-item { display: flex; align-items: center; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f5f0fb; font-size: 13px; }
    .nearby-item:last-child { border-bottom: none; }
    .nearby-name { display: flex; align-items: center; gap: 8px; }
    .nearby-avatar { width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg, #c0547a, #9B59B6); display: flex; align-items: center; justify-content: center; color: white; font-size: 11px; font-weight: 600; }
    .nearby-dist { color: #6C3483; font-weight: 600; font-size: 12px; }
    .no-nearby { color: #aaa; font-size: 13px; text-align: center; padding: 10px 0; }

    /* Status */
    .status-card { background: white; border-radius: 16px; padding: 24px; margin-bottom: 20px; box-shadow: 0 2px 12px rgba(108,52,131,0.08); text-align: center; }
    .status-indicator { width: 16px; height: 16px; border-radius: 50%; display: inline-block; margin-right: 8px; animation: blink 1s infinite; }
    .status-active { background: #27AE60; }
    .status-inactive { background: #aaa; animation: none; }
    @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }
    .location-box { background: #f5f0fb; border-radius: 12px; padding: 14px 18px; margin: 14px 0; font-size: 13px; color: #555; text-align: left; }
    .location-box strong { color: #2C1A3E; display: block; margin-bottom: 4px; }

    /* Buttons */
    .btn-start { width: 100%; padding: 16px; background: linear-gradient(135deg, #27AE60, #1E8449); color: white; border: none; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer; font-family: 'DM Sans', sans-serif; margin-bottom: 12px; }
    .btn-safe { width: 100%; padding: 16px; background: linear-gradient(135deg, #2980B9, #1A6A9A); color: white; border: none; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer; font-family: 'DM Sans', sans-serif; margin-bottom: 12px; }
    .btn-sos { width: 100%; padding: 18px; background: linear-gradient(135deg, #E74C3C, #C0392B); color: white; border: none; border-radius: 12px; font-size: 18px; font-weight: 700; cursor: pointer; font-family: 'DM Sans', sans-serif; letter-spacing: 1px; animation: sos-pulse 1.5s infinite; box-shadow: 0 4px 20px rgba(231,76,60,0.4); }
    @keyframes sos-pulse { 0%, 100% { box-shadow: 0 4px 20px rgba(231,76,60,0.4), 0 0 0 0 rgba(231,76,60,0.3); } 50% { box-shadow: 0 4px 20px rgba(231,76,60,0.4), 0 0 0 16px rgba(231,76,60,0); } }

    .alert-box { display: none; border-radius: 14px; padding: 20px; margin-bottom: 16px; text-align: center; font-size: 14px; font-weight: 500; }
    .alert-success { background: #eafaf1; color: #1e8449; border: 1px solid #a9dfbf; }
    .alert-danger  { background: #fdecea; color: #c0392b; border: 1px solid #f5b7b1; }

    .contacts-preview { background: white; border-radius: 14px; padding: 18px; margin-bottom: 20px; box-shadow: 0 2px 12px rgba(108,52,131,0.07); }
    .contacts-preview h4 { font-size: 14px; color: #2C1A3E; margin-bottom: 12px; font-weight: 600; }
    .contact-row { display: flex; align-items: center; gap: 10px; padding: 7px 0; font-size: 13px; color: #555; border-bottom: 1px solid #f5f0fb; }
    .contact-row:last-child { border-bottom: none; }
    .c-badge { width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: white; flex-shrink: 0; }
    .cb1 { background: #E74C3C; } .cb2 { background: #E67E22; } .cb3 { background: #27AE60; }

    .scenario-box { background: linear-gradient(135deg, #2C1A3E, #6C3483); border-radius: 16px; padding: 22px 24px; color: white; margin-bottom: 24px; line-height: 1.7; font-size: 14px; }
    .scenario-box h3 { font-family: 'Playfair Display', serif; font-size: 18px; margin-bottom: 10px; color: #F1948A; }

    .how-card { background: white; border-radius: 16px; padding: 24px; box-shadow: 0 2px 12px rgba(108,52,131,0.07); margin-bottom: 20px; }
    .how-card h3 { font-family: 'Playfair Display', serif; font-size: 17px; color: #2C1A3E; margin-bottom: 16px; }
    .step { display: flex; gap: 12px; align-items: flex-start; padding: 10px 0; border-bottom: 1px solid #f5f0fb; font-size: 13px; color: #555; line-height: 1.6; }
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

  <h2 class="page-title">🌙 Safe Journey Mode</h2>
  <p class="page-sub">Akele safar karte waqt apni safety ensure karo</p>

  <!-- Scenario -->
  <div class="scenario-box">
    <h3>🚨 Ye kab use karo?</h3>
    Raat ko akele safar kar rahi ho — cab, auto, ya walk pe — koi anjaan shakhs saath aaya — turant <strong>Silent SOS</strong> trigger karo! Tumhari <strong>live location</strong> automatically family aur contacts tak pahunch jayegi!
  </div>

  <!-- Travel Mode -->
  <div class="mode-card">
    <h4>🚗 Safar ka tarika chuno:</h4>
    <div class="mode-grid">
      <button class="mode-btn active" onclick="selectMode(this, 'walk')">🚶 Walk</button>
      <button class="mode-btn" onclick="selectMode(this, 'cab')">🚖 Cab</button>
      <button class="mode-btn" onclick="selectMode(this, 'auto')">🛺 Auto</button>
      <button class="mode-btn" onclick="selectMode(this, 'bus')">🚌 Bus</button>
      <button class="mode-btn" onclick="selectMode(this, 'bike')">🏍️ Bike</button>
      <button class="mode-btn" onclick="selectMode(this, 'train')">🚂 Train</button>
    </div>
  </div>

  <!-- Map -->
  <div class="map-card">
    <div class="map-card-header">
      <span>🗺️</span>
      <h4>Live Map — Tumhari location aur nearby users</h4>
    </div>
    <div id="journeyMap"></div>
    <div class="map-legend">
      <div class="legend-item"><div class="legend-dot" style="background:#c0547a"></div> Tum</div>
      <div class="legend-item"><div class="legend-dot" style="background:#27AE60"></div> Nearby Active Users</div>
    </div>
  </div>

  <!-- Nearby Users -->
  <div class="nearby-card">
    <h4>👥 Nearby Active HerSafe Users:</h4>
    <div id="nearbyList"><p class="no-nearby">📡 Location detect hone ke baad nearby users dikhenge...</p></div>
  </div>

  <!-- Contacts -->
  <?php if ($contacts): ?>
  <div class="contacts-preview">
    <h4>📞 Alert jayega in logon ko:</h4>
    <?php if (!empty($contacts['contact1_phone'])): ?>
    <div class="contact-row"><div class="c-badge cb1">1</div><span><strong><?php echo htmlspecialchars($contacts['contact1_name']); ?></strong> — <?php echo htmlspecialchars($contacts['contact1_phone']); ?></span></div>
    <?php endif; ?>
    <?php if (!empty($contacts['contact2_phone'])): ?>
    <div class="contact-row"><div class="c-badge cb2">2</div><span><strong><?php echo htmlspecialchars($contacts['contact2_name']); ?></strong> — <?php echo htmlspecialchars($contacts['contact2_phone']); ?></span></div>
    <?php endif; ?>
    <?php if (!empty($contacts['contact3_phone'])): ?>
    <div class="contact-row"><div class="c-badge cb3">3</div><span><strong><?php echo htmlspecialchars($contacts['contact3_name']); ?></strong> — <?php echo htmlspecialchars($contacts['contact3_phone']); ?></span></div>
    <?php endif; ?>
  </div>
  <?php else: ?>
  <div class="contacts-preview">
    <p style="color:#E74C3C;font-size:13px">⚠️ Emergency contacts save nahi hain! <a href="emergency_contacts.php" style="color:#6C3483">Abhi add karo →</a></p>
  </div>
  <?php endif; ?>

  <!-- Status Card -->
  <div class="status-card">
    <?php if ($active_journey): ?>
      <p><span class="status-indicator status-active"></span><strong style="color:#27AE60">Journey Active Hai!</strong></p>
    <?php else: ?>
      <p><span class="status-indicator status-inactive"></span><strong style="color:#888">Journey Shuru Nahi Hui</strong></p>
    <?php endif; ?>

    <div class="location-box">
      <strong>📍 Tumhari Location:</strong>
      <span id="locationDisplay">Detect ho rahi hai...</span>
    </div>

    <div class="alert-box alert-success" id="successAlert"></div>
    <div class="alert-box alert-danger"  id="dangerAlert"></div>

    <?php if (!$active_journey): ?>
    <button class="btn-start" onclick="startJourney()">🚶‍♀️ Journey Shuru Karo — Location Track Karo</button>
    <?php else: ?>
    <button class="btn-safe" onclick="imSafe()">✅ Main Safe Hoon — Journey Khatam</button>
    <?php endif; ?>

    <button class="btn-sos" onclick="silentSOS()">🚨 SILENT SOS — DANGER MEIN HOON!</button>
  </div>

  <!-- How it works -->
  <div class="how-card">
    <h3>⚡ Ye kaise kaam karta hai?</h3>
    <div class="step"><span class="step-icon">1️⃣</span><span>Safar ka tarika chuno — Walk, Cab, Auto, Bus ya Bike.</span></div>
    <div class="step"><span class="step-icon">2️⃣</span><span><strong>"Journey Shuru Karo"</strong> dabao — live location track hona shuru.</span></div>
    <div class="step"><span class="step-icon">3️⃣</span><span>Map pe tumhari location aur <strong>nearby active HerSafe users</strong> dikhenge.</span></div>
    <div class="step"><span class="step-icon">4️⃣</span><span>Ghar pahunchte hi <strong>"Main Safe Hoon"</strong> dabao.</span></div>
    <div class="step"><span class="step-icon">5️⃣</span><span>Koi problem ho — <strong>Silent SOS</strong> dabao — location family ko milegi!</span></div>
  </div>

  <div class="how-card">
    <h3>📞 Direct Emergency Call</h3>
    <div class="step"><span class="step-icon">🚔</span><span><a href="tel:112" style="color:#E74C3C;font-weight:700;font-size:18px">112</a> — Police Emergency</span></div>
    <div class="step"><span class="step-icon">👩</span><span><a href="tel:1091" style="color:#6C3483;font-weight:700;font-size:18px">1091</a> — Women Helpline</span></div>
    <div class="step"><span class="step-icon">🚑</span><span><a href="tel:108" style="color:#E74C3C;font-weight:700;font-size:18px">108</a> — Ambulance</span></div>
  </div>

</div>

<script>
var watchId = null;
var currentLat = null;
var currentLng = null;
var locationInterval = null;
var selectedMode = 'walk';
var map = null;
var myMarker = null;
var nearbyMarkers = [];

// Travel mode select
function selectMode(btn, mode) {
  document.querySelectorAll('.mode-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  selectedMode = mode;
}

// Init map
function initMap() {
  map = L.map('journeyMap').setView([20.5937, 78.9629], 5);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
  }).addTo(map);
}

// My marker icon
var myIcon = L.divIcon({
  html: '<div style="width:28px;height:28px;background:linear-gradient(135deg,#c0547a,#9B59B6);border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.3);"></div>',
  iconSize: [28, 28], iconAnchor: [14, 28], className: ''
});

// Nearby user icon
var nearbyIcon = L.divIcon({
  html: '<div style="width:22px;height:22px;background:#27AE60;border-radius:50%;border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.2);"></div>',
  iconSize: [22, 22], iconAnchor: [11, 11], className: ''
});

// Calculate distance between 2 coords (km)
function calcDistance(lat1, lng1, lat2, lng2) {
  var R = 6371;
  var dLat = (lat2 - lat1) * Math.PI / 180;
  var dLng = (lng2 - lng1) * Math.PI / 180;
  var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
          Math.cos(lat1 * Math.PI/180) * Math.cos(lat2 * Math.PI/180) *
          Math.sin(dLng/2) * Math.sin(dLng/2);
  var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
  var d = R * c;
  return d < 1 ? (d * 1000).toFixed(0) + ' m' : d.toFixed(2) + ' km';
}

// Fetch nearby users
function fetchNearby() {
  if (!currentLat || !currentLng) return;
  fetch('safe_journey.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'get_nearby=1&lat=' + currentLat + '&lng=' + currentLng
  })
  .then(r => r.json())
  .then(users => {
    // Clear old markers
    nearbyMarkers.forEach(m => map.removeLayer(m));
    nearbyMarkers = [];

    var html = '';
    if (users.length === 0) {
      html = '<p class="no-nearby">✅ Abhi koi nearby active user nahi hai.</p>';
    } else {
      users.forEach(function(u) {
        if (u.last_lat && u.last_lng) {
          var dist = calcDistance(currentLat, currentLng, parseFloat(u.last_lat), parseFloat(u.last_lng));
          html += '<div class="nearby-item"><div class="nearby-name"><div class="nearby-avatar">' + u.user_name.charAt(0).toUpperCase() + '</div><span>' + u.user_name + '</span></div><span class="nearby-dist">📍 ' + dist + '</span></div>';

          // Add marker on map
          var m = L.marker([parseFloat(u.last_lat), parseFloat(u.last_lng)], { icon: nearbyIcon })
            .addTo(map)
            .bindPopup('<b>👤 ' + u.user_name + '</b><br>Distance: ' + dist);
          nearbyMarkers.push(m);
        }
      });
    }
    document.getElementById('nearbyList').innerHTML = html;
  })
  .catch(function() {});
}

// Get location
function getLocation() {
  if (navigator.geolocation) {
    watchId = navigator.geolocation.watchPosition(function(pos) {
      currentLat = pos.coords.latitude;
      currentLng = pos.coords.longitude;

      document.getElementById('locationDisplay').innerHTML =
        'Lat: ' + currentLat.toFixed(5) + ', Lng: ' + currentLng.toFixed(5) +
        ' <a href="https://maps.google.com/?q=' + currentLat + ',' + currentLng + '" target="_blank" style="color:#6C3483;font-size:11px">Map pe dekho</a>';

      // Update map
      if (map) {
        map.setView([currentLat, currentLng], 15);
        if (myMarker) {
          myMarker.setLatLng([currentLat, currentLng]);
        } else {
          myMarker = L.marker([currentLat, currentLng], { icon: myIcon })
            .addTo(map)
            .bindPopup('<b>📍 Tum yahan ho!</b><br><?php echo addslashes($user_name); ?>')
            .openPopup();
        }
      }

      fetchNearby();

    }, function() {
      document.getElementById('locationDisplay').textContent = 'Location access do please!';
    }, { enableHighAccuracy: true });
  }
}

// Journey start
function startJourney() {
  getLocation();
  setTimeout(function() {
    if (!currentLat) { currentLat = 0; currentLng = 0; }
    fetch('safe_journey.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'start_journey=1&lat=' + currentLat + '&lng=' + currentLng + '&mode=' + selectedMode
    })
    .then(r => r.json())
    .then(function(data) {
      if (data.status === 'started') {
        showSuccess('✅ Journey shuru! (' + selectedMode.toUpperCase() + ') Tumhari location track ho rahi hai. Ghar pahunchte hi "Main Safe Hoon" dabana!');
        startLocationUpdates();
        setTimeout(function() { location.reload(); }, 2000);
      }
    });
  }, 2000);
}

// Location update every 5 minutes
function startLocationUpdates() {
  locationInterval = setInterval(function() {
    if (currentLat && currentLng) {
      fetch('safe_journey.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'update_location=1&lat=' + currentLat + '&lng=' + currentLng
      });
      fetchNearby();
    }
  }, 30000); // every 30 seconds
}

// I'm Safe
function imSafe() {
  if (confirm('Kya aap safe ghar pahunch gayi hain?')) {
    fetch('safe_journey.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'end_journey=1'
    })
    .then(r => r.json())
    .then(function(data) {
      clearInterval(locationInterval);
      if (watchId) navigator.geolocation.clearWatch(watchId);
      showSuccess('🎉 Bahut accha! Journey safely khatam. Family ko bhi batao!');
      setTimeout(function() { location.reload(); }, 2000);
    });
  }
}

// Silent SOS
function silentSOS() {
  if (navigator.vibrate) navigator.vibrate([500, 200, 500, 200, 500]);
  getLocation();
  setTimeout(function() {
    fetch('safe_journey.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'sos_trigger=1&lat=' + (currentLat || 0) + '&lng=' + (currentLng || 0)
    })
    .then(r => r.json())
    .then(function(data) {
      var mapsLink = 'https://maps.google.com/?q=' + currentLat + ',' + currentLng;
      var msg = encodeURIComponent('🆘 DANGER! <?php echo addslashes($user_name); ?> needs help!\n\n📍 Location: ' + mapsLink + '\n\nPlease respond immediately!');

      var phones = [
        '<?php echo addslashes($contacts["contact1_phone"] ?? ""); ?>',
        '<?php echo addslashes($contacts["contact2_phone"] ?? ""); ?>',
        '<?php echo addslashes($contacts["contact3_phone"] ?? ""); ?>'
      ].filter(p => p);

      phones.forEach(function(p, i) {
        setTimeout(function() {
          window.open('https://wa.me/91' + p + '?text=' + msg, '_blank');
        }, i * 1000);
      });

      showDanger('🚨 SILENT SOS BHEJA GAYA! Tumhari location family ko mil gayi! Abhi 112 call karo!');

      var c1 = '<?php echo addslashes($contacts["contact1_phone"] ?? ""); ?>';
      if (c1) {
        setTimeout(function() { window.location.href = 'tel:' + c1; }, 3500);
      } else {
        setTimeout(function() { window.location.href = 'tel:112'; }, 3500);
      }
    });
  }, 1500);
}

function showSuccess(msg) {
  var el = document.getElementById('successAlert');
  el.textContent = msg; el.style.display = 'block';
  document.getElementById('dangerAlert').style.display = 'none';
}
function showDanger(msg) {
  var el = document.getElementById('dangerAlert');
  el.textContent = msg; el.style.display = 'block';
  document.getElementById('successAlert').style.display = 'none';
}

// Init
window.onload = function() {
  initMap();
  getLocation();
  <?php if ($active_journey): ?>
  startLocationUpdates();
  <?php endif; ?>
  // Refresh nearby every 30 seconds
  setInterval(fetchNearby, 30000);
};
</script>
</body>
</html>
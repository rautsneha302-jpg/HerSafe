<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id   = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Fetch emergency contacts
$contacts = [];
$result = mysqli_query($conn, "SELECT * FROM emergency_contacts WHERE user_id = '$user_id'");
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    for ($i = 1; $i <= 3; $i++) {
        if (!empty($row["contact{$i}_name"])) {
            $contacts[] = [
                'name'  => $row["contact{$i}_name"],
                'phone' => preg_replace('/[^0-9]/', '', $row["contact{$i}_phone"]),
            ];
        }
    }
}

$contacts_json = json_encode($contacts);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Share Location — HerSafe</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <style>
    :root {
      --pink: #c0547a;
      --purple: #6C3483;
      --light-purple: #9B59B6;
      --bg: #f5f0fb;
      --white: #ffffff;
      --text: #2C1A3E;
      --muted: #888;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

    .header {
      background: white; padding: 16px 24px;
      display: flex; align-items: center; gap: 16px;
      box-shadow: 0 2px 12px rgba(108,52,131,0.08);
      position: sticky; top: 0; z-index: 100;
    }
    .header a { color: var(--light-purple); text-decoration: none; font-size: 13px; font-weight: 500; }
    .header h1 { font-family: 'Playfair Display', serif; font-size: 22px; color: var(--text); }
    .header h1 span { color: var(--pink); }

    .main { max-width: 900px; margin: 0 auto; padding: 24px 16px; display: flex; flex-direction: column; gap: 20px; }

    /* SOS */
    .sos-wrapper {
      display: flex; flex-direction: column; align-items: center; gap: 16px;
      padding: 32px 20px; background: white; border-radius: 20px;
      box-shadow: 0 4px 24px rgba(108,52,131,0.08);
    }
    .sos-btn {
      width: 150px; height: 150px; border-radius: 50%;
      background: linear-gradient(135deg, #e74c3c, #c0392b);
      border: none; color: white; font-size: 20px; font-weight: 700;
      cursor: pointer;
      box-shadow: 0 0 0 12px rgba(231,76,60,0.15), 0 0 0 24px rgba(231,76,60,0.07);
      display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px;
      transition: transform 0.15s;
      letter-spacing: 3px;
      animation: sosPulse 2s infinite;
    }
    .sos-btn:hover { transform: scale(1.05); }
    .sos-btn:active { transform: scale(0.96); }
    .sos-btn .sos-icon { font-size: 36px; }
    @keyframes sosPulse {
      0%, 100% { box-shadow: 0 0 0 12px rgba(231,76,60,0.15), 0 0 0 24px rgba(231,76,60,0.07); }
      50%       { box-shadow: 0 0 0 18px rgba(231,76,60,0.18), 0 0 0 32px rgba(231,76,60,0.04); }
    }
    .sos-desc { font-size: 13px; color: #e74c3c; font-weight: 600; text-align: center; line-height: 1.6; }

    /* Map */
    .card { background: white; border-radius: 20px; box-shadow: 0 4px 24px rgba(108,52,131,0.08); overflow: hidden; }
    .card-header { padding: 20px 24px 0; display: flex; align-items: center; gap: 10px; }
    .card-header .icon {
      width: 36px; height: 36px;
      background: linear-gradient(135deg, var(--pink), var(--light-purple));
      border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px;
    }
    .card-header h2 { font-size: 16px; font-weight: 600; }
    .card-header p { font-size: 12px; color: var(--muted); }
    #map { width: 100%; height: 300px; margin-top: 16px; }
    .status-bar { padding: 14px 24px; display: flex; align-items: center; gap: 10px; border-top: 1px solid #f0e8f8; font-size: 13px; color: var(--muted); }
    .status-dot { width: 10px; height: 10px; border-radius: 50%; background: #ccc; flex-shrink: 0; }
    .status-dot.active { background: #2ecc71; animation: pulse 1.5s infinite; }
    @keyframes pulse {
      0%, 100% { box-shadow: 0 0 0 3px rgba(46,204,113,0.2); }
      50%       { box-shadow: 0 0 0 6px rgba(46,204,113,0.1); }
    }
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; padding: 16px 24px 20px; border-top: 1px solid #f0e8f8; }
    .info-item label { font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 4px; }
    .info-item span { font-size: 13px; font-weight: 500; }

    /* Share */
    .share-card { background: white; border-radius: 20px; box-shadow: 0 4px 24px rgba(108,52,131,0.08); padding: 20px 24px; }
    .share-card h2 { font-size: 15px; font-weight: 600; margin-bottom: 12px; }
    .link-box { display: flex; gap: 8px; margin-bottom: 14px; }
    .link-box input {
      flex: 1; padding: 10px 14px; border: 1.5px solid #e8e0f0; border-radius: 10px;
      font-size: 12px; background: #faf8fc; outline: none; font-family: monospace;
    }
    .btn-copy {
      padding: 10px 16px;
      background: linear-gradient(135deg, var(--pink), var(--light-purple));
      color: white; border: none; border-radius: 10px;
      font-size: 13px; font-weight: 500; cursor: pointer; white-space: nowrap;
    }
    .contacts-title { font-size: 13px; color: var(--muted); margin-bottom: 10px; }
    .contacts-list { display: flex; flex-direction: column; gap: 8px; }
    .contact-item { display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: var(--bg); border-radius: 12px; }
    .contact-info { display: flex; align-items: center; gap: 10px; }
    .contact-avatar {
      width: 36px; height: 36px; border-radius: 50%;
      background: linear-gradient(135deg, var(--pink), var(--light-purple));
      display: flex; align-items: center; justify-content: center;
      color: white; font-weight: 600; font-size: 14px;
    }
    .contact-name { font-size: 14px; font-weight: 500; }
    .contact-phone { font-size: 12px; color: var(--muted); }
    .btn-whatsapp {
      padding: 8px 14px; background: #25D366; color: white;
      border: none; border-radius: 8px; font-size: 12px; font-weight: 500;
      cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;
    }
    .no-contacts { text-align: center; padding: 20px; color: var(--muted); font-size: 13px; }
    .no-contacts a { color: var(--pink); text-decoration: none; font-weight: 500; }

    /* Toast */
    .toast {
      position: fixed; bottom: 24px; left: 50%;
      transform: translateX(-50%) translateY(80px);
      background: #2C1A3E; color: white;
      padding: 12px 24px; border-radius: 12px;
      font-size: 13px; font-weight: 500;
      transition: transform 0.3s; z-index: 999;
    }
    .toast.show { transform: translateX(-50%) translateY(0); }
    .toast.red { background: #e74c3c; }

    @media (max-width: 480px) { .info-grid { grid-template-columns: 1fr; } }
  </style>
</head>
<body>

<div class="header">
  <a href="dashboard.php">← Back</a>
  <h1>HerSafe<span>.</span></h1>
</div>

<div class="main">

  <!-- SOS Button -->
  <div class="sos-wrapper">
    <button class="sos-btn" onclick="triggerSOS()">
      <span class="sos-icon">🆘</span>
      SOS
    </button>
    <p class="sos-desc">Press SOS to instantly send your location<br>to ALL emergency contacts via WhatsApp</p>
  </div>

  <!-- Map -->
  <div class="card">
    <div class="card-header">
      <div class="icon">📍</div>
      <div>
        <h2>Your Live Location</h2>
        <p>Real-time GPS tracking</p>
      </div>
    </div>
    <div id="map"></div>
    <div class="status-bar">
      <div class="status-dot" id="statusDot"></div>
      <span id="statusText">Fetching your location...</span>
    </div>
    <div class="info-grid">
      <div class="info-item"><label>Latitude</label><span id="latText">—</span></div>
      <div class="info-item"><label>Longitude</label><span id="lngText">—</span></div>
      <div class="info-item"><label>Accuracy</label><span id="accText">—</span></div>
      <div class="info-item"><label>Last Updated</label><span id="timeText">—</span></div>
    </div>
  </div>

  <!-- Share Link -->
  <div class="share-card">
    <h2>🔗 Share Location Link</h2>
    <div class="link-box">
      <input type="text" id="shareLink" readonly placeholder="Getting your location..."/>
      <button class="btn-copy" onclick="copyLink()">Copy</button>
    </div>

    <?php if (count($contacts) > 0): ?>
      <p class="contacts-title">Send individually via WhatsApp:</p>
      <div class="contacts-list">
        <?php $loop = 0; foreach ($contacts as $c): ?>
          <div class="contact-item">
            <div class="contact-info">
              <div class="contact-avatar"><?php echo strtoupper(substr($c['name'], 0, 1)); ?></div>
              <div>
                <div class="contact-name"><?php echo htmlspecialchars($c['name']); ?></div>
                <div class="contact-phone"><?php echo htmlspecialchars($c['phone']); ?></div>
              </div>
            </div>
            <a class="btn-whatsapp" href="#" target="_blank"
               data-phone="<?php echo $c['phone']; ?>">📲 WhatsApp</a>
          </div>
        <?php $loop++; endforeach; ?>
      </div>
    <?php else: ?>
      <div class="no-contacts">
        No emergency contacts added yet.<br>
        <a href="emergency_contacts.php">+ Add Emergency Contacts</a>
      </div>
    <?php endif; ?>
  </div>

</div>

<div class="toast" id="toast"></div>

<script>
  const contacts = <?php echo $contacts_json; ?>;
  const userName = "<?php echo addslashes($user_name); ?>";
  let map, marker, userLat, userLng;

  map = L.map('map').setView([20.5937, 78.9629], 5);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
  }).addTo(map);

  const icon = L.divIcon({
    html: `<div style="width:32px;height:32px;background:linear-gradient(135deg,#c0547a,#9B59B6);border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.3);"></div>`,
    iconSize: [32, 32], iconAnchor: [16, 32], className: ''
  });

  function showToast(msg, red = false) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'toast show' + (red ? ' red' : '');
    setTimeout(() => t.className = 'toast', 3000);
  }

  function updateLinks() {
    const url = `https://maps.google.com/?q=${userLat},${userLng}`;
    const msg = encodeURIComponent(`🌸 ${userName} is sharing their live location!\n\n📍 View here: ${url}\n\nSent via HerSafe`);
    document.querySelectorAll('.btn-whatsapp').forEach(btn => {
      btn.href = `https://wa.me/${btn.dataset.phone}?text=${msg}`;
    });
    document.getElementById('shareLink').value = url;
  }

  function copyLink() {
    const val = document.getElementById('shareLink').value;
    if (!val || val.includes('Getting')) return;
    navigator.clipboard.writeText(val).then(() => showToast('✅ Link copied!'));
  }

  function triggerSOS() {
    if (!userLat || !userLng) { showToast('⚠️ Location not ready yet!', true); return; }
    if (contacts.length === 0) { showToast('⚠️ No emergency contacts found!', true); return; }

    const url = `https://maps.google.com/?q=${userLat},${userLng}`;
    const msg = encodeURIComponent(`🆘 EMERGENCY! ${userName} needs help!\n\n📍 Live location: ${url}\n\nPlease respond immediately!\n\nSent via HerSafe 🌸`);

    contacts.forEach((c, i) => {
      setTimeout(() => {
        window.open(`https://wa.me/${c.phone}?text=${msg}`, '_blank');
      }, i * 800);
    });

    showToast(`🆘 SOS sent to ${contacts.length} contact(s)!`, true);
  }

  if (navigator.geolocation) {
    navigator.geolocation.watchPosition(
      function(pos) {
        userLat = pos.coords.latitude.toFixed(6);
        userLng = pos.coords.longitude.toFixed(6);
        const acc = Math.round(pos.coords.accuracy);
        const now = new Date().toLocaleTimeString();

        map.setView([userLat, userLng], 16);
        if (marker) { marker.setLatLng([userLat, userLng]); }
        else {
          marker = L.marker([userLat, userLng], { icon }).addTo(map);
          marker.bindPopup(`<b>📍 You are here</b><br>${userName}`).openPopup();
        }

        document.getElementById('latText').textContent = userLat;
        document.getElementById('lngText').textContent = userLng;
        document.getElementById('accText').textContent = acc + ' meters';
        document.getElementById('timeText').textContent = now;
        document.getElementById('statusDot').classList.add('active');
        document.getElementById('statusText').textContent = 'Location active — updating live';
        updateLinks();
      },
      function() {
        document.getElementById('statusText').textContent = '⚠️ Location access denied.';
      },
      { enableHighAccuracy: true, maximumAge: 10000, timeout: 10000 }
    );
  }
</script>
</body>
</html>
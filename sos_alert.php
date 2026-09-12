<script>
  var c1phone = '<?php echo addslashes($contacts["contact1_phone"] ?? ""); ?>';
  var c2phone = '<?php echo addslashes($contacts["contact2_phone"] ?? ""); ?>';
  var c3phone = '<?php echo addslashes($contacts["contact3_phone"] ?? ""); ?>';
  var c1name  = '<?php echo addslashes($contacts["contact1_name"] ?? ""); ?>';
  var c2name  = '<?php echo addslashes($contacts["contact2_name"] ?? ""); ?>';
  var c3name  = '<?php echo addslashes($contacts["contact3_name"] ?? ""); ?>';
  var userName = '<?php echo addslashes($_SESSION["user_name"] ?? ""); ?>';

  function triggerSOS() {
    document.getElementById('alertSent').style.display = 'block';
    document.getElementById('alertSent').scrollIntoView({ behavior: 'smooth' });
    if (navigator.vibrate) navigator.vibrate([500, 200, 500, 200, 500]);

    var contacts = [];
    if (c1phone) contacts.push({ name: c1name, phone: c1phone });
    if (c2phone) contacts.push({ name: c2name, phone: c2phone });
    if (c3phone) contacts.push({ name: c3name, phone: c3phone });

    if (contacts.length === 0) {
      document.getElementById('alertMsg').innerHTML = '⚠️ Koi emergency contact nahi hai!';
      return;
    }

    // Step 1 — Check karo registered hain ya nahi
    var phoneList = contacts.map(c => c.phone).join(',');
    document.getElementById('alertMsg').innerHTML = '🔍 Contacts check ho rahe hain...';

    fetch('check_contacts.php?phones=' + phoneList)
      .then(res => res.json())
      .then(function(data) {

        var registered = [];
        var unregistered = [];

        contacts.forEach(function(c) {
          if (data[c.phone] && data[c.phone].registered) {
            registered.push(c);
          } else {
            unregistered.push(c);
          }
        });

        // UI update
        var statusHTML = '';
        contacts.forEach(function(c) {
          var isReg = data[c.phone] && data[c.phone].registered;
          statusHTML += '<span style="background:' + (isReg ? '#27AE60' : '#E67E22') + ';color:white;padding:3px 10px;border-radius:20px;font-size:12px;margin:3px;display:inline-block;">' +
            c.name + (isReg ? ' ✅ HerSafe User' : ' 📵 Not Registered') + '</span>';
        });
        document.getElementById('alertMsg').innerHTML = statusHTML;

        // Step 2 — Registered contacts ko pehle call karo
        var allContacts = [...registered, ...unregistered];

        // Pehle call
        if (allContacts[0]) {
          setTimeout(function() {
            window.location.href = 'tel:' + allContacts[0].phone;
          }, 800);
        }

        // Step 3 — Location le aur WhatsApp bhejo
        setTimeout(function() {
          if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(pos) {
              var lat = pos.coords.latitude;
              var lng = pos.coords.longitude;
              var mapsLink = 'https://maps.google.com/?q=' + lat + ',' + lng;

              allContacts.forEach(function(c, i) {
                var msg;
                if (data[c.phone] && data[c.phone].registered) {
                  // Registered user ko special message
                  msg = encodeURIComponent(
                    '🆘 EMERGENCY ALERT!\n\n' +
                    userName + ' ne HerSafe par SOS bheja hai!\n\n' +
                    '📍 Live Location: ' + mapsLink + '\n\n' +
                    '⚠️ Woh danger mein hain — turant respond karo!\n\n' +
                    'HerSafe App par login karo: hersafe.free.nf 🌸'
                  );
                } else {
                  // Unregistered ko normal message
                  msg = encodeURIComponent(
                    '🆘 EMERGENCY!\n\n' +
                    userName + ' ko madad chahiye!\n\n' +
                    '📍 Location: ' + mapsLink + '\n\n' +
                    'Turant respond karo!\n\nSent via HerSafe 🌸'
                  );
                }

                setTimeout(function() {
                  window.open('https://wa.me/91' + c.phone + '?text=' + msg, '_blank');
                }, i * 1200);
              });

            }, function() {
              // Location nahi mili — bhi WhatsApp bhejo
              allContacts.forEach(function(c, i) {
                var msg = encodeURIComponent('🆘 EMERGENCY! ' + userName + ' ko madad chahiye! Turant respond karo!\n\nSent via HerSafe 🌸');
                setTimeout(function() {
                  window.open('https://wa.me/91' + c.phone + '?text=' + msg, '_blank');
                }, i * 1200);
              });
            });
          }
        }, 6000);

        // Database mein save
        document.getElementById('sosForm').submit();
      })
      .catch(function() {
        // Agar check fail ho — normal SOS chalao
        document.getElementById('alertMsg').innerHTML = '🚨 Alert bheja ja raha hai...';
        if (contacts[0]) {
          setTimeout(function() {
            window.location.href = 'tel:' + contacts[0].phone;
          }, 500);
        }
      });
  }

  function getLocation() {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(function(pos) {
        var lat = pos.coords.latitude.toFixed(4);
        var lng = pos.coords.longitude.toFixed(4);
        document.getElementById('locationText').innerHTML =
          'Lat: ' + lat + ', Lng: ' + lng +
          ' <a href="https://maps.google.com/?q=' + lat + ',' + lng +
          '" target="_blank" style="color:#6C3483;font-size:11px">Map pe dekho</a>';
      }, function() {
        document.getElementById('locationText').textContent = 'Location access denied.';
      });
    }
  }
</script>
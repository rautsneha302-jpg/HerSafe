<?php
// Language setup
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = $_SESSION['lang'] ?? 'en';

$translations = [
    'en' => [
        'welcome'    => 'Welcome',
        'safe_today' => 'You are safe with HerSafe. What do you need today?',
        'sos'        => 'SOS Alert',
        'safe_routes'=> 'Safe Routes',
        'help'       => 'Help Centers',
        'stories'    => 'Stories',
        'self_def'   => 'Self Defense',
        'report'     => 'Report Area',
        'emergency'  => 'Emergency Contacts',
        'journey'    => 'Safe Journey',
        'profile'    => 'My Profile',
        'logout'     => 'Logout',
        'get_help'   => 'Get Help Now',
        'register'   => 'Register Free',
        'tagline'    => 'You Are Never Alone — We Are Always Here',
    ],
    'hi' => [
        'welcome'    => 'स्वागत है',
        'safe_today' => 'HerSafe के साथ आप सुरक्षित हैं। आज क्या चाहिए?',
        'sos'        => 'SOS अलर्ट',
        'safe_routes'=> 'सुरक्षित रास्ते',
        'help'       => 'सहायता केंद्र',
        'stories'    => 'कहानियाँ',
        'self_def'   => 'आत्मरक्षा',
        'report'     => 'क्षेत्र रिपोर्ट',
        'emergency'  => 'आपातकालीन संपर्क',
        'journey'    => 'सुरक्षित यात्रा',
        'profile'    => 'मेरी प्रोफाइल',
        'logout'     => 'लॉगआउट',
        'get_help'   => 'अभी सहायता लें',
        'register'   => 'मुफ्त रजिस्टर करें',
        'tagline'    => 'आप कभी अकेली नहीं हैं — हम हमेशा यहाँ हैं',
    ],
    'mr' => [
        'welcome'    => 'स्वागत आहे',
        'safe_today' => 'HerSafe सोबत तुम्ही सुरक्षित आहात. आज काय हवे?',
        'sos'        => 'SOS अलर्ट',
        'safe_routes'=> 'सुरक्षित मार्ग',
        'help'       => 'मदत केंद्रे',
        'stories'    => 'कथा',
        'self_def'   => 'स्वसंरक्षण',
        'report'     => 'क्षेत्र अहवाल',
        'emergency'  => 'आपत्कालीन संपर्क',
        'journey'    => 'सुरक्षित प्रवास',
        'profile'    => 'माझी प्रोफाइल',
        'logout'     => 'लॉगआउट',
        'get_help'   => 'आत्ता मदत घ्या',
        'register'   => 'मोफत नोंदणी करा',
        'tagline'    => 'तुम्ही कधीच एकट्या नाही — आम्ही नेहमी इथे आहोत',
    ],
];

function t($key) {
    global $translations, $lang;
    return $translations[$lang][$key] ?? $translations['en'][$key] ?? $key;
}
?>
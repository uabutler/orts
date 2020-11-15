const VIEW_TEXT = "View";
const HIDE_TEXT = "Hide";
const EDIT_TEXT = "Edit";
const BASE_URL = "http://localhost:3000/api/v1"

/**
 * Launches the Authentication Needed modal
 */
function launchAuthenticationModal(){
  $.get("authenticationModal.html", function(h){
    $(h).appendTo('main');
    $("#authenticationNeededSignIn").click(function(){
      var win = window.open("login.php", "_blank");
      if(win){
        win.focus();
        $.modal.close();
      } else {
        alert("Please allow popups for this website.");
      }
    });
    $("#authenticationNeeded").modal({
      escapeClose: false,
      clickClose: false,
      showClose: false,
      fadeDuration: 250
    });
  });
};

/**
 * Sets a Session Cookie
 * @param {string} name name of cookie
 * @param {string} value cookie value
 */
function setCookie(name, value) {
  document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value);
}

/**
 * Get the value of a cookie
 * @param {string} name name of cookie
 * @return Cookie value, or null if not found
 */
function getCookie(name) {
  var cookieArr = document.cookie.split(";");
  
  for(var i = 0; i < cookieArr.length; i++) {
      var cookiePair = cookieArr[i].split("=");
      if(encodeURIComponent(name) == cookiePair[0].trim()) {
          return decodeURIComponent(cookiePair[1]);
      }
  }
  
  // Return null if not found
  return null;
}

/**
 * Clears the value of a cookie, thereby "deleting" it.
 * @param {string} name name of cookie
 */
function clearCookie(name) {
  document.cookie = encodeURIComponent(name) + "="
}

// MOCK; REMOVE FOR PRODUCTION
setCookie("userName", "Abby Example");
setCookie("userEmail", "ane1234@truman.edu");
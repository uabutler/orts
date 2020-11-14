const VIEW_TEXT = "View";
const HIDE_TEXT = "Hide";
const EDIT_TEXT = "Edit";
const BASE_URL = "http://localhost:3000/api/v1"

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


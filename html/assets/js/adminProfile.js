$(document).ready(function() {
  const dismissible = new Dismissible(document.querySelector('#dismissible-container'));
   
  /* Load Profile */
  $("#email").val(getCookie("userEmail"));
  $.ajax(
   BASE_URL+"/profile",
   {
       type: "get",
       data: {
           email: getCookie("userEmail")
       },
       complete: function(request, status){
         if (request.status == 200){
           currProfile = $.parseJSON(request.responseText);
           $("#firstname").val(currProfile["first-name"]);
           $("#lastname").val(currProfile["last-name"]);
           $("#profileLoading").css("display", "none");
         } else if (request.status == 404){
           $("#profileLoading").text("No current profile found. Please enter your information below.");
         } else {
           var data = $.parseJSON(request.responseText);
           dismissible.error("An Error Occurred: " + data.message + " (Code " + data.code + ")");
         }
       }
   }
 );
  
  /**
   * A validator method to match a regex pattern
   */
  $.validator.addMethod('regex', function(value, element, param) {
    return this.optional(element) ||
        value.match(typeof param == 'string' ? new RegExp(param) : param);
  },
  'Please enter a value in the correct format.');

  $("#profileForm").validate({
    rules: {
      email: {
        email: true,
        regex: /@truman.edu$/
      }
    },
    messages: {
      email: "Please enter a valid Truman email address",
    },
    submitHandler: function(form, event){
      event.preventDefault();
      var payload = {
        "first-name": $("#firstname").val(),
        "last-name": $("#lastname").val(),
        email: $("#email").val()
      }
      $.ajax({
        method: "PUT",
        url: BASE_URL+"/profile",
        contentType: "application/json",
        data: JSON.stringify(payload),
        complete: function(request, status){
          if (request.status == 200){
            setCookie("userName", payload["first-name"] + " " + payload["last-name"]);
            $("#userName").text(getCookie("userName"));
            dismissible.success("Profile Updated Successfully");
          } else {
            var data = $.parseJSON(request.responseText);
            dismissible.error("An Error Occurred: " + data.message + " (Code " + data.code + ")");
          }
        }
      });
      return false;
    }
  });

});
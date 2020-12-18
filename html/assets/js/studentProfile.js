$(document).ready(function() {
  const dismissible = new Dismissible(document.querySelector('#dismissible-container'));
  var currProfile;
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
            $("#bannerid").val(currProfile["banner-id"]);
            $("#class").val(currProfile["class-standing"]);
            const gradDate = currProfile["grad-month"].split("/");
            $("#gradmonth").val(gradDate[0]);
            $("#gradyear").val(gradDate[1]);
            if(!$("#majors").is(":empty") && !$("#minors").is(":empty")){
              $("#majors").val(currProfile.major);
              $("#minors").val(currProfile.minor);
            }
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
  /* Load Majors */
  $.ajax(
    BASE_URL+"/majors",
    {
      success: function(data, status, xhr){
        data = $.parseJSON(data);
        $(data.majors).each(function(){
          $("#majors").append("<option value='" + this + "'>" + this + "</option>");
        });
        // Sort majors
        var select = $('#majors');
        select.html(select.find('option').sort(function(x, y) {
          // to change to descending order switch "<" for ">"
          return $(x).text() > $(y).text() ? 1 : -1;
        }));
        if(currProfile !== undefined && currProfile !== null){
          $("#majors").val(currProfile.major);
        }
        $("#majors").prop("disabled", false);
        $("#majorsLoading").css("display", "none");
      },
      error: function(request, status, error){
        var data = $.parseJSON(request.responseText);
        dismissible.error("An Error Occurred: " + data.message + " (Code " + data.code + ")");
      }
    }
  );

  /* Load Minors */
  $.ajax(
    BASE_URL+"/minors",
    {
      success: function(data, status, xhr){
        data = $.parseJSON(data);
        $(data.minors).each(function(){
          $("#minors").append("<option value='" + this + "'>" + this + "</option>");
        });
        // Sort minors
        var select = $('#minors');
        select.html(select.find('option').sort(function(x, y) {
          // to change to descending order switch "<" for ">"
          return $(x).text() > $(y).text() ? 1 : -1;
        }));
        if(currProfile !== undefined && currProfile !== null){
          $("#minors").val(currProfile.minor);
        }
        $("#minors").prop("disabled", false);
        $("#minorsLoading").css("display", "none");
      },
      error: function(request, status, error){
        var data = $.parseJSON(request.responseText);
        dismissible.error("An Error Occurred: " + data.message + " (Code " + data.code + ")");
      }
    }
  );

  $("#majors, #minors").select2({
    multiple: true,
    closeOnSelect: true,
    width: 'auto',
    dropdownAutoWidth: 'true'
  });

  //initially clear select otherwise first option is selected
  // can also set default value(s) here
  // ex .val(["2020-08", "2020-07"])
  $('.select2').val(null).trigger('change');

  /**
   * A validator method to match a regex pattern
   */
  $.validator.addMethod('regex', function(value, element, param) {
    return this.optional(element) ||
        value.match(typeof param == 'string' ? new RegExp(param) : param);
  },
  'Please enter a value in the correct format.');

  /**
   * Validator method to not equal a value
   */
  $.validator.addMethod("valueNotEquals", function(value, element, arg) {
      return arg !== value;
  }, "Value must not equal arg.");

  $("#profileForm").validate({
    rules: {
      bannerid: {
        regex: /^001\d{6}$/
      },
      email: {
        email: true,
        regex: /@truman.edu$/
      },
      gradmonth: {
        valueNotEquals: "default"
      },
      class: {
        valueNotEquals: "default"
      },
      gradyear:{
        min: 2020
      }
    },
    messages: {
      bannerid: "Please enter a valid Banner ID",
      email: "Please enter a valid Truman email address",
      gradmonth: "Please select a graduation month.",
      class: "Please select a class standing."
    },
    submitHandler: function(form, event){
      event.preventDefault();
      var selectedMajors = [];
      $("#majors option:selected").each(function(){
        selectedMajors.push($(this).val());
      });
      var selectedMinors = [];
      $("#minors option:selected").each(function(){
        selectedMinors.push($(this).val());
      });
      var payload = {
        "first-name": $("#firstname").val(),
        "last-name": $("#lastname").val(),
        "banner-id": $("#bannerid").val(),
        email: $("#email").val(),
        "grad-month": $("#gradmonth option:selected").val() + "/" + $("#gradyear").val(),
        major: selectedMajors,
        minor: selectedMinors,
        "class-standing": $("#class option:selected").val()
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
$(document).ready(function() {
  const dismissible = new Dismissible(document.querySelector('#dismissible-container'));
  var profile;
  $("#crn, #classtitle").val(null);

  /* Load Profile */
    $.ajax(
        BASE_URL+"/profile",
        {
            type: "get",
            data: {
                email: getCookie("userEmail")
            },
            success: function(data, status, xhr){
                profile = $.parseJSON(data);
                $("#profileLoading").css("display", "none");
                
            },
            error: function(request, status, error){
                var data = $.parseJSON(request.responseText);
                dismissible.error("An Error Occurred: " + data.message + " (Code " + data.code + ")");
            }
        }
    );
  /* Load Semesters */
  $.ajax(
    BASE_URL+"/semesters",
    {
      success: function(data, status, xhr){
        data = $.parseJSON(data);
        $(data.semesters).each(function(){
          $("#semester").append("<option value='" + this + "'>" + this + "</option>");
        });
        // Sort reasons
        var select = $('#semester');
        select.html(select.find('option').sort(function(x, y) {
          // to change to descending order switch "<" for ">"
          return $(x).text() > $(y).text() ? 1 : -1;
        }));
        $("#semester").prop("disabled", false);
        $("#semesterLoading").css("display", "none");
      },
      error: function(request, status, error){
        var data = $.parseJSON(request.responseText);
        dismissible.error("An Error Occurred: " + data.message + " (Code " + data.code + ")");
      }
    }
  );
  /* Load Departments */
  $.ajax(
    BASE_URL+"/departments",
    {
      success: function(data, status, xhr){
        data = $.parseJSON(data);
        $(data.departments).each(function(){
          $("#department").append("<option value='" + this + "'>" + this + "</option>");
        });
        // Sort reasons
        var select = $('#department');
        select.html(select.find('option').sort(function(x, y) {
          // to change to descending order switch "<" for ">"
          return $(x).text() > $(y).text() ? 1 : -1;
        }));
        $("#department").prop("disabled", false);
        $("#departmentLoading").css("display", "none");
      },
      error: function(request, status, error){
        var data = $.parseJSON(request.responseText);
        dismissible.error("An Error Occurred: " + data.message + " (Code " + data.code + ")");
      }
    }
  );
    /* Load Types */
    $.ajax(
      BASE_URL+"/requests/types",
      {
        success: function(data, status, xhr){
          data = $.parseJSON(data);
          $(data.types).each(function(){
            $("#type").append("<option value='" + this + "'>" + this + "</option>");
          });
          // Sort reasons
          var select = $('#type');
          select.html(select.find('option').sort(function(x, y) {
            // to change to descending order switch "<" for ">"
            return $(x).text() > $(y).text() ? 1 : -1;
          }));
          $("#type").prop("disabled", false);
          $("#typeLoading").css("display", "none");
        },
        error: function(request, status, error){
          var data = $.parseJSON(request.responseText);
          dismissible.error("An Error Occurred: " + data.message + " (Code " + data.code + ")");
        }
      }
    );

  // Deal with loading CRN and Title
  $("#semester, #department, #classnumber, #sectionnumber").change(function(){
    if($("#semester").val() != "zzzdefault" && 
       $("#department").val() != "zzzdefault" && 
       $("#classnumber").val() != "" &&
       $("#sectionnumber").val() != ""){
         $("#crnLoading, #titleLoading").css("display", "inline");
         $("#crn, #classtitle").val(null);
        /* Load class info */
        $.ajax(
          BASE_URL+"/courses",
          {
            type: "get",
            data: {
              department: $("#department").val(),
              number: parseInt($("#classnumber").val()),
              section: parseInt($("#sectionnumber").val()),
              semester: $("#semester").val()
            },
            success: function(data, status, xhr){
              data = $.parseJSON(data);
              $("#crn").val(data.crn);
              $("#classtitle").val(data.title);
              $("#crnLoading, #titleLoading").css("display", "none");
            },
            error: function(request, status, error){
              var data = $.parseJSON(request.responseText);
              dismissible.error("An Error Occurred: " + data.message + " (Code " + data.code + ")");
            }
          }
        );
    } else {
      $("#crn, #classtitle").val(null);
    }
  });

  /**
   * Validator method to not equal a value
   */
  $.validator.addMethod("valueNotEquals", function(value, element, arg) {
    return arg !== value;
  }, "Value must not equal arg.");
  /**
   * Validator method to accept regex values
   */
  $.validator.addMethod('regex', function(value, element, param) {
    return this.optional(element) ||
        value.match(typeof param == 'string' ? new RegExp(param) : param);
  }, 'Please enter a value in the correct format.');

  var validator = $("#requestForm").validate({
    rules: {
      semester: {
        valueNotEquals: "zzzdefault"
      },
      department: { 
        valueNotEquals: "zzzdefault"
      },
      classnumber: {
        min: 0,
        max: 499
      },
      sectionnumber: {
        min: 1
      },
      crn: {
        regex: /^\d{4}\$/
      },
      type: {
        valueNotEquals: "zzzdefault"
      },
      explanation: {
        maxlength: 65535
      }
    },
    messages: {
      semester: "Please select a semester.",
      department: "Please select a department.",
      classnumber: "Not a valid class number.",
      sectionnumber: "Not a valid section.",
      crn: "CRN is invalid.",
      type: "Please select a request type.",
      explanation: {
        maxlength: "Please shorten your explanation. Character limit is 65,535."
      },
      terms: "You must agree to the Terms and Conditions."
    },

    submitHandler: function(form, event){
      event.preventDefault();

      // Make sure the profile is loaded
      if (profile === undefined || profile === null){
        dismissible.error("An Error Occurred: Profile data could not be read.");
        return false;
      }

      // Collect Override Types
      var selectedTypes = [];
      $("#type option:selected").each(function(){
        selectedTypes.push($(this).val());
      });

      // Make request
      $.ajax({
          method: "POST",
          url: BASE_URL+"/requests",
          contentType: "application/json",
          data: JSON.stringify({
            "last-name": profile['last-name'],
            "first-name": profile['first-name'],
            "grad-month": profile['grad-month'],
            "banner-id": profile['banner-id'],
            "crn": $("#crn").val(),
            "department": $("#department").val(),
            "class-number": $("#classnumber").val(),
            "class-standing": profile['class-standing'],
            "semester": $("#semester").val(),
            "types": selectedTypes,
            "email": getCookie("userEmail"),
            "major": profile.major,
            "minor": profile.minor,
            "explanation": $("#explanation").val()
          }),
          complete: function(request, status){
            if (request.status == 201){
              dismissible.success("Request Created Successfully");
              form.reset();
              $('.select2').val(null).trigger('change');
            } else {
              var data = $.parseJSON(request.responseText);
              dismissible.error("An Error Occurred: " + data.message + " (Code " + data.code + ")");
            }
          }
        });
    }
  })
});

$(document).ready(function() {

  /* Load Majors */
  $.ajax(
    BASE_URL+"/majors",
    {
      success: function(data, status, xhr){
        data = $.parseJSON(data);
        $(data).each(function(){
          $("#majors").append("<option value='" + this.id + "'>" + this.name + "</option>");
        });
        $("#majors").prop("disabled", false);
        $("#majorsLoading").css("display", "none");
      },
      failure: function(data, status, xhr){
        data = $.parseJSON(data);
        //TODO
        console.log("status: " + status + "; data: " + data);
      }
    }
  );

  /* Load Minors */
  $.ajax(
    BASE_URL+"/minors",
    {
      success: function(data, status, xhr){
        data = $.parseJSON(data);
        $(data).each(function(){
          $("#minors").append("<option value='" + this.id + "'>" + this.name + "</option>");
        });
        $("#minors").prop("disabled", false);
        $("#minorsLoading").css("display", "none");
      },
      failure: function(data, status, xhr){
        data = $.parseJSON(data);
        //TODO
        console.log("status: " + status + "; data: " + data);
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
      gradyear:{
        min: 2020
      }
    },
    messages: {
      bannerid: "Please enter a valid Banner ID",
      email: "Please enter a valid Truman email address",
      gradmonth: "Please select a graduation month."
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
        firstname: $("#firstname").val(),
        lastname: $("#lastname").val(),
        bannerid: $("#bannerid").val(),
        email: $("#email").val(),
        gradmonth: $("#gradmonth option:selected").val() + "/" + $("#gradyear").val(),
        majors: selectedMajors,
        minors: selectedMinors
      }
      console.log(payload);
      return false;
    }
  });

});
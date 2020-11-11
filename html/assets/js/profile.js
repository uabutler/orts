$(document).ready(function() {
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
      var payload = {
        firstname: $("#firstname").val(),
        lastname: $("#lastname").val(),
        bannerid: $("#bannerid").val(),
        email: $("#email").val(),
        gradmonth: $("#gradmonth option:selected").val() + "/" + $("#gradyear").val()
      }
      console.log(payload);
      return false;
    }
  });

});
$(document).ready(function() {

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
        firstname: $("#firstname").val(),
        lastname: $("#lastname").val(),
        email: $("#email").val()
      }
      console.log(payload);
      return false;
    }
  });

});
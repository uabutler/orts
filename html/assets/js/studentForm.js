$(document).ready(function() {
  $("#crn, #classtitle").val(null);
  /* Load Departments */
  $.ajax(
    BASE_URL+"/departments",
    {
      success: function(data, status, xhr){
        data = $.parseJSON(data);
        $(data).each(function(){
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
      failure: function(data, status, xhr){
        data = $.parseJSON(data);
        //TODO
        console.log("status: " + status + "; data: " + data);
      }
    }
  );
    /* Load Types */
    $.ajax(
      BASE_URL+"/requests/types",
      {
        success: function(data, status, xhr){
          data = $.parseJSON(data);
          $(data).each(function(){
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
        failure: function(data, status, xhr){
          data = $.parseJSON(data);
          //TODO
          console.log("status: " + status + "; data: " + data);
        }
      }
    );

  $("#type").select2({
    multiple: true,
    closeOnSelect: true,
    width: 'auto',
    dropdownAutoWidth: 'true'
  });

  //initially clear select otherwise first option is selected
  // can also set default value(s) here
  // ex .val(["2020-08", "2020-07"])
  $('.select2').val(null).trigger('change');

  // Deal with loading CRN and Title
  $("#department, #classnumber, #sectionnumber").change(function(){
    if($("#department").val() != "zzzdefault" && 
       $("#classnumber").val() != "" &&
       $("#sectionnumber").val() != ""){
         $("#crnLoading, #titleLoading").css("display", "inline");
         $("#crn, #classtitle").val(null);
        /* Load class info */
        $.ajax(
          BASE_URL+"/courses",
          {
            data: JSON.stringify({
              department: $("#department").val(),
              number: parseInt($("#classnumber").val()),
              section: parseInt($("#sectionnumber").val())
            }),
            success: function(data, status, xhr){
              data = $.parseJSON(data);
              $("#crn").val(data.crn);
              $("#classtitle").val(data.title);
              $("#crnLoading, #titleLoading").css("display", "none");
            },
            failure: function(data, status, xhr){
              data = $.parseJSON(data);
              //TODO
              console.log("status: " + status + "; data: " + data);
            }
          }
        );
    } else {
      $("#crn, #classtitle").val(null);
    }
  });
});
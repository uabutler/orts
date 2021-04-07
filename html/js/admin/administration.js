let MODE = "major\n"; // Arbitrarily chosen so this field will always have a value

$(function ()
{
   $('#major-add').on("click", function ()
   {
      $("#overlay").css("display", "initial");
      $("body").css("overflow-y", "hidden");
      MODE = "major\n";
   });

   $('#minor-add').on("click", function ()
   {
      $("#overlay").css("display", "initial");
      $("body").css("overflow-y", "hidden");
      MODE = "minor\n";
   });

   $('#cancel').on("click", function ()
   {
      $("#overlay").css("display", "none");
      $("body").css("overflow-y", "scroll");
   });

   $('#add-programs').on("click", addPrograms);

   $('.major-del').on("click", function ()
   {
      console.log($(this).val());
   })
});

function addPrograms()
{
   $.post("/api/administration/programs.php", MODE + $('#entries').val(), function(data)
   {
      $("#overlay").css("display", "none");
      $("body").css("overflow-y", "scroll");
      // TODO: Display new majors in table
      $('#entries').val("");
   })
   .fail(function(response)
   {
      console.log("Could not add request");
      console.log(MODE + $('#entries').val());
      console.log(response)
      // TODO: Display error to user
   });
}
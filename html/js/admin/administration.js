let MODE = "major\n"; // Arbitrarily chosen so this field will always have a value

$(function ()
{
   $('#major-add').on("click", function ()
   {
      $("#overlay").css("display", "initial");
      MODE = "major\n";
   });

   $('#minor-add').on("click", function () {
      $("#overlay").css("display", "initial");
      MODE = "minor\n";
   });

   $('#cancel').on("click", function () { $("#overlay").css("display", "none"); });

   $('#add-programs').on("click", addPrograms);
});

function addPrograms()
{
   $.post("/api/administration/programs.php", MODE + $('#entries').val(), function(data)
   {
      $("#overlay").css("display", "none");
      // TODO: Display new majors in table
   })
    .fail(function(response)
    {
       console.log("Could not add request");
       console.log(MODE + $('#entries').val());
       console.log(response)
       // TODO: Display error to user
    });
}
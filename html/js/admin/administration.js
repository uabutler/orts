let MODE = "major"; // Arbitrarily chosen so this field will always have a value

$(function ()
{
   $('#major-add').on("click", function ()
   {
      $("#overlay").css("display", "initial");
      $("body").css("overflow-y", "hidden");
      MODE = "major";
   });

   $('#minor-add').on("click", function ()
   {
      $("#overlay").css("display", "initial");
      $("body").css("overflow-y", "hidden");
      MODE = "minor";
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
   let data = {};
   data.type = MODE;
   data.programs = [];

   for (let program of $('#entries').val().split(/\r?\n+/))
   {
       program.trim();
       if (/^[A-Za-z ]+$/.test(program))
       {
          data.programs.push(program);
       }
       else if (program !== "")
       {
          // TODO: Display error
          return;
       }
   }

   $.post("/api/admin/programs.php", JSON.stringify(data), function(data)
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
function switchSection(newSection)
{
   $('.administration-section').addClass('hidden');
   $(`#${newSection}-administration`).removeClass('hidden');

   $('.administration-menu.item').removeClass('active');
   $(`#${newSection}-menu-button`).addClass('active');
}

$(function()
{
   $('#semester-menu-button').on('click', function() { switchSection('semester') });
   $('#faculty-menu-button').on('click', function() { switchSection('faculty') });
   $('#program-menu-button').on('click', function() { switchSection('program') });
   $('#student-menu-button').on('click', function() { switchSection('student') });
   $('#course-menu-button').on('click', function() { switchSection('course') });
});

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
      console.log($(this).data('value'));
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
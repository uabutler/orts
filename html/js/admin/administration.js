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
   $('#course-menu-button').on('click', function() { switchSection('course') });
});
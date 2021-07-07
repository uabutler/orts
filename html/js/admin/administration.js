function switchSection(newSection)
{
   $('.administration-section').addClass('hidden');
   $(`#${newSection}-administration`).removeClass('hidden');

   $('.administration-menu.item').removeClass('active');
   $(`#${newSection}-menu-button`).addClass('active');

   Cookies.set('admin-page', newSection);
}

$(function()
{
   $('#semester-menu-button').on('click', function() { switchSection('semester') });
   $('#faculty-menu-button').on('click', function() { switchSection('faculty') });
   $('#program-menu-button').on('click', function() { switchSection('program') });
   $('#course-menu-button').on('click', function() { switchSection('course') });

   if (!Cookies.get('admin-page'))
       Cookies.set('admin-page', 'semester');

   switchSection(Cookies.get('admin-page'));
});
function updateDisplay()
{
    $.ajax({
        url: '/api/student/student.php',
        method: 'GET',
        data: "email=" + STUDENT_EMAIL,
        success: function(data)
        {
            data = JSON.parse(data).response;

            // Create standing arrays
            let majors = [];
            let minors = [];

            for(const major of data.majors)
                majors.push(major.name);

            for(const minor of data.minors)
                minors.push(minor.name);

            // Graduation month
            let month = data.grad_month.substr(0, 3);
            let year = data.grad_month.substr(3);

            // Name
            $('#first-name-input-display').val(data.first_name);
            $('#last-name-input-display').val(data.last_name);
            $('#first_name').val(data.first_name);
            $('#last_name').val(data.last_name);

            // Standing
            $('#standing-input-display').dropdown('set text', data.standing);
            $('#standing').dropdown('set selected', data.standing);

            // Programs
            $('#major-input-display').dropdown('set exactly', majors);
            $('#minor-input-display').dropdown('set exactly', minors);
            $('#major-input-edit').dropdown('set exactly', majors);
            $('#minor-input-edit').dropdown('set exactly', minors);

            // Banner
            $('#banner-input-display').val(data.banner_id);
            $('#banner_id').val(data.banner_id);

            // Grad month
            $('#grad-month-input-display').dropdown('set selected', month)
            $('#grad-year-input-display').val(year)
            $('#grad_month').dropdown('set selected', month)
            $('#year').val(year)
        }
    });
}

function updateProfile(data, edit, display)
{
    $('section').addClass('ui loading form');

    $.ajax({
        url: '/api/student/student.php',
        method: 'PUT',
        data: JSON.stringify(data),
        success: function ()
        {
            $(`#${edit}`).css("display", "none");
            $(`#${display}`).css("display", "grid");
            updateDisplay();
            hideStatusMessage();
        },
        error: function (response)
        {
            response = JSON.parse(response.responseText);
            displayStatusMessage("Error", response.msg, false, response.request_id);
        },
        complete: function ()
        {
            $('section').removeClass('ui loading form');
        }
    })
}

function updateName()
{
    let err = validateFirstName();
    if (!(validateLastName() && err))
        return;

    let data = {};
    data.first_name = $('#first_name').val();
    data.last_name = $('#last_name').val();
    updateProfile(data, 'name-edit', 'name-display');
}

function updateStanding()
{
    if (!validateStanding())
        return;

    let data = {};
    data.standing = $('#standing').val();
    updateProfile(data, 'standing-edit', 'standing-display');
}

function updatePrograms()
{
    let data = {};
    data.majors = $('#major-input-edit').val();
    data.minors = $('#minor-input-edit').val();
    updateProfile(data, 'program-edit', 'program-display');
}

function updateBanner()
{
    if (!validateBannerId())
        return false;

    let data = {};
    data.banner_id = $('#banner_id').val();
    updateProfile(data, 'banner-edit', 'banner-display');
}

function updateGradMonth()
{
    let err = validateGradMonth();
    if (!(validateGradYear() && err))
        return false;

    let data = {};
    data.grad_month = $('#grad_month').val() + $('#year').val();
    updateProfile(data, 'grad-edit', 'grad-display');
}

$(function()
{
    createHandlers('name-edit-button', 'name-cancel-button', 'name-edit', 'name-display');
    createHandlers('standing-edit-button', 'standing-cancel-button', 'standing-edit', 'standing-display');
    createHandlers('program-edit-button', 'program-cancel-button', 'program-edit', 'program-display');
    createHandlers('banner-edit-button', 'banner-cancel-button', 'banner-edit', 'banner-display');
    createHandlers('grad-edit-button', 'grad-cancel-button', 'grad-edit', 'grad-display');

    $('#name-submit-button').on('click', updateName);
    $('#standing-submit-button').on('click', updateStanding);
    $('#program-submit-button').on('click', updatePrograms);
    $('#banner-submit-button').on('click', updateBanner);
    $('#grad-submit-button').on('click', updateGradMonth);

    $('#first_name').on('focusout', validateFirstName);
    $('#last_name').on('focusout', validateLastName);
    $('#standing').on('change', validateStanding);
    $('#banner_id').on('focusout', validateBannerId);
    $('#grad_month').on('change', validateGradMonth);
    $('#year').on('focusout', validateGradYear);

    updateDisplay();

    $('section').removeClass('ui loading form');
})
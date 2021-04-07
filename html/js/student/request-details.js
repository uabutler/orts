function changeAdditional()
{
    if(!validateExplanation())
        return false;

    let reason = $('#reason');
    let explanation = $('#explanation');

    let data = "id=" + REQUEST_ID + "&";
    data += "reason=" + encodeURIComponent(reason.val()) + "&";
    data += "explanation=" + encodeURIComponent(explanation.val());

    $('#additional-form').addClass('loading');

    $.ajax({
        url: '/api/request.php',
        type: 'PUT',
        data: data,
        success: function (data)
        {
            $('#reason-display').parent().children(".text").html(reason.val());
            $('#explanation-display').html(explanation.val());

            $('#additional-edit').css("display", "none");
            $('#additional-display').css("display", "initial");
        },
        complete: function ()
        {
            $('#additional-form').removeClass('loading');
        }
    });
}

function changeCourse()
{
    if (!validateCrn())
        return false;

    let semester = $('#semester');
    let crn = $('#crn');

    let data = "id=" + REQUEST_ID + "&";
    data += "semester=" + encodeURIComponent(semester.val()) + "&";
    data += "crn=" + encodeURIComponent(crn.val());

    $('#course-form').addClass('loading');

    $.ajax({
        url: '/api/request.php',
        type: 'PUT',
        data: data,
        success: function (data)
        {
            $('#semester-display').html(semester.html());

            let section = $('#section').val();
            if (section.length)
                section = '0' + section;
            let course = $('#department').val() + " ";
            course += $('#course_num').val() + " ";
            course += section + ": ";
            course += $('#course_title').val();

            $('#section-display').html(course);

            $('#crn-display').html($('#crn').val());

            $('#course-edit').css("display", "none");
            $('#course-display').css("display", "initial");
        },
        complete: function()
        {
            $('#course-form').removeClass('loading');
        }
    });
}

function createHandlers(edit_button, cancel_button, edit, display)
{
    $(`#${edit_button}`).on("click", function ()
    {
        $(`#${display}`).css("display", "none");
        $(`#${edit}`).css("display", "initial");
    });

    $(`#${cancel_button}`).on("click", function ()
    {
        $(`#${edit}`).css("display", "none");
        $(`#${display}`).css("display", "initial");
    });
}

$(function()
{
    $(document).on("input", ".numeric", function ()
    {
        this.value = this.value.replace(/\D/g, '');
    });

    createHandlers("course-edit-button", "course-cancel-button", "course-edit", "course-display");
    createHandlers("additional-edit-button", "additional-cancel-button", "additional-edit", "additional-display");

    $('#course_num').on("keyup", setSection);
    $('#section').on("keyup", setSection);
    $('#semester').on("change", setSection);
    $('#department').on("change", setSection);

    $('#course_num').on("focusout", function () { setError(validateCourseNum(), "course_num"); })
    $('#section').on("focusout", function () { setError(validateSection(), "section"); })
    $('#explanation').on("focusout", function () { setError(validateExplanation(), "explanation"); })

    $('#additional-submit-button').on("click", changeAdditional)
    $('#course-submit-button').on("click", changeCourse);
})
function changeAdditional()
{
    if(!validateExplanation())
        return false;

    let reason = $('#reason');
    let explanation = $('#explanation');

    let data = {};

    data.id = REQUEST_ID;
    data.reason = reason.val();
    data.explanation = explanation.val();

    console.log(data);

    $('#additional-form').addClass('loading');

    $.ajax({
        url: '/api/student/request.php',
        type: 'PUT',
        data: JSON.stringify(data),
        success: function (data)
        {
            $('#reason-display').parent().children(".text").html(reason.val());
            $('#explanation-display').html(explanation.val());

            $('#additional-edit').css("display", "none");
            $('#additional-display').css("display", "grid");
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

    let data = {};

    data.id = REQUEST_ID;
    data.semester = semester.val();
    data.crn = crn.val();

    $('#course-form').addClass('loading');

    $.ajax({
        url: '/api/student/request.php',
        type: 'PUT',
        data: JSON.stringify(data),
        success: function (data)
        {
            $('#semester-display').html(semester.html());

            let section = $('#section').val();
            if (section.length)
                section = '0' + section;

            let course = $('#department').val() + " ";
            course += $('#course_num').val();

            $('#course-info-display').html(course);
            $('#course-title-display').html($('#course_title').val())
            $('#section-display').html(section);
            $('#crn-display').html($('#crn').val());

            $('#course-edit').css("display", "none");
            $('#course-display').css("display", "grid");
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
        $(`#${edit}`).css("display", "grid");
    });

    $(`#${cancel_button}`).on("click", function ()
    {
        $(`#${edit}`).css("display", "none");
        $(`#${display}`).css("display", "grid");
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

    $('#course_num').on("focusout", function () { setError(validateCourseNum(), "course_num"); });
    $('#section').on("focusout", function () { setError(validateSection(), "section"); });
    $('#explanation').on("focusout", function () { setError(validateExplanation(), "explanation"); });

    $('#additional-submit-button').on("click", changeAdditional)
    $('#course-submit-button').on("click", changeCourse);
})
function reasonSelect()
{
    let out = '<select class="select" id="reason">';

    let first = CURRENT_REASON;
    out += `<option value="${first}">${first}</option>`;
    for(let i = 0; i < REASONS.length; i++)
    {
        if(REASONS[i] !== first)
            out += `<option value="${REASONS[i]}">${REASONS[i]}</option>`;
    }

    out += '</select>';

    return out;
}

function changeAdditional()
{
    if(!validateExplanation())
        return false;

    let reason = $('#reason');
    let explanation = $('#explanation');

    let data = "id=" + REQUEST_ID + "&";
    data += "reason=" + encodeURIComponent(reason.val()) + "&";
    data += "explanation=" + encodeURIComponent(explanation.val());

    reason.prop("disabled", true);
    explanation.prop("readonly", true);

    $.ajax({
        url: '/api/request.php',
        type: 'PUT',
        data: data,
        success: function (data)
        {
            $('#reason-display').html(reason.val());
            $('#explanation-display').html(explanation.val());

            $('div.additional-edit').css("display", "none");
            $('div.additional-display').css("display", "initial");

            reason.prop("disabled", false);
            explanation.prop("readonly", false);
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

    inputEnable(false, false);

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
            course += $('#title').val();

            $('#course-display').html(course);

            $('#crn-display').html($('#crn').val());

            $('div.course-edit').css("display", "none");
            $('div.course-display').css("display", "initial");

            inputEnable(true, false);
        }
    });
}

function createHandlers(edit, display, cancel)
{
    $('button.' + edit).on("click", function ()
    {
        $('div.' + display).css("display", "none");
        $('div.' + edit).css("display", "initial");
        $('.select').select2();
    });

    $('button.' + cancel).on("click", function ()
    {
        $('div.' + edit).css("display", "none");
        $('div.' + display).css("display", "initial");
    });
}

$(function()
{
    $(document).on("input", ".numeric", function ()
    {
        this.value = this.value.replace(/\D/g, '');
    });

    createHandlers("course-edit", "course-display", "course-cancel");
    createHandlers("additional-edit", "additional-display", "additional-cancel");

    $('#course_num').on("keyup", setSection);
    $('#section').on("keyup", setSection);
    $('#semester').on("change", setSection);
    $('#department').on("change", setSection);

    $('#course_num').on("focusout", function () { setError(validateCourseNum(), "course_num"); })
    $('#section').on("focusout", function () { setError(validateSection(), "section"); })
    $('#explanation').on("focusout", function () { setError(validateExplanation(), "section"); })

    $('#reason-cell').html(reasonSelect());

    $('.additional-submit').on("click", changeAdditional)
    $('.course-submit').on("click", changeCourse);
})
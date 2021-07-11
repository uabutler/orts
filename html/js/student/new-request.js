function createRequest()
{
    if(!validate())
        return false;

    $('form').addClass("loading");

    let data = {};

    data.student_id = STUDENT_ID;
    data.semester = $('#semester').val();
    data.crn = $('#crn').val();
    data.reason = $('#reason').val();
    data.explanation = $('#explanation').val();

    $.post("/api/student/request.php", JSON.stringify(data), function(data)
    {
        window.location.href = "/student/request-list.php";
    })
    .fail(function(response)
    {
        response = JSON.parse(response.responseText);
        displayStatusMessage('Error', response.msg, false);
        $('form').removeClass("loading");
    });
}

/*
 * MAIN
 */
$(function ()
{
    $(document).on("input", ".numeric", function ()
    {
        this.value = this.value.replace(/\D/g, '');
    });

    $('#course_num').on("keyup", setSection);
    $('#section').on("keyup", setSection);
    $('#semester').on("change", setSection);
    $('#department').on("change", setSection);

    $('#course_num').on("focusout", validateCourseNum);
    $('#section').on("focusout", validateSection);
    $('#explanation').on("focusout", validateExplanation);
    $('#department').on("change", validateDepartment);
    $('#reason').on("change", validateReason);

    $('#next').on("click", createRequest);
});

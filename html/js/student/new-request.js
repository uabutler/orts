function createRequest()
{
    if(!validate())
        return false;

    inputEnable(false);

    let data = {};

    data.student_id = STUDENT_ID;
    data.semester = $('#semester').val();
    data.crn = $('#crn').val();
    data.reason = $('#reason').val();
    data.explanation = $('#explanation').val();

    $.post("/api/request.php", JSON.stringify(data), function(data)
    {
        window.location.href = "/student/request-list.php";
    })
    .fail(function(response)
    {
        console.log("Could not add request");
        console.log(data);
        console.log(response)
        // TODO: Display error to user
        inputEnable(true);
    });
}

/*
 * MAIN
 */
$(function ()
{
    $('.select').select2();

    $(document).on("input", ".numeric", function ()
    {
        this.value = this.value.replace(/\D/g, '');
    });

    $('#course_num').on("keyup", setSection);
    $('#section').on("keyup", setSection);
    $('#semester').on("change", setSection);
    $('#department').on("change", setSection);

    $('#course_num').on("focusout", function () { setError(validateCourseNum(), "course_num"); })
    $('#section').on("focusout", function () { setError(validateSection(), "section"); })
    $('#explanation').on("focusout", function () { setError(validateExplanation(), "explanation"); })

    $('#next').on("click", createRequest);
});

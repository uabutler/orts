/*
 * INPUT VALIDATION
 */
function validateCourseNum() { return validateRegex("course_num", /^\d{3}$/); }
function validateSection() { return validateRegex("section", /^\d{1,2}$/) && $("#section").val() > 0; }
function validateExplanation() { return validateNotEmpty("explanation"); }
function validateCrn() { return validateNotEmpty("crn"); }

function validate()
{
    // Return true iff all are true
    switch(false)
    {
        case validateExplanation():
        case validateCrn(): // This includes course number and section
            return false;
        default:
            return true;
    }
}

/*
 * Get the section CRN and Title from the server
 */
function setSection()
{
    if(!(validateCourseNum() && validateSection()))
    {
        $('#crn').val("");
        $('#title').val("");
        return;
    }

    let data = {};

    data.semester = $('#semester').val();
    data.department = $('#department').val();
    data.course_num = $('#course_num').val();
    data.section = parseInt($('#section').val(), 10);

    let request = $.get("/api/section.php", data, function (data, status, xhr)
    {
        if(status === "success")
        {
            $('#crn').val(data.crn);
            $('#title').val(data.course.title);
        }
        else
        {
            $('#crn').val("");
            $('#title').val("");
        }
    }, "json");
}

/*
 * INPUT DISABLE
 */
function inputEnable(bool)
{
    bool = !bool;

    $('#course_num').attr("readonly", bool);
    $('#section').attr("readonly", bool);

    $('#semester').attr("disabled", bool);
    $('#department').attr("disabled", bool);
    $('#reason').attr("disabled", bool);

    $('#explanation').attr("readonly", bool);
}

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

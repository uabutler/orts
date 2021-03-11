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
    $('#crn').val("");
    $('#title').val("");

    if(!(validateCourseNum() && validateSection()))
        return;

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
function inputEnable(bool, explain = true)
{
    bool = !bool;

    $('#course_num').attr("readonly", bool);
    $('#section').attr("readonly", bool);

    $('#semester').attr("disabled", bool);
    $('#department').attr("disabled", bool);

    if (explain)
    {
        $('#reason').attr("disabled", bool);
        $('#explanation').attr("readonly", bool);
    }
}


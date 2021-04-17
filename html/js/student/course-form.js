/*
 * INPUT VALIDATION
 */

let COURSE_REGEX = /^\d{3}$/;
let SECTION_REGEX = /^\d{1,2}$/;

function validateCourseNum() { return setError(validateRegex("course_num", COURSE_REGEX), "course_num"); }
function validateSection() { return setError(validateRegex("section", SECTION_REGEX) && $("#section").val() > 0, "section"); }
function validateExplanation() { return setError(validateNotEmpty("explanation"), "explanation"); }
function validateDepartment() { return setError(validateNotEmpty("department"), "department"); }
function validateReason() { return setError(validateNotEmpty("reason"), "reason"); }

// We only use this to see if we can submit, we don't want to set it as erroneous
function validateCrn() { return validateNotEmpty("crn"); }

function validate()
{
    let ret = validateCourseNum();
    ret = validateSection() && ret;
    ret = validateExplanation() && ret;
    ret = validateDepartment() && ret;
    ret = validateReason() && ret;
    ret = validateCrn() && ret;

    return ret;
}

/*
 * Get the section CRN and Title from the server
 */
function setSection()
{
    let crn = $('#crn');
    let title = $('#course_title');

    crn.val("");
    title.val("");

    if(!(validateNotEmpty("department") &&
        validateRegex("course_num", COURSE_REGEX) &&
        validateRegex("section", SECTION_REGEX) &&
        $('#section').val() > 0))
        return;

    crn.parent().addClass("loading");
    title.parent().addClass("loading");

    let data = {};

    data.semester = $('#semester').val();
    data.department = $('#department').val();
    data.course_num = $('#course_num').val();
    data.section = parseInt($('#section').val(), 10);

    let request = $.get("/api/truman/section.php", data, function (data, status, xhr)
    {
        crn.parent().removeClass("loading");
        title.parent().removeClass("loading");

        if(status === "success")
        {
            crn.val(data.crn);
            title.val(data.course.title);
        }
        else
        {
            crn.val("");
            title.val("");
        }
    }, "json").fail(function ()
    {
        crn.parent().removeClass("loading");
        title.parent().removeClass("loading");
    });
}
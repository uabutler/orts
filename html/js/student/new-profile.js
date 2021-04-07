/*
 * INPUT VALIDATION
 */
function validateBannerId() { return setError(validateRegex("banner_id", /^001\d{6}$/), "banner_id"); }
function validateGradYear() { return setError(validateRegex("year", /^20[2-9]\d$/), "year"); }
function validateFirstName() { return setError(validateNotEmpty("first_name"), "first_name"); }
function validateLastName() { return setError(validateNotEmpty("last_name"), "last_name"); }
function validateGradMonth() { return setError(validateNotEmpty("grad_month"), "grad_month"); }
function validateStanding() { return setError(validateNotEmpty("standing"), "standing"); }

function validate()
{
    let ret = validateBannerId();
    ret = validateGradMonth() && ret;
    ret = validateGradYear() && ret;
    ret = validateFirstName() && ret;
    ret = validateLastName() && ret;
    ret = validateStanding() && ret;

    return ret;
}

/*
 * INPUT DISABLE
 */
function inputEnable(bool)
{
    bool = !bool;

    $('#first_name').attr("readonly", bool);
    $('#last_name').attr("readonly", bool);
    $('#banner_id').attr("readonly", bool);
    $('#year').attr("readonly", bool);

    $('#standing').attr("disabled", bool);
    $('#grad_month').attr("disabled", bool);
    $('#majors').attr("disabled", bool);
    $('#minors').attr("disabled", bool);
}

function createStudent()
{
    if(!validate())
        return false;

    $('form').addClass("loading");

    let data = {};

    data.email = STUDENT_EMAIL;
    data.first_name = $('#first_name').val();
    data.last_name = $('#last_name').val();
    data.banner_id = $('#banner_id').val();
    data.grad_month = $('#grad_month').val() + $('#year').val();
    data.standing = $('#standing').val();
    data.majors = $('#majors').val();
    data.minors = $('#minors').val();


    $.post("/api/student.php", JSON.stringify(data), function(data)
    {
        console.log("GOOD");
        console.log(data);
        window.location.replace('/student/new-request.php');
    })
    .fail(function(response)
    {
        console.log("BAD");
        $('form').removeClass("loading");
    });
}

/*
 * MAIN
 */
$(function ()
{
    $('.select').select2({minimumResultsForSearch: Infinity});

    $('#banner_id').on("focusout", validateBannerId);
    $('#year').on("focusout", validateGradYear);
    $('#first_name').on("focusout", validateFirstName);
    $('#last_name').on("focusout", validateLastName);

    $('#standing').on("change", validateStanding);
    $('#grad_month').on("change", validateGradMonth);

    $('#next').on("click", createStudent);
});

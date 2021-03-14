/*
 * INPUT VALIDATION
 */
function validateBannerId() { return validateRegex("banner_id", /^001\d{6}$/); }
function validateGradMonth() { return validateRegex("year", /^20[2-9]\d$/); }
function validateFirstName() { return validateNotEmpty("first_name"); }
function validateLastName() { return validateNotEmpty("last_name"); }

function validate()
{
    // Return true iff all are true
    switch(false)
    {
        case validateBannerId():
        case validateGradMonth():
        case validateFirstName():
        case validateLastName():
            return false;
        default:
            return true;
    }
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

    inputEnable(false);

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
        inputEnable(true);
    });
}

/*
 * MAIN
 */
$(function ()
{
    $('.select').select2({minimumResultsForSearch: Infinity});

    $(document).on("input", ".numeric", function ()
    {
        this.value = this.value.replace(/\D/g, '');
    });

    $('#banner_id').on("focusout", function () { setError(validateBannerId(), "banner_id"); })
    $('#year').on("focusout", function () { setError(validateGradMonth(), "year"); })
    $('#first_name').on("focusout", function () { setError(validateFirstName(), "first_name"); })
    $('#last_name').on("focusout", function () { setError(validateLastName(), "last_name"); })

    $('#next').on("click", createStudent);
});

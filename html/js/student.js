/*
 * INPUT VALIDATION
 */
function validateBannerId() { return validateRegex("banner_id", /^001\d{6}$/); }
function validateGradMonth() { return validateRegex("grad_month", /(0[1-9]|1[0-2])\/20[2-9]\d/); }
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
 * Create error notice
 */
function setError(valid, element_name)
{
    let element = $(`input[name="${element_name}"]`);

    if(valid)
        element.removeClass("error");
    else
        element.addClass("error");
}

/*
 * INPUT DISABLE
 */
function inputEnable(bool)
{
    bool = !bool;

    $('input[name="first_name"]').attr("readonly", bool);
    $('input[name="last_name"]').attr("readonly", bool);
    $('input[name="banner_id"]').attr("readonly", bool);
    $('input[name="grad_month"]').attr("readonly", bool);

    $('select[name="standing"]').attr("disabled", bool);
    $('select[name="majors[]"]').attr("disabled", bool);
    $('select[name="minors[]"]').attr("disabled", bool);
}

function createStudent()
{
    if(!validate())
        return false;

    inputEnable(false);

    let data = {};

    data.email = STUDENT_EMAIL;
    data.first_name = $('input[name="first_name"]').val();
    data.last_name = $('input[name="last_name"]').val();
    data.banner_id = $('input[name="banner_id"]').val();
    data.grad_month = $('input[name="grad_month"]').val();
    data.standing = $('select[name="standing"]').val();
    data.majors = $('select[name="majors[]"]').val();
    data.minors = $('select[name="minors[]"]').val();


    $.post("api/student.php", JSON.stringify(data), function(data)
    {
        console.log("GOOD");
        console.log(data);
        window.location.replace('request.php?id=' + data);
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
    $('.select').select2();

    $(document).on("input", ".numeric", function ()
    {
        this.value = this.value.replace(/\D/g, '');
    });

    $('input[name="banner_id"]').on("focusout", function () { setError(validateBannerId(), "banner_id"); })
    $('input[name="grad_month"]').on("focusout", function () { setError(validateGradMonth(), "grad_month"); })
    $('input[name="first_name"]').on("focusout", function () { setError(validateFirstName(), "first_name"); })
    $('input[name="last_name"]').on("focusout", function () { setError(validateLastName(), "last_name"); })

    $('#next').on("click", createStudent);
});

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

function createStudent()
{
    if(!validate())
        return false;

    $('form').addClass("loading");

    let data = {};

    data.first_name = $('#first_name').val();
    data.last_name = $('#last_name').val();
    data.banner_id = $('#banner_id').val();
    data.grad_month = $('#grad_month').val() + $('#year').val();
    data.standing = $('#standing').val();
    data.majors = $('#majors').val();
    data.minors = $('#minors').val();


    $.post("/api/student/student.php", JSON.stringify(data), function(data)
    {
        window.location.replace('/student/new-request.php');
    })
    .fail(function(response)
    {
        response = JSON.parse(response.responseText);
        displayStatusMessage('Error', response.msg, false, response.request_id);
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

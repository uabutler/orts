/*
 * Returns the equivalent html for a given status code
 */
function getStatusHtml(request)
{
    switch (request.status)
    {
        case 'Received':
            return '<i class="material-icons" style="color:orange">warning</i> Received';
        case 'Approved':
            if (request.banner)
                return '<i class="material-icons" style="color:green">done_all</i> Approved: In Banner';
            else
                return '<i class="material-icons" style="color:green">done</i> Approved';
        case 'Provisionally Approved':
            if (request.banner)
                return '<i class="material-icons" style="color:yellowgreen">done_all</i> Provisionally Approved: In Banner';
            else
                return '<i class="material-icons" style="color:yellowgreen">done</i> Provisionally Approved';
        case 'Denied':
            return '<i class="material-icons" style="color:red">cancel</i> Denied';
        case 'Requires Faculty Approval':
            return '<i class="material-icons" style="color:orange">warning</i> Requires Faculty Approval';
        default:
            return 'ERROR: STATUS NOT RECOGNIZED';
    }
}

/*
 * INPUT VALIDATION
 */
function validateRegex(element_name, regex)
{
    let element = $(`#${element_name}`);

    return regex.test(String(element.val()));
}

function validateNotEmpty(element_name)
{
    let element = $(`#${element_name}`);

    return element.val() !== "";
}

/*
 * Create error notice
 */
function setError(valid, element_name)
{
    let element = $(`#${element_name}`).parent();

    if(valid)
        element.removeClass("error");
    else
        element.addClass("error");

    return valid;
}

$(function ()
{
    $('select.ui.dropdown').dropdown();

    $('.message .close').on('click', function()
    {
        $(this).closest('.message').transition('fade');
    });

    $('.numeric').on("input", function ()
    {
        this.value = this.value.replace(/\D/g, '');
    });

});
function changeStatus()
{
    let data = "id=" + REQUEST_ID + "&";
    data += "status=" + encodeURIComponent($('#status_input').val()) + "&";
    data += "banner=" + $('#banner').is(":checked") + "&";
    data += "justification=" + encodeURIComponent($('#justification').val());

    $.ajax({
        url: '/api/request.php',
        type: 'PUT',
        data: data,
        timeout: 5000,
        success: function (data)
        {
            $('#status_info').html(getStatusHtml({status:$('#status_input').val(), banner:$('#banner').is(":checked")}));
            setMessage("Success", "The request status has been successfully updated", true);
        },
        error: function ()
        {
            setMessage("Error", "We weren't able to add", false);
        }
    });
}

function setMessage(header, body, success)
{
    let element = $(".ui.message");
    element.removeClass("success", "error");

    if(success)
        element.addClass("success");
    else
        element.addClass("error");

    element.children(".header").html(header);
    element.children("p").html(body);

    element.removeClass("hidden");
}


$(function ()
{
    $(`#status_input option[value="${REQUEST_STATUS}"]`).prop("selected", true);
    $('#submit').on("click", changeStatus);
})

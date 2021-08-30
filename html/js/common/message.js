function displayStatusMessage(header, message, status, request_id = null)
{
    console.log("Message: " + message)
    console.log("Request: " + request_id)

    let content = message
    if (request_id) content += "<small><br>Request ID: " + request_id + "</small>";

    $('#status-message-header').html(header);
    $('#status-message-content').html(content);

    let element = $('#status-message');
    let icon = $('#status-message-icon');

    element.removeClass('positive negative');
    icon.removeClass('times check')

    if (status)
    {
        element.addClass('positive');
        icon.addClass('check');
    }
    else
    {
        element.addClass('negative');
        icon.addClass('times');
    }

    element.removeClass('hidden');
}

function hideStatusMessage()
{
    $('#status-message').addClass('hidden');
}
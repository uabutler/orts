function displayStatusMessage(header, message, status)
{
    $('#status-message-header').html(header);
    $('#status-message-content').html(message);

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
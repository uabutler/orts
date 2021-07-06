function setStatusWarning(type, event)
{
    let select = event.children('select');
    let tableCell = event.parent();
    let statusIcon = tableCell.find(`.${type}-status-icon`);

    tableCell.removeClass('warning negative positive');
    statusIcon.removeClass('exclamation circle triangle icon');

    if (select.val() !== select.data('original'))
    {
        console.log(select.val());

        statusIcon.removeClass('hidden');

        if (select.val() === 'archive')
        {
            tableCell.addClass('warning')
            statusIcon.addClass('exclamation circle icon');
        }

        if (select.val() === 'delete')
        {
            tableCell.addClass('negative')
            statusIcon.addClass('exclamation triangle icon');
        }
    }
    else
    {
        statusIcon.addClass('hidden');
    }

    if ($(`.${type}-status-icon.icon`).length)
        $(`#${type}-update-button`).removeClass('hidden')
    else
        $(`#${type}-update-button`).addClass('hidden')
}
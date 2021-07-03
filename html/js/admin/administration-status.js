function setStatusWarning()
{
    let select = $(this).children('select');
    let tableCell = $(this).parent();
    let statusIcon = tableCell.find('.semester-status-icon');

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

    if ($('.semester-status-icon.icon').length)
        $('#semester-update-button').removeClass('hidden')
    else
        $('#semester-update-button').addClass('hidden')
}

function updateSemesters()
{
    $('#semester-primary-content-display').addClass('ui loading form');

    let data = [];

    $('.semester-status-icon.icon').each(function()
    {
        let item = {};

        item.id = $(this).parents('tr').data('value');
        if ($(this).hasClass('circle'))
            item.archive = true;
        else if ($(this).hasClass('triangle'))
            item.delete = true;

        data.push(item);
    });

    $.ajax({
        url: '/api/admin/semester.php',
        method: 'PUT',
        data: JSON.stringify(data),
        success: function()
        {
            updateSemesterTable();
            $('#semester-primary-content-display').removeClass('ui loading form');
        }
    })
}


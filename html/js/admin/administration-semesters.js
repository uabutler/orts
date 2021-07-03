function updateSemesterTable()
{
    $.ajax({
        url: '/api/admin/semester.php',
        method: 'GET',
        success: function(data)
        {
            data = JSON.parse(data);

            if (data.length === 0)
            {
                $('#semester-primary-content-display').html("Nothing to show");
                return;
            }

            let table = `<table class="ui celled structured table">
                    <thead>
                    <tr class="center aligned">
                        <th>Name</th>
                        <th>Code</th>
                        <th>Offerings</th>
                        <th>
                            Status
                            <i id="status-info-icon" class="exclamation circle icon link" data-content="Archiving a semester will also archive all requests. Similarly, deleting a semester will delete all requests. Deletion cannot be undone. Activating an archived semester will not reactivate any requests." data-variation="wide"></i>
                        </th>
                    </tr>
                    </thead>
                    <tbody>`;

            for (const semester of data)
            {
                table += `<tr data-value="${semester.id}">
                        <td>${semester.description}</td>
                        <td>${semester.semester}</td>
                        <td><button class="ui button">View</button></td>
                        <td class="semester-status-table-cell">
                            <div>
                                <i class="semester-status-icon hidden"></i>
                            </div>`;


                if (semester.active)
                {
                    table +=`<select data-original="active" class="semester-status-select ui dropdown fluid">
                            <option value="active">Active</option>
                            <option value="archive">Archive</option>`;
                }
                else
                {
                    table +=`<select data-original="archive" class="semester-status-select ui dropdown fluid">
                            <option value="archive">Archive</option>`;

                }

                    table += `<option value="delete">Delete</option>
                            </select>
                        </td>
                    </tr>`;
            }

            table += `</tbody>
                </table>
                <button id="semester-update-button" class="hidden ui right floated button">Update</button>`;

            $('#semester-primary-content-display').html(table);

            $('.ui.dropdown').dropdown();
            $('#status-info-icon').popup();
            $('.semester-status-select').on('change', setStatusWarning);
            $('#semester-update-button').on('click', updateSemesters);
        }
    });
}

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

function enableSemesterPopup(enabled)
{
    $('#semester-description').prop('disabled', !enabled);
    $('#semester-code').prop('disabled', !enabled);
    $('#new-semester-submit-button').prop('disabled', !enabled);
    $('#new-semester-cancel-button').prop('disabled', !enabled);
}

function showSemesterPopup()
{
    enableSemesterPopup(true);
    $('#semester-description').val('');
    $('#semester-code').val('');
    $('#new-semester-popup').modal('show');
}

function cancelSemesterPopup()
{
    $('#new-semester-popup').modal('hide');
}

function submitSemester()
{
    // TODO: Validate here
    enableSemesterPopup(false);
    let data = {};

    data.description = $('#semester-description').val();
    data.semester = $('#semester-code').val();

    $.ajax({
        url: '/api/admin/semester.php',
        method: 'POST',
        data: JSON.stringify(data),
        success: function ()
        {
            cancelSemesterPopup();
            updateSemesterTable();
        }
    })
}

$(function()
{
    $('#new-semester-popup').modal({closable: false});
    updateSemesterTable();

    $('#new-semester-popup-button').on('click', showSemesterPopup);
    $('#new-semester-cancel-button').on('click', cancelSemesterPopup);
    $('#new-semester-submit-button').on('click', submitSemester);
});
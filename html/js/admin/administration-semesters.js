function validateSemesterDescription() { return setError(validateRegex('semester-description', /\S+/), 'semester-description'); }
function validateSemesterCode() { return setError(validateRegex('semester-code', /^\d{6}$/), 'semester-code'); }

function updateSemesterTable()
{
    $.ajax({
        url: '/api/admin/semester.php',
        method: 'GET',
        success: function(data)
        {
            data = JSON.parse(data).response;

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
                table += `<tr data-value="${semester.id}" data-semester="${semester.description}">
                        <td>${semester.description}</td>
                        <td>${semester.semester}</td>
                        <td><button class="view-sections-button ui button">View</button></td>
                        <td class="status-table-cell">
                            <div>
                                <i class="semester-status-icon hidden"></i>
                            </div>`;


                if (semester.active)
                {
                    table +=`<select data-original="active" class="semester-status-select ui dropdown fluid">
                            <option value="active">Active</option>
                            <option value="archive">Inactive</option>`;
                }
                else
                {
                    table +=`<select data-original="archive" class="semester-status-select ui dropdown fluid">
                            <option value="archive">Inactive</option>`;

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
            $('.semester-status-select').on('change', function() { setStatusWarning('semester', $(this)) });
            $('#semester-update-button').on('click', updateSemesters);
            $('.view-sections-button').on('click', function () { switchToSection($(this)) });
        },
        error: function (response)
        {
            response = JSON.parse(response.responseText);
            displayStatusMessage("Error", response.msg, false, response.request_id);
        }
    });
}

function updateSemesters()
{
    $('#semester-primary-content-display').addClass('ui loading form');

    let data = [];

    $('.semester-status-icon.icon').each(function() { createStatusUpdate(data, $(this)) });

    $.ajax({
        url: '/api/admin/semester.php',
        method: 'PUT',
        data: JSON.stringify(data),
        complete: updateSemesterTable,
        success: function() { $('#semester-primary-content-display').removeClass('ui loading form'); },
        error: function (response)
        {
            response = JSON.parse(response.responseText);
            displayStatusMessage("Error", response.msg, false, response.request_id);
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

    setError(true, 'semester-description');
    setError(true, 'semester-code');

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
    let err = validateSemesterDescription();

    if (!(validateSemesterCode() && err))
        return;

    enableSemesterPopup(false);
    let data = {};

    data.description = $('#semester-description').val();
    data.semester = $('#semester-code').val();

    $.ajax({
        url: '/api/admin/semester.php',
        method: 'POST',
        data: JSON.stringify(data),
        success: cancelSemesterPopup,
        complete: updateSemesterTable,
        error: function (response)
        {
            response = JSON.parse(response.responseText);
            displayStatusMessage("Error", response.msg, false, response.request_id);
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

    // Data validation
    $('#semester-description').on('focusout', validateSemesterDescription);
    $('#semester-code').on('focusout', validateSemesterCode);
});
function validatePrograms() { return setError(validateRegex('program-input', /\S+/), 'program-input'); }

function updateProgramTable(type)
{
    $.ajax({
        url: '/api/admin/' + type +'s.php',
        method: 'GET',
        success: function(data)
        {
            data = JSON.parse(data).response;

            if (data.length === 0)
            {
                $(`#${type}s-primary-content-display`).html("Nothing to show");
                return;
            }

            let table = `<table class="ui celled structured table">
                    <thead>
                    <tr class="center aligned">
                        <th>Name</th>
                        <th>
                            Status
                            <i class="status-info-icon exclamation circle icon link" data-content="If a ${type} is inactive, students cannot add it to their list of ${type}, but it will not be removed if a student has already added it." data-variation="wide"></i>
                        </th>
                    </tr>
                    </thead>
                    <tbody>`;

            for (const program of data)
            {
                table += `<tr data-value="${program.id}">
                        <td>${program.name}</td>
                        <td class="status-table-cell">
                            <div>
                                <i class="${type}-status-icon hidden"></i>
                            </div>`;


                if (program.active)
                {
                    table +=`<select data-original="active" class="${type}-status-select ui dropdown fluid">
                            <option value="active">Active</option>
                            <option value="archive">Inactive</option>`;
                }
                else
                {
                    table +=`<select data-original="archive" class="${type}-status-select ui dropdown fluid">
                            <option value="archive">Inactive</option>`;

                }

                table += `<option value="delete">Delete</option>
                            </select>
                        </td>
                    </tr>`;
            }

            table += `</tbody>
                </table>
                <button id="${type}-update-button" class="hidden ui right floated button">Update</button>`;

            $(`#${type}s-primary-content-display`).html(table);

            $('.ui.dropdown').dropdown();
            $('.status-info-icon').popup();

            // TODO: Update
            $(`.${type}-status-select`).on('change', function() { setStatusWarning(type, $(this)) });
            $(`#${type}-update-button`).on('click', updateSemesters);

            $('#major-update-button').on('click', function() { updatePrograms('major') });
            $('#minor-update-button').on('click', function() { updatePrograms('minor') });
        }
    });
}

function enableProgramPopup(enabled)
{
    $('#program-input').prop('disabled', !enabled);
    $('#new-program-submit-button').prop('disabled', !enabled);
    $('#new-program-cancel-button').prop('disabled', !enabled);
}

function showProgramPopup(type)
{
    let lower_case_type = type.toLowerCase();

    setError(true, 'program-input');

    $('#new-program-popup-header').html(`New ${type}s`);
    $('#program-administration').data('type', lower_case_type);
    $('#new-program-popup-description').html(`Place each ${lower_case_type} on a separate line`);

    enableProgramPopup(true);
    $('#program-input').val('');
    $('#new-program-popup').modal('show');
}

function cancelProgramPopup()
{
    $('#new-program-popup').modal('hide');
}

function submitPrograms()
{
    if (!validatePrograms())
        return;

    let type = $('#program-administration').data('type');

    enableProgramPopup(false);

    let input = $('#program-input').val().split('\n');
    let data = [];

    for (let program of input)
    {
        program = program.trim();

        if (program !== "")
            data.push(program)
    }

    $.ajax({
        url: `/api/admin/${type}s.php`,
        method: 'POST',
        data: JSON.stringify(data),
        success: function()
        {
            updateProgramTable(type)
            cancelProgramPopup();
        }
    })
}

function updatePrograms(type)
{
    if (!validatePrograms())
        return;

    $('#program-primary-content-display').addClass('ui loading form');

    let data = [];

    $(`.${type}-status-icon.icon`).each(function() { createStatusUpdate(data, $(this)) });

    $.ajax({
        url: `/api/admin/${type}s.php`,
        method: 'PUT',
        data: JSON.stringify(data),
        success: function()
        {
            updateProgramTable(type)
            $('#program-primary-content-display').removeClass('ui loading form');
        }
    })
}

$(function()
{
    $('#new-program-popup').modal({closable: false});

    updateProgramTable('major');
    updateProgramTable('minor');

    $('#new-majors-popup-button').on('click', function() { showProgramPopup('Major') });
    $('#new-minors-popup-button').on('click', function() { showProgramPopup('Minor') });
    $('#new-program-cancel-button').on('click', cancelProgramPopup);
    $('#new-program-submit-button').on('click', submitPrograms);

    // Data validation
    $('#program-input').on('focusout', validatePrograms);
});
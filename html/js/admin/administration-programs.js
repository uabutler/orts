function updateProgramTable(type)
{
    $.ajax({
        url: '/api/admin/' + type +'s.php',
        method: 'GET',
        success: function(data)
        {
            data = JSON.parse(data);

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
                        <td class="semester-status-table-cell">
                            <div>
                                <i class="semester-status-icon hidden"></i>
                            </div>`;


                if (program.active)
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

            $(`#${type}s-primary-content-display`).html(table);

            $('.ui.dropdown').dropdown();
            $('.status-info-icon').popup();
            // TODO: Update
            $('.semester-status-select').on('change', setStatusWarning);
            $('#semester-update-button').on('click', updateSemesters);
        }
    });
}

function enableSemesterPopup(enabled)
{
    $('#program-input').prop('disabled', !enabled);
    $('#new-program-submit-button').prop('disabled', !enabled);
    $('#new-program-cancel-button').prop('disabled', !enabled);
}

function showProgramPopup(type)
{
    let lower_case_type = type.toLowerCase();

    $('#new-program-popup-header').html(`New ${type}s`);
    $('#program-administration').data('type', lower_case_type);
    $('#new-program-popup-description').html(`Place each ${lower_case_type} on a separate line`);

    enableSemesterPopup(true);
    $('#program-input').val('');
    $('#new-program-popup').modal('show');
}

function showMajorPopup()
{
    showProgramPopup('Major');
}

function showMinorPopup()
{
    showProgramPopup('Minor');
}

function cancelProgramPopup()
{
    $('#new-program-popup').modal('hide');
}

$(function()
{
    $('#new-program-popup').modal({closable: false});

    updateProgramTable('major');
    updateProgramTable('minor');

    $('#new-majors-popup-button').on('click', showMajorPopup);
    $('#new-minors-popup-button').on('click', showMinorPopup);
    $('#new-program-cancel-button').on('click', cancelProgramPopup);
});
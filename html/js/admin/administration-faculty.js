EMAIL_REGEX = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+$/;

function validateFacultyFirstName() { return setError(validateRegex('faculty-first-name', /\S+/), 'faculty-first-name'); }
function validateFacultyLastName() { return setError(validateRegex('faculty-last-name', /\S+/), 'faculty-last-name'); }

function validateFacultyEmail()
{
    let element = $('#faculty-email');
    let parent = element.parent().parent();

    if (EMAIL_REGEX.test(element.val()))
        parent.removeClass('error');
    else
        parent.addClass('error');

    return EMAIL_REGEX.test(element.val());
}

function updateFacultyTable()
{
    $.ajax({
        url: '/api/admin/faculty.php',
        method: 'GET',
        success: function(data)
        {
            data = JSON.parse(data).response

            let table = ` <table class="ui celled structured table">
                <thead>
                <tr class="center aligned">
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Default</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>`;

            for (const faculty of data)
            {
                table += `
                    <tr>
                        <td>${faculty.first_name}</td>
                        <td>${faculty.last_name}</td>
                        <td>${faculty.email}@truman.edu</td>`;

                if (faculty.default)
                {
                    table += `
                        <td class="center aligned"><i class="check circle icon"></i></td>
                        <td>
                        <div class="ui dropdown disabled file-actions">
                            <div class="text">Options</div>
                            <i class="dropdown icon"></i>
                        </div>`;
                }
                else
                {
                    table += `
                        <td class="center aligned"></td>
                        <td>
                            <div class="ui dropdown faculty-actions" tabIndex="0">
                                    <div class="text">Options</div>
                                    <i class="dropdown icon"></i>
                                    <div data-value="${faculty.id}" class="menu transition hidden" tabIndex="-1">
                                        <div class="item make-default-faculty">
                                            <i class="check circle icon"></i>
                                            Make Default
                                        </div>
                                        <div class="item delete-faculty">
                                            <i class="trash icon"></i>
                                            Delete
                                        </div>
                                    </div>
                                </div>`;
                }

                table += `
                        </td>
                    </tr>`;
            }

            table += `
                </tbody>
            </table>`;

            $('#faculty-primary-content-display').html(table);
            $('.ui.dropdown').dropdown({action: 'hide'});
            $('.make-default-faculty').on('click', makeDefaultFaculty);
            $('.delete-faculty').on('click', deleteFaculty);
        }
    });
}

function makeDefaultFaculty()
{
    let id = $(this).parent().data('value');

    $('#faculty-default-confirmation')
        .modal({
            closable: false,
            onApprove: function()
            {
                let data = {};

                data.id = id;
                data.make_default = true;

                $.ajax({
                    url: '/api/admin/faculty.php',
                    method: 'PUT',
                    data: JSON.stringify(data),
                    success: updateFacultyTable
                })
            }
        })
        .modal('show');
}

function deleteFaculty()
{
    let id = $(this).parent().data('value');

    $('#faculty-delete-confirmation')
        .modal({
            closable: false,
            onApprove: function()
            {
                let data = {};

                data.id = id;
                data.delete = true;

                $.ajax({
                    url: '/api/admin/faculty.php',
                    method: 'PUT',
                    data: JSON.stringify(data),
                    success: updateFacultyTable
                })
            }
        })
        .modal('show');
}

function enableFacultyPopup(enabled)
{
    $('#faculty-first-name').prop('disabled', !enabled);
    $('#faculty-last-name').prop('disabled', !enabled);
    $('#faculty-email').prop('disabled', !enabled);
    $('#new-faculty-submit-button').prop('disabled', !enabled);
    $('#new-faculty-cancel-button').prop('disabled', !enabled);
}

function showFacultyPopup()
{
    enableFacultyPopup(true);

    $('#faculty-email').parent().parent().removeClass('error');
    setError(true, 'faculty-first-name');
    setError(true, 'faculty-last-name');

    $('#faculty-first-name').val('');
    $('#faculty-last-name').val('');
    $('#faculty-email').val('');
    $('#new-faculty-popup').modal('show');
}

function cancelFacultyPopup()
{
    $('#new-faculty-popup').modal('hide');
}

function submitFaculty()
{
    let err = validateFacultyFirstName();
    err = validateFacultyLastName() && err;
    err = validateFacultyEmail() && err;

    if (!err)
        return;

    enableFacultyPopup(false);

    let data = {};

    data.first_name = $('#faculty-first-name').val();
    data.last_name = $('#faculty-last-name').val();
    data.email = $('#faculty-email').val();

    $.ajax({
        url: '/api/admin/faculty.php',
        method: 'POST',
        data: JSON.stringify(data),
        success: function()
        {
            cancelFacultyPopup();
            updateFacultyTable();
        }
    })
}

$(function()
{
    $('#new-faculty-popup').modal({closable: false});
    updateFacultyTable();

    $('#new-faculty-popup-button').on('click', showFacultyPopup);
    $('#new-faculty-cancel-button').on('click', cancelFacultyPopup);
    $('#new-faculty-submit-button').on('click', submitFaculty);

    // Data validation
    $('#faculty-first-name').on('focusout', validateFacultyFirstName);
    $('#faculty-last-name').on('focusout', validateFacultyLastName);
    $('#faculty-email').on('focusout', validateFacultyEmail);
});
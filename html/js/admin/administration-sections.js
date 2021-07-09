function switchToSection(event)
{
    let semester_id = event.parents('tr').data('value');
    let semester_name = event.parents('tr').data('semester');

    Cookies.set('semester_id', semester_id);
    Cookies.set('semester_name', semester_name);

    openSection();
}

function openSection()
{
    Cookies.set('admin-page', 'section');

    $('#section-semester-header').html(Cookies.get('semester_name'));
    $(`#semester-menu-button`).addClass('active');
    $('.administration-section').addClass('hidden');
    $('#section-administration').removeClass('hidden');

    updateSectionTable(Cookies.get('semester_id'));
}


function updateSectionTable(semester)
{
    let data = 'id=' + semester;

    $.ajax({
        url: '/api/admin/sections.php',
        method: 'GET',
        data: data,
        success: function(data)
        {
            data = JSON.parse(data);

            if (data.length === 0)
            {
                $('#section-primary-content-display').html("Nothing to show");
                return;
            }

            let table = `<table class="ui celled structured table">
                    <thead>
                    <tr class="center aligned">
                        <th>Code</th>
                        <th>Title</th>
                        <th>Section</th>
                        <th>CRN</th>
                        <th>
                            Status
                            <i id="status-info-icon" class="exclamation circle icon link" data-content="" data-variation="wide"></i>
                        </th>
                    </tr>
                    </thead>
                    <tbody>`;

            for (const section of data)
            {
                // Build a row of the table
                table += `<tr data-value="${section.id}">
                        <td>${section.course.department.department} ${section.course.course_num}</td>
                        <td>${section.course.title}</td>
                        <td>${section.section}</td>
                        <td>${section.crn}</td>
                        <td class="status-table-cell">
                            <div>
                                <i class="section-status-icon hidden"></i>
                            </div>`;


                if (section.active)
                {
                    table +=`<select data-original="active" class="section-status-select ui dropdown fluid">
                            <option value="active">Active</option>
                            <option value="archive">Inactive</option>`;
                }
                else
                {
                    table +=`<select data-original="archive" class="section-status-select ui dropdown fluid">
                            <option value="archive">Inactive</option>`;

                }

                table += `<option value="delete">Delete</option>
                            </select>
                        </td>
                    </tr>`;
            }

            table += `</tbody>
                </table>
                <button id="section-update-button" class="hidden ui right floated button">Update</button>`;

            $('#section-primary-content-display').html(table);

            $('.ui.dropdown').dropdown();
            $('#status-info-icon').popup();
            $('.section-status-select').on('change', function() { setStatusWarning('section', $(this)) });
            $('#section-update-button').on('click', updateSection);
        }
    });
}

function enableSectionPopup(enabled)
{
    $('#section-department-input').prop('disabled', !enabled);
    $('#section-course-input').prop('disabled', !enabled);
    $('#section-title-input').prop('disabled', !enabled);
    $('#section-number-input').prop('disabled', !enabled);
    $('#section-crn-input').prop('disabled', !enabled);

}

function showSectionPopup()
{
    enableSectionPopup(true);

    $('#section-department-input').dropdown('restore defaults');
    $('#section-course-input').val('');
    $('#section-title-input').val('');
    $('#section-number-input').val('');
    $('#section-crn-input').val('');

    $('#new-section-popup').modal('show');
}

function cancelSectionPopup()
{
    $('#new-section-popup').modal('hide');
}

function showSectionUploadPopup()
{
    $('#new-section-upload-popup').modal('show');
}

function cancelSectionUploadPopup()
{
    $('#new-section-upload-popup').modal('hide');
}

function displayCourseTitle()
{
    let data = "department=" + $('#section-department-input').val();
    data += "&course_num=" + $('#section-course-input').val();

    $.ajax({
        url: '/api/admin/course.php',
        method: 'GET',
        data: data,
        success: function(data)
        {
            data = JSON.parse(data);

            $('#section-title-input').val(data.title);
        }
    })
}

function submitSection()
{
    let data = {};

    data.department = $('#section-department-input').val();
    data.course_num = $('#section-course-input').val();
    data.section = $('#section-number-input').val();
    data.crn = $('#section-crn-input').val();
    data.semester = $('#section-semester-header').html();

    $.ajax({
        url: '/api/admin/sections.php',
        method: 'POST',
        data: JSON.stringify(data),
        success: function()
        {
            enableSectionPopup(false);
            cancelSectionPopup();
            updateSectionTable(Cookies.get('semester_id'));
        }
    });
}

function updateSection()
{
    $('#section-primary-content-display').addClass('ui loading form');

    let data = [];

    $('.section-status-icon.icon').each(function() { createStatusUpdate(data, $(this)) });

    $.ajax({
        url: '/api/admin/sections.php',
        method: 'PUT',
        data: JSON.stringify(data),
        success: function()
        {
            updateSectionTable(Cookies.get('semester_id'));
            $('#section-primary-content-display').removeClass('ui loading form');
        }
    })
}

$(function()
{
    $('#new-section-popup').modal({closable: false, autofocus: false});
    $('#new-section-upload-popup').modal({closable: false, autofocus: false});

    $('#new-section-popup-button').on('click', showSectionPopup);
    $('#new-section-submit-button').on('click', submitSection);
    $('#new-section-cancel-button').on('click', cancelSectionPopup);

    $('#new-section-upload-popup-button').on('click', showSectionUploadPopup);
    $('#new-section-upload-cancel-button').on('click', cancelSectionUploadPopup);

    $('#section-department-input').on('change', displayCourseTitle)
    $('#section-course-input').on('keyup', displayCourseTitle);
});
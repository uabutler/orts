function updateDepartmentTable()
{
    $.ajax({
        url: '/api/admin/department.php',
        method: 'GET',
        success: function(data)
        {
            data = JSON.parse(data);

            if (data.length === 0)
            {
                $('#department-primary-content-display').html("Nothing to show");
                return;
            }

            let table = `<table class="ui celled structured table">
                    <thead>
                    <tr class="center aligned">
                        <th>Dept.</th>
                        <th>
                            Status
                            <i id="status-info-icon" class="exclamation circle icon link" data-content="" data-variation="wide"></i>
                        </th>
                    </tr>
                    </thead>
                    <tbody>`;

            let select = '<option value="">Ex. "CS"</option>';

            for (const department of data)
            {
                // Build a row of the table
                table += `<tr data-value="${department.id}">
                        <td>${department.department}</td>
                        <td class="status-table-cell">
                            <div>
                                <i class="department-status-icon hidden"></i>
                            </div>`;


                if (department.active)
                {
                    table +=`<select data-original="active" class="department-status-select ui dropdown fluid">
                            <option value="active">Active</option>
                            <option value="archive">Inactive</option>`;
                }
                else
                {
                    table +=`<select data-original="archive" class="department-status-select ui dropdown fluid">
                            <option value="archive">Inactive</option>`;

                }

                table += `<option value="delete">Delete</option>
                            </select>
                        </td>
                    </tr>`;

                // Build an option
                if (department.active)
                {
                    select += `<option value="${department.department}">${department.department}</option>`;
                }
            }

            table += `</tbody>
                </table>
                <button id="department-update-button" class="hidden ui right floated button">Update</button>`;

            $('#department-primary-content-display').html(table);

            $('.ui.dropdown').dropdown();
            $('#status-info-icon').popup();
            $('.department-status-select').on('change', function() { setStatusWarning('department', $(this)) });
            $('#department-update-button').on('click', updateDepartments);

            $('#course-department-input').html(select).dropdown();
            $('#section-department-input').html(select).dropdown();
        }
    });
}

function updateCourseTable()
{
    $.ajax({
        url: '/api/admin/course.php',
        method: 'GET',
        success: function(data)
        {
            data = JSON.parse(data);

            if (data.length === 0)
            {
                $('#course-primary-content-display').html("Nothing to show");
                return;
            }

            let table = `<table class="ui celled structured table">
                    <thead>
                    <tr class="center aligned">
                        <th>Code</th>
                        <th>Title</th>
                        <th>
                            Status
                            <i id="status-info-icon" class="exclamation circle icon link" data-content="" data-variation="wide"></i>
                        </th>
                    </tr>
                    </thead>
                    <tbody>`;

            for (const course of data)
            {
                table += `<tr data-value="${course.id}">
                        <td>${course.department.department} ${course.course_num}</td>
                        <td>${course.title}</td>
                        <td class="status-table-cell">
                            <div>
                                <i class="course-status-icon hidden"></i>
                            </div>`;


                if (course.active)
                {
                    table +=`<select data-original="active" class="course-status-select ui dropdown fluid">
                            <option value="active">Active</option>
                            <option value="archive">Inactive</option>`;
                }
                else
                {
                    table +=`<select data-original="archive" class="course-status-select ui dropdown fluid">
                            <option value="archive">Inactive</option>`;

                }

                table += `<option value="delete">Delete</option>
                            </select>
                        </td>
                    </tr>`;
            }

            table += `</tbody>
                </table>
                <button id="course-update-button" class="hidden ui right floated button">Update</button>`;

            $('#course-primary-content-display').html(table);

            $('.ui.dropdown').dropdown();
            $('#status-info-icon').popup();
            $('.course-status-select').on('change', function() { setStatusWarning('course', $(this)) });
            $('#course-update-button').on('click', updateCourses);
        }
    });
}

function enableDepartmentPopup(enabled)
{
    $('#department-name').prop('disabled', !enabled);
    $('#new-department-submit-button').prop('disabled', !enabled);
    $('#new-department-cancel-button').prop('disabled', !enabled);
}

function showDepartmentPopup()
{
    enableDepartmentPopup(true);
    $('#department-name').val('');
    $('#new-department-popup').modal('show');
}

function cancelDepartmentPopup()
{
    $('#new-department-popup').modal('hide');
}

function enableCoursePopup(enabled)
{
    $('#course-department-input').prop('disabled', !enabled);
    $('#course-number-input').prop('disabled', !enabled);
    $('#course-title-input').prop('disabled', !enabled);
    $('#new-course-submit-button').prop('disabled', !enabled);
    $('#new-course-cancel-button').prop('disabled', !enabled);
}

function showCoursePopup()
{
    enableCoursePopup(true);
    $('#course-department-input').dropdown('restore defaults');
    $('#course-number-input').val('');
    $('#course-title-input').val('');
    $('#new-course-popup').modal('show');
}

function cancelCoursePopup()
{
    $('#new-course-popup').modal('hide');
}

function submitDepartment()
{
    enableDepartmentPopup(false);

    let data = {};

    data.department = $('#department-name').val();

    $.ajax({
        url: `/api/admin/department.php`,
        method: 'POST',
        data: JSON.stringify(data),
        success: function()
        {
            updateDepartmentTable();
            cancelDepartmentPopup();
        }
    });
}

function submitCourse()
{
    enableCoursePopup(false);

    let data = {};

    data.department = $('#course-department-input').val();
    data.course_num = $('#course-number-input').val();
    data.title = $('#course-title-input').val();

    $.ajax({
        url: `/api/admin/course.php`,
        method: 'POST',
        data: JSON.stringify(data),
        success: function()
        {
            updateCourseTable();
            cancelCoursePopup();
        }
    })
}

function updateDepartments()
{
    $('#department-primary-content-display').addClass('ui loading form');
    $('#course-primary-content-display').addClass('ui loading form');

    let data = [];

    $('.department-status-icon.icon').each(function() { createStatusUpdate(data, $(this)) });

    $.ajax({
        url: '/api/admin/department.php',
        method: 'PUT',
        data: JSON.stringify(data),
        success: function()
        {
            updateDepartmentTable();
            updateCourseTable();
            $('#department-primary-content-display').removeClass('ui loading form');
            $('#course-primary-content-display').removeClass('ui loading form');
        }
    })
}

function updateCourses()
{
    $('#course-primary-content-display').addClass('ui loading form');

    let data = [];

    $('.course-status-icon.icon').each(function() { createStatusUpdate(data, $(this)) });

    $.ajax({
        url: '/api/admin/course.php',
        method: 'PUT',
        data: JSON.stringify(data),
        success: function()
        {
            updateCourseTable();
            $('#course-primary-content-display').removeClass('ui loading form');
        }
    })
}

$(function()
{
    updateDepartmentTable();
    updateCourseTable();

    $('#new-department-popup').modal({closable: false})
    $('#new-course-popup').modal({closable: false, autofocus: false})

    $('#new-department-popup-button').on('click', showDepartmentPopup);
    $('#new-department-cancel-button').on('click', cancelDepartmentPopup);
    $('#new-department-submit-button').on('click', submitDepartment);

    $('#new-course-popup-button').on('click', showCoursePopup);
    $('#new-course-cancel-button').on('click', cancelCoursePopup);
    $('#new-course-submit-button').on('click', submitCourse);
});
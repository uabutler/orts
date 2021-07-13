COURSE_REGEX = /^\d{3}$/;

function validateSectionDepartment() { return setError(validateNotEmpty('section-department-input'), 'section-department-input'); }
function validateSectionCourseNumber() { return setError(validateRegex('section-course-input', COURSE_REGEX), 'section-course-input'); }
function validateSectionNumber() { return setError(validateRegex('section-number-input', /^\d{1,2}$/), 'section-number-input'); }
function validateSectionCrn() { return setError(validateRegex('section-crn-input', /^\d{4}$/), 'section-crn-input'); }

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

    updateSectionTable();
}


function updateSectionTable()
{
    let data = 'id=' + Cookies.get('semester_id');

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
    if (enabled)
        $('#section-department-input').parent().removeClass('disabled');
    else
        $('#section-department-input').parent().addClass('disabled');

    $('#section-course-input').prop('disabled', !enabled);
    $('#section-title-input').prop('disabled', !enabled);
    $('#section-number-input').prop('disabled', !enabled);
    $('#section-crn-input').prop('disabled', !enabled);

}

function showSectionPopup()
{
    enableSectionPopup(true);

    setError(true, 'section-department-input');
    setError(true, 'section-course-input');
    setError(true, 'section-number-input');
    setError(true, 'section-crn-input');

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

function displayCourseTitle()
{
    if (!(validateNotEmpty('section-department-input')  && validateRegex('section-course-input', COURSE_REGEX)))
        return;

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
    let err = validateSectionDepartment();
    err = validateSectionCourseNumber() && err;
    err = validateSectionNumber() && err;
    err = validateSectionCrn() && err;

    if (!err)
        return;

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
            updateSectionTable();
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
            updateSectionTable();
            $('#section-primary-content-display').removeClass('ui loading form');
        }
    })
}

function showSectionUploadPopup()
{
    $('#file-selector').val('');
    $('#new-section-upload-submit-button').addClass('disabled');
    $('#file-upload-name').addClass('hidden');
    $('#upload-progress-bar').addClass('hidden');
    $('#default-upload-text').removeClass('hidden')
    $('#upload-browse-button').removeClass('hidden')
    $('#new-section-upload-popup').modal('show');
}

function selectFile()
{
    $('#file-upload-name').html($('#file-selector').prop('files')[0].name);
    $('#new-section-upload-submit-button').removeClass('disabled');
    $('#upload-browse-button').addClass('hidden')
    $('#default-upload-text').addClass('hidden')
    $('#file-upload-name').removeClass('hidden');
}

function uploadStatus(event)
{
    let percentage = Math.floor((event.loaded / event.total) * 100)
    $('#upload-progress-bar').progress({percent: percentage});
}

function uploadFile()
{
    $('#upload-progress-bar')
        .removeClass('hidden')
        .progress({percent: 0});

    let data = new FormData();
    data.append('semester', Cookies.get('semester_id'));
    data.append('attachment', $('#file-selector').prop('files')[0]);

    $.ajax({
        url: '/api/admin/section-upload.php',
        method: 'POST',
        data: data,
        cache: false,
        contentType: false,
        processData: false,
        xhr: function()
        {
            let xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener("progress", uploadStatus, false);
            return xhr;
        },
        success: completeUpload
    })
}

function completeUpload()
{
    cancelSectionUploadPopup();
    updateSectionTable();
    updateDepartmentTable();
    updateCourseTable();
}

function cancelSectionUploadPopup()
{
    $('#new-section-upload-popup').modal('hide');
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

    // Upload
    $('#file-selector').on('change', selectFile);
    $('#new-section-upload-submit-button').on('click', uploadFile);

    // Data validation
    $('#section-department-input').on('change', validateSectionDepartment);
    $('#section-course-input').on('focusout', validateSectionCourseNumber);
    $('#section-number-input').on('focusout', validateSectionNumber);
    $('#section-crn-input').on('focusout', validateSectionCrn)
});
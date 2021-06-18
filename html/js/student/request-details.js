function changeAdditional()
{
    if(!validateExplanation())
        return false;

    let reason = $('#reason');
    let explanation = $('#explanation');

    let data = {};

    data.id = REQUEST_ID;
    data.reason = reason.val();
    data.explanation = explanation.val();

    console.log(data);

    $('#additional-form').addClass('loading');

    $.ajax({
        url: '/api/student/request.php',
        type: 'PUT',
        data: JSON.stringify(data),
        success: function (data)
        {
            $('#reason-display').parent().children(".text").html(reason.val());
            $('#explanation-display').html(explanation.val());

            $('#additional-edit').css("display", "none");
            $('#additional-display').css("display", "grid");
        },
        complete: function ()
        {
            $('#additional-form').removeClass('loading');
        }
    });
}

function changeCourse()
{
    if (!validateCrn())
        return false;

    let semester = $('#semester');
    let crn = $('#crn');

    let data = {};

    data.id = REQUEST_ID;
    data.semester = semester.val();
    data.crn = crn.val();

    $('#course-form').addClass('loading');

    $.ajax({
        url: '/api/student/request.php',
        type: 'PUT',
        data: JSON.stringify(data),
        success: function (data)
        {
            $('#semester-display').html(semester.html());

            let section = $('#section').val();
            if (section.length)
                section = '0' + section;

            let course = $('#department').val() + " ";
            course += $('#course_num').val();

            $('#course-info-display').html(course);
            $('#course-title-display').html($('#course_title').val())
            $('#section-display').html(section);
            $('#crn-display').html($('#crn').val());

            $('#course-edit').css("display", "none");
            $('#course-display').css("display", "grid");
        },
        complete: function()
        {
            $('#course-form').removeClass('loading');
        }
    });
}

function displayFilePreview()
{
    let file_preview = $('#file-preview');
    let id = $(this).data('value');

    console.log("Loading preview for attachment " + id);

    file_preview.html('<div class="ui active centered inline loader"> </div>');
    $('#file-list').css('grid-column-end', '2');
    $('#file-preview-container').css('display', 'initial');

    // TODO: load the file? Maybe?
    $.ajax({
        url: '/api/file.php',
        data: 'id=' + id,
        cache: true,
        success: function (response, status, xhr)
        {
            if (xhr.getResponseHeader('content-type').indexOf("application/pdf") > -1)
            {
                file_preview.html(`
                <embed  src="/api/file.php?id=${id}"
                        type="application/pdf"
                        scrolling="auto"
                        width="100%"
                        style="min-height: 50vw;"
                >`);
            }
            else if (xhr.getResponseHeader('content-type').indexOf("image") > -1)
            {
                file_preview.html(`
                <img    src="/api/file.php?id=${id}"
                        alt="A preview of the user uploaded attachment"
                        width="100%"
                >`);
            }
            else
            {
                file_preview.html('<h3 style="text-align: center;">Cannot preview filetype</h3>')
            }
        }
    })

}

function closeFilePreview()
{
    $('#file-preview-container').css('display', 'none');
    $('#file-list').css('grid-column-end', '3');
}

function cancelUpload()
{
    $('.ui.modal').modal('hide');
    $('#file-selector').val('');
    $('#upload-file-button').addClass('disabled');
    $('#file-upload-name').addClass('hidden');
    $('#upload-progress-bar').addClass('hidden');
    $('#default-upload-text').removeClass('hidden')
    $('#upload-browse-button').removeClass('hidden')
}

function selectFile()
{
    $('#file-upload-name').html($('#file-selector').prop('files')[0].name);
    $('#upload-file-button').removeClass('disabled');
    $('#upload-browse-button').addClass('hidden')
    $('#default-upload-text').addClass('hidden')
    $('#file-upload-name').removeClass('hidden');
}

function uploadFile()
{
    $('#upload-progress-bar').removeClass('hidden');

    // Reset the progress bar in case this isn't the first upload
    $('#upload-progress-bar').progress({percent: 0});

    let data = new FormData();
    data.append("request", REQUEST_ID);
    data.append("attachment", $('#file-selector').prop('files')[0]);

    $.ajax({
        url: '/api/upload.php',
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
    });

    // prevent the upload dialog from closing
    return false;
}


function completeUpload()
{
    // This resets the interface for us
    cancelUpload();
    updateAttachmentTable();
}

function uploadStatus(event)
{
    let percentage = Math.floor((event.loaded / event.total) * 100)
    $('#upload-progress-bar').progress({percent: percentage});
}

function updateAttachmentTable()
{
    $.ajax({
       url: '/api/student/attachments.php',
        method: 'GET',
        data: 'id=' + REQUEST_ID,
        success: function(response)
        {
            let display = $('#file-list-table');
            if (response)
            {
                let data = JSON.parse(response);
                let table = `
                    <table class="ui celled table">
                        <thead>
                        <tr>
                            <th>File Name</th>
                            <th>Uploaded</th>
                            <th>Size</th>
                        </tr>
                        </thead>
                        <tbody>`;

                for (const file of data)
                {
                    table += `
                    <tr data-value="${file.id}" class="clickable-row attachment-entry">
                        <td>${file.name}</td>
                        <td>${file.upload_time}</td>
                        <td>${file.filesize}</td>
                    </tr>`;
                }

                table += `
                        </tbody>
                    </table>`;

                display.html(table);
                $('.attachment-entry').on('click', displayFilePreview);
            }
            else
            {
                display.html("<h3 style='text-align: center;'>Nothing to show</h3>");
            }
        }
    });
}

function createHandlers(edit_button, cancel_button, edit, display)
{
    $(`#${edit_button}`).on("click", function ()
    {
        $(`#${display}`).css("display", "none");
        $(`#${edit}`).css("display", "grid");
    });

    $(`#${cancel_button}`).on("click", function ()
    {
        $(`#${edit}`).css("display", "none");
        $(`#${display}`).css("display", "grid");
    });
}

$(function()
{
    updateAttachmentTable();

    $(document).on("input", ".numeric", function ()
    {
        this.value = this.value.replace(/\D/g, '');
    });

    createHandlers("course-edit-button", "course-cancel-button", "course-edit", "course-display");
    createHandlers("additional-edit-button", "additional-cancel-button", "additional-edit", "additional-display");

    $('#course_num').on("keyup", setSection);
    $('#section').on("keyup", setSection);
    $('#semester').on("change", setSection);
    $('#department').on("change", setSection);

    $('#course_num').on("focusout", function () { setError(validateCourseNum(), "course_num"); });
    $('#section').on("focusout", function () { setError(validateSection(), "section"); });
    $('#explanation').on("focusout", function () { setError(validateExplanation(), "explanation"); });

    $('#additional-submit-button').on("click", changeAdditional)
    $('#course-submit-button').on("click", changeCourse);

    $('#close-file-preview').on('click', closeFilePreview)

    $('.ui.modal').modal(
    {
        closable: false,
    });

    $('#upload-window-button').on('click', function() { $('.ui.modal').modal('show'); });
    $('#file-cancel').on('click', cancelUpload);
    $('#upload-file-button').on('click', uploadFile);

    $('#file-selector').on('change', selectFile);
})
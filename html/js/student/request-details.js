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
    // TODO: load the file? Maybe?
    console.log($(this).data('value'));

    $('#file-list').css('grid-column-end', '2');
    $('#file-preview-container').css('display', 'initial');

    // Do we need to remove the current preview?
    // TODO: Set the file preview to display the contents of the file if possible. Otherwise, start download
    // Do we need to display a download button?
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

    let fileReader = new FileReader();
    fileReader.readAsText($('#file-selector').prop('files')[0], 'UTF-8');
    fileReader.onload = shipOff;
    fileReader.onprogress = uploadStatus;

    // prevent the upload dialog from closing
    return false;
}

function shipOff(event)
{
    let result = event.target.result;
    let fileName = $('#file-selector').prop('files')[0].name;
    $.post('/attachment-upload.php', { data: result, name: fileName, request: REQUEST_ID }, completeUpload);
}

function completeUpload()
{
    // This resets the interface for us
    cancelUpload();
    // TODO: Update the list of attachments
}

function uploadStatus(event)
{
    let percentage = Math.floor((event.loaded / event.total) * 100)
    $('#upload-progress-bar').progress({percent: percentage});
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

    $('.attachment-entry').on('click', displayFilePreview);
    $('#close-file-preview').on('click', closeFilePreview)

    $('.ui.modal').modal(
    {
        closable: false,
        onDeny: cancelUpload,
        onApprove: uploadFile
    });

    $('#upload-window-button').on('click', function() { $('.ui.modal').modal('show'); });

    $('#file-selector').on('change', selectFile);
})
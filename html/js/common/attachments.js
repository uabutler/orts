function displayFilePreview()
{
    let file_preview = $('#file-preview');
    let id = $(this).parent().data('value');

    console.log("Loading preview for attachment " + id);

    window.open('/api/file.php?id=' + id, "_blank");
}

function showUploadPopup()
{
    $('#file-selector').val('');
    $('#upload-file-button').addClass('disabled');
    $('#file-upload-name').addClass('hidden');
    $('#upload-progress-bar').addClass('hidden');
    $('#default-upload-text').removeClass('hidden')
    $('#upload-browse-button').removeClass('hidden')
    $('#upload-popup').modal('show');
}

function cancelUpload()
{
    $('#upload-popup').modal('hide');
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
    cancelUpload();
    updateAttachmentTable();
}

function uploadStatus(event)
{
    let percentage = Math.floor((event.loaded / event.total) * 100)
    $('#upload-progress-bar').progress({percent: percentage});
}

function deleteAttachment()
{
    let id = $(this).data('value');

    $('#delete-confirmation')
        .modal({
            closable: false,
            onApprove: function ()
            {
                $.ajax({
                    url: '/api/attachments.php',
                    method: 'DELETE',
                    data: 'id=' + id,
                    success: function(data)
                    {
                        displayStatusMessage('Success', 'Attachment deleted', true);
                        updateAttachmentTable();
                    },
                    fail: function(data)
                    {
                        displayStatusMessage('Error', 'Could not delete attachment', false, data.request_id);
                    }
                });
            }
        })
        .modal('show');
}

function updateAttachmentTable()
{
    $.ajax({
        url: '/api/attachments.php',
        method: 'GET',
        data: 'id=' + REQUEST_ID,
        success: function(data)
        {
            let display = $('#file-list-table');
            if (data)
            {
                data = JSON.parse(data)
                let table = `
                    <table class="ui celled table">
                        <thead>
                        <tr>
                            <th>File Name</th>
                            <th>Uploaded</th>
                            <th>Size</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>`;

                for (const file of data.response)
                {
                    table += `
                    <tr data-value="${file.id}">
                        <td class="clickable-row attachment-entry">${file.name}</td>
                        <td class="clickable-row attachment-entry">${file.upload_time}</td>
                        <td class="clickable-row attachment-entry">${file.filesize}</td>
                        <td>
                            <div class="ui dropdown file-actions">
                              <div class="text">File</div>
                              <i class="dropdown icon"></i>
                              <div class="menu">
                                <div class="item">
                                    <a
                                        href="/api/file.php?id=${file.id}"
                                        download="${file.name}"
                                        style="color: inherit;">
                                      <i class="download icon"></i>
                                      Download
                                    </a>
                                </div>
                                <div class="item delete-file" data-value="${file.id}">
                                  <i class="trash icon"></i>
                                  Delete
                                </div>
                              </div>
                            </div>
                        </td>
                    </tr>`;
                }

                table += `
                        </tbody>
                    </table>`;

                display.html(table);

                $('.file-actions').dropdown({action: 'hide'});
                $('.attachment-entry').on('click', displayFilePreview);
                $('.delete-file').on('click', deleteAttachment)
            }
            else
            {
                display.html("<h3 style='text-align: center;'>Nothing to show</h3>");
            }
        }
    });
}

$(function ()
{
    updateAttachmentTable();

    $('#upload-window-button').on('click', showUploadPopup);
    $('#file-cancel').on('click', cancelUpload);
    $('#upload-file-button').on('click', uploadFile);

    $('#file-selector').on('change', selectFile);

    $('#upload-popup').modal({ closable: false, });
});
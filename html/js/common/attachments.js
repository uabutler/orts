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
        url: '/api/attachments.php',
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

$(function ()
{
    updateAttachmentTable();

    $('#upload-window-button').on('click', function() { $('.ui.modal').modal('show'); });
    $('#file-cancel').on('click', cancelUpload);
    $('#upload-file-button').on('click', uploadFile);

    $('#file-selector').on('change', selectFile);

    $('#close-file-preview').on('click', closeFilePreview)

    $('.ui.modal').modal({
        closable: false,
    });
});
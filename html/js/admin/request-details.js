function changeStatus()
{
    let data = "id=" + REQUEST_ID + "&";
    data += "status=" + encodeURIComponent($('#status_input').val()) + "&";
    data += "banner=" + $('#banner').is(":checked") + "&";
    data += "justification=" + encodeURIComponent($('#justification').val());

    $.ajax({
        url: '/api/request.php',
        type: 'PUT',
        data: data,
        success: function (data)
        {
            $('#status_info').html(getStatusHtml({status:$('#status_input').val(), banner:$('#banner').is(":checked")}));
        }
    });
}

$(function ()
{
    $(`#status_input option[value="${REQUEST_STATUS}"]`).prop("selected", true);
    $('#submit').on("click", changeStatus);
})

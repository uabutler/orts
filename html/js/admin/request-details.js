function changeStatus()
{
    let data = {};

    data.id = REQUEST_ID;
    data.status = $('#status_input').val();
    data.banner = $('#banner').is(':checked');
    data.justification = $('#justification').val();

    $.ajax({
        url: '/api/admin/request.php',
        type: 'PUT',
        data: JSON.stringify(data),
        timeout: 5000,
        success: function (data)
        {
            $('#status_info').html(getStatusHtml({status:$('#status_input').val(), banner:$('#banner').is(":checked")}));
            setMessage("Success", "The request status has been successfully updated", true);
        },
        error: function ()
        {
            setMessage("Error", "The request could not be completed", false);
        }
    });
}

function changeFaculty()
{
    let data = {};

    data.id = REQUEST_ID;
    data.faculty = $('#faculty_input').val();
    data.note = $('#note').val();

    $.ajax({
        url: '/api/admin/request.php',
        type: 'PUT',
        data: JSON.stringify(data),
        timeout: 5000,
        success: function (data)
        {
            $('#faculty_info').html($('#faculty_input option:selected').text())
            $('#faculty_input').dropdown('clear');
            $('#note').val('');
            setMessage("Success", "The request has been reassigned", true);
        },
        error: function ()
        {
            setMessage("Error", "The request could not be completed", false);
        }
    });
}

$(function ()
{
    $(`#status_input option[value="${REQUEST_STATUS}"]`).prop("selected", true);
    $('#submit').on("click", changeStatus);
    $('#submit-faculty').on("click", changeFaculty);
})

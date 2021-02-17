function reasonSelect()
{
    let out = '<select class="select" id="reason">';

    let first = $('#reason-cell').html();
    out += `<option value="${first}">${first}</option>`;
    for(let i = 0; i < REASONS.length; i++)
    {
        if(REASONS[i] !== first)
            out += `<option value="${REASONS[i]}">${REASONS[i]}</option>`;
    }

    out += '</select>';

    return out;
}

function changeAdditional(reason, explanation)
{
    let data = "id=" + REQUEST_ID + "&";
    data += "reason=" + encodeURIComponent(reason) + "&";
    data += "explanation=" + encodeURIComponent(explanation);

    $('#additional-edit').prop("disabled", true);
    $('#course-edit').prop("disabled", true);

    $.ajax({
        url: '/api/request.php',
        type: 'PUT',
        data: data,
        success: function (data)
        {
            $('#course-edit').prop("disabled", false);
            $('#additional-edit').prop("disabled", false);
        }
    });
}

function additionalHandler()
{
    let element = $("#additional-edit-icon");
    if(element.html() === "create")
    {
        element.html("done_outline");
        $('#reason-cell').html(reasonSelect());
        $('.select').select2();
        $('#explanation').attr("readonly", false);
    }
    else
    {
        let reason = $('#reason').val();
        let explanation = $('#explanation').val();

        element.html("create");
        $('#reason-cell').html(reason);
        $('#explanation').attr("readonly", true);

        changeAdditional(reason, explanation);
    }
}

$(function()
{
    $("#additional-edit").on("click", additionalHandler);
})
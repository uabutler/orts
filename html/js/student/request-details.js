function reasonSelect()
{
    let out = '<select class="select" id="reason">';

    for(let i = 0; i < REASONS.length; i++)
        out += `<option value="${REASONS[i]}">${REASONS[i]}</option>`;

    out += '</select>';

    return out;
}

function courseHandler()
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
        $('#reason-cell').html(getStatusHtml(reason));
        $('#explanation').attr("readonly", true);
    }
}

$(function()
{
    $("#additional-edit").on("click", courseHandler())
})
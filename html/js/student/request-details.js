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
        $('#reason-cell').html(reason);
        $('#explanation').attr("readonly", true);
    }
}

$(function()
{
    $("#additional-edit").on("click", courseHandler);
})
function reasonSelect()
{
    let out = '<select class="select" id="reason">';

    let first = CURRENT_REASON;
    out += `<option value="${first}">${first}</option>`;
    for(let i = 0; i < REASONS.length; i++)
    {
        if(REASONS[i] !== first)
            out += `<option value="${REASONS[i]}">${REASONS[i]}</option>`;
    }

    out += '</select>';

    return out;
}

function changeAdditional()
{
    let reason = $('#reason');
    let explanation = $('#explanation');

    let data = "id=" + REQUEST_ID + "&";
    data += "reason=" + encodeURIComponent(reason.val()) + "&";
    data += "explanation=" + encodeURIComponent(explanation.val());

    reason.prop("disabled", true);
    explanation.prop("readonly", true);

    $.ajax({
        url: '/api/request.php',
        type: 'PUT',
        data: data,
        success: function (data)
        {
            $('#reason-display').html(reason.val());
            $('#explanation-display').html(explanation.val());

            $('div.additional-edit').css("display", "none");
            $('div.additional-display').css("display", "initial");

            reason.prop("disabled", false);
            explanation.prop("readonly", false);
        }
    });
}

function createHandlers(edit, display, cancel)
{
    $('button.' + edit).on("click", function ()
    {
        $('div.' + display).css("display", "none");
        $('div.' + edit).css("display", "initial");
        $('.select').select2();
    });

    $('button.' + cancel).on("click", function ()
    {
        $('div.' + edit).css("display", "none");
        $('div.' + display).css("display", "initial");
    });
}

$(function()
{
    createHandlers("course-edit", "course-display", "course-cancel");
    createHandlers("additional-edit", "additional-display", "additional-cancel");

    $('#reason-cell').html(reasonSelect());

    $('.additional-submit').on("click", changeAdditional)
})
function createHandlers(edit_button, cancel_button, edit, display)
{
    $(`#${edit_button}`).on("click", function ()
    {
        $(`#${display}`).css("display", "none");
        $(`#${edit}`).css("display", "initial");
    });

    $(`#${cancel_button}`).on("click", function ()
    {
        $(`#${edit}`).css("display", "none");
        $(`#${display}`).css("display", "grid");
    });
}


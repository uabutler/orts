let requests;

/**
 * Sort the table based on which option the user has selected
 */
function sortTable() {
    requests.sort(function (lhs, rhs) {
        if ($("input[name='datesort']:checked").val() === "datedescending")
            return lhs.last_modified < rhs.last_modified ? -1 : +(lhs.last_modified > rhs.last_modified);
        else // dateascending
            return lhs.last_modified > rhs.last_modified ? -1 : +(lhs.last_modified < rhs.last_modified);
    });
}

/**
 * Does a given request match the filter options the user has selected? Used when rendering the table
 * to determine which entries should be displayed
 * TODO: In the future, I want to try to just hide those rows instead of deleting and reprinting them.
 */
function matchFilter(request) {
    let out = true;

    let filter;

    // After pulling this little stunt, a judge considering handing down an restraining order preventing me from
    // coming within 10 ft of any technology
    switch (false)
    {
        // First, remove anything that doesn't match an option selected with a radio button
        case ((filter = $("input[name='status']:checked").val()) === "all_stat") || (filter === request.status):
        case ((filter = $("input[name='banner']:checked").val()) === "both") || ((filter === "inbanner") === request.banner):
        case ((filter = $("input[name='dept']:checked").val()) === "all_dept") || (filter === request.section.course.department.department):

        // Next, remove anything that doesn't start with the given search string.
        case request.student.first_name.toLowerCase().startsWith($('#first').val().toLowerCase()):
        case request.student.last_name.toLowerCase().startsWith($('#last').val().toLowerCase()):
        case request.section.crn.startsWith($('#crn').val()):
        case request.student.banner_id.startsWith($('#bannerid').val()):
        case String(request.section.course.course_num).startsWith($('#course_num').val()):
        case request.section.semester.semester.startsWith($('#semester').val()):
            return false;
        default:
            return true;
    }
}

/**
 * Deletes all rows currently in the table and adds the rows according to the filters in the order indicated by
 * the sort option
 */
function renderTable() {
    $('.request-item').remove();

    requests.forEach(function writeToTable(request) {
        if (matchFilter(request))
            $('#request-table').append(request.rowHtml);
    })
}

/*
 *  MAIN
 */
$(function ()
{
    // Check all of the default filters
    $(".default").prop('checked', true);

    // Add the numeric restriction to CRN, Course num, banner, etc.
    $(document).on("input", ".numeric", function () {
        this.value = this.value.replace(/\D/g, '');
    });

    // Import the requests from the database into javascript
    requests = JSON.parse(JSON_DATA);

    if (requests.length === 0) {
        $("#request-table").after('<h3 style="text-align: center">No relevant entries</h3>');
        return;
    }

    // construct the html code representing a row
    requests.forEach(function createHtml(request) {
        // The opening tag including the get request for the details page
        const tdOpen = '<td onclick="window.location=\'request-details.php?id=' + request.id + '\'">';

        let out = '<tr class="request-item">';
        out += '<td><input type="checkbox"></td>';
        out += tdOpen + getStatusHtml(request) + '</td>';
        out += tdOpen + request.last_modified + '</td>';
        out += tdOpen + request.student.last_name + '</td>';
        out += tdOpen + request.student.first_name + '</td>';
        out += tdOpen + request.student.banner_id + '</td>';
        out += tdOpen + request.section.course.department.department + '</td>';
        out += tdOpen + request.section.course.course_num + '</td>';
        out += tdOpen + request.section.crn + '</td>';
        out += tdOpen + request.section.semester.semester + '</td>';
        out += '</tr>';

        request.rowHtml = out;
    });

    sortTable();
    renderTable();

    $('input[name="datesort"]').on("change", function () {
        sortTable();
    });
    $('input[type="radio"]').on("change", function () {
        renderTable();
    });
    $('input[type="text"]').on("input", function () {
        renderTable();
    });
})
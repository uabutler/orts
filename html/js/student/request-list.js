let requests;

/**
 * Deletes all rows currently in the table and adds the rows according to the filters in the order indicated by
 * the sort option
 */
function renderTable() {
    $('.request-item').remove();

    requests.forEach(function writeToTable(request) {
        $('#request-table').append(request.rowHtml);
    })
}

/*
 *  MAIN
 */
$(function () {
    // Import the requests from the database into javascript
    requests = JSON.parse(JSON_DATA);

    if (requests.length === 0) {
        $("#request-table").after('<h3 style="text-align: center">No relevant entries</h3>');
        return;
    }

    // construct the html code representing a row
    requests.forEach(function createHtml(request)
    {
        // The opening tag including the get request for the details page
        let out = '<tr class="clickable-row" onclick="window.location=\'request-details.php?id=' + request.id + '\'" class="request-item">';
        out += '<td>' + request.section.course.department.department + '</td>';
        out += '<td>' + request.section.course.course_num + '</td>';
        out += '<td>' + request.section.course.title + '</td>';
        out += '<td>' + getStatusHtml(request) + '</td>';
        out += '</tr>';

        request.rowHtml = out;
    });

    renderTable();
});

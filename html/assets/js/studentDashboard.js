$(document).ready(function() {
    var activeTable = $("#studentActiveRequestsTable").DataTable({
        dom: "lfrtip",
        pageLength: 10,
        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "All"]
        ],
        fixedHeader: true,
        columnDefs: [
            {
                targets: 5,
                data: null,
                defaultContent: "<button class='studentEditRequest'>"+EDIT_TEXT+"</button>",
                sortable: false,
                searchable: false
            }
        ],
        order: [
            [2, "desc"]
        ]
    }); // var activetable

    var archiveTable = $("#studentArchivedRequestsTable").DataTable({
        dom: "lfrtip",
        pageLength: 10,
        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "All"]
        ],
        fixedHeader: true,
        columnDefs: [
            {
                targets: 5,
                data: null,
                defaultContent: "<button class='studentEditRequest'>"+EDIT_TEXT+"</button>",
                sortable: false,
                searchable: false
            }
        ],
        order: [
            [2, "desc"]
        ]
    }); // var archivetable

    
}); // document ready
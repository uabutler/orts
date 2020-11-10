$(document).ready(function() {
    var activeTable = $("#studentActiveRequestsTable").DataTable({
        dom: "lfrt",
        paging: false,
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
        dom: "lfrt",
        paging: false,
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
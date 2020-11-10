$(document).ready(function() {
    var activeTable = $("#studentActiveRequestsTable").DataTable({
        dom: "lfrt",
        paging: false,
        fixedHeader: true,
        columnDefs: [
            {
                targets: 4,
                data: null,
                defaultContent: "<button class='studentEditRequest'>"+EDIT_TEXT+"</button>",
                sortable: false,
                searchable: false
            }
        ],
        order: [
            [1, "desc"]
        ]
    }); // var activetable

    var archiveTable = $("#studentArchivedRequestsTable").DataTable({
        dom: "lfrt",
        paging: false,
        fixedHeader: true,
        columnDefs: [
            {
                targets: 4,
                data: null,
                defaultContent: "<button class='studentEditRequest'>"+EDIT_TEXT+"</button>",
                sortable: false,
                searchable: false
            }
        ],
        order: [
            [1, "desc"]
        ]
    }); // var archivetable

    
}); // document ready
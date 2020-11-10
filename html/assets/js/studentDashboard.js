$(document).ready(function() {
    var activeTable = $("#studentActiveRequestsTable").DataTable({
        dom: "lfrt",
        paging: false,
        fixedHeader: true,
        ajax: {
            url: BASE_URL+"/requests",
            dataSrc: ""
        },
        columnDefs: [
            {
                targets: 0,
                data: "status"
            },
            {
                targets: 1,
                data: "last-modified"
            },
            {
                targets: 2,
                data: "department"
            },
            {
                targets: 3,
                data: "class-number"
            },
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
        ajax: {
            url: BASE_URL+"/requests?archived=true",
            dataSrc: ""
        },
        columnDefs: [
            {
                targets: 0,
                data: "status"
            },
            {
                targets: 1,
                data: "last-modified"
            },
            {
                targets: 2,
                data: "department"
            },
            {
                targets: 3,
                data: "class-number"
            },
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
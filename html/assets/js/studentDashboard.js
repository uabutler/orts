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
                data: "last-modified",
                render: function(data, type, row){
                    var d = new Date(data+"-0:00");
                    return d.toLocaleDateString() + " " + d.toLocaleTimeString();
                }
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
                defaultContent: "<button disabled class='studentEditRequest'>"+EDIT_TEXT+"</button>",
                sortable: false,
                searchable: false
            }
        ],
        order: [
            [1, "desc"]
        ],
        initComplete: function(){
            $("#requestsTable tbody tr").each(function(){
                var cell = $(this).find("td:eq(2)");
                var d = new Date(cell.text() + "-0:00");
                cell.attr("data-sort", d.getTime() / 1000);
            });
        }
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
                data: "last-modified",
                render: function(data, type, row){
                    var d = new Date(data+"-0:00");
                    return d.toLocaleDateString() + " " + d.toLocaleTimeString();
                }
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
                defaultContent: "<button disabled class='studentEditRequest'>"+EDIT_TEXT+"</button>",
                sortable: false,
                searchable: false
            }
        ],
        order: [
            [1, "desc"]
        ],
        initComplete: function(){
            $("#requestsTable tbody tr").each(function(){
                var cell = $(this).find("td:eq(2)");
                var d = new Date(cell.text() + "-0:00");
                cell.attr("data-sort", d.getTime() / 1000);
            });
        }
    }); // var archivetable

    
}); // document ready
$(document).ready(function() {
    $.fn.dataTable.ext.type.order['unixTime-pre'] = function (datestring){
        var d = new Date(datestring + "-0:00");
        return (d.getTime() / 1000);
    };

   $.fn.dataTable.ext.type.order['monthYear-pre'] = function(datestring){
        const components = datestring.split("/");
       return (components[1]+components[0]);
    };

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
                data: "status",
                render: function(data, type, row){
                    return setIcon(data) + "<span class='statusText'>" + data + "</span>";
                }
            },
            {
                targets: 1,
                data: "last-modified",
                render: function(data, type, row){
                    var d = new Date(data+"-0:00");
                    return d.toLocaleDateString() + " " + d.toLocaleTimeString();
                },
                type: "unixTime"
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
        ]
    }); // var activetable

    /*
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
                data: "status",
                render: function(data, type, row){
                    return "<i></i><span class='statusText'>" + data + "</span>";
                }
            },
            {
                targets: 1,
                data: "last-modified",
                render: function(data, type, row){
                    var d = new Date(data+"-0:00");
                    return d.toLocaleDateString() + " " + d.toLocaleTimeString();
                },
                type: "unixTime"
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
        ]
    }); // var archivetable
    */
    
}); // document ready
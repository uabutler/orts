$(document).ready(function() {
    const dismissible = new Dismissible(document.querySelector('#dismissible-container'));
    // Clone the header row into the footer and to make a row for later filters
    $("#requestsTable thead tr").clone(true).appendTo("#requestsTable tfoot")
    $("#requestsTable tfoot tr th:eq(0)").empty();
    $("#requestsTable thead tr").clone(true).appendTo("#requestsTable thead");
    $("#requestsTable thead tr:eq(0) th").each(function() { $(this).empty() });

    $.fn.dataTable.ext.type.order['unixTime-pre'] = function (datestring){
        var d = new Date(datestring + "-0:00");
        return (d.getTime() / 1000);
    };

   $.fn.dataTable.ext.type.order['monthYear-pre'] = function(datestring){
        const components = datestring.split("/");
       return (components[1]+components[0]);
    };

    var table = $("#requestsTable").DataTable({
        dom: "lrtip",
        pageLength: 10,
        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "All"]
        ],
        fixedHeader: true,
        ajax: {
            url: BASE_URL+"/requests",
            dataSrc: ""
        },
        columnDefs: [{
                targets: 0,
                data: null,
                defaultContent: "<input type='checkbox' class='requestSelect' disabled />",
                sortable: false,
                searchable: false
            },
            {
                targets: 1,
                data: "status",
                render: function(data, type, row){
                    return setIcon(data) + "<span class='statusText'>" + data + "</span>";
                }
            },
            {
                targets: 2,
                data: "last-modified",
                render: function(data, type, row){
                    var d = new Date(data+"-0:00");
                    return d.toLocaleDateString() + " " + d.toLocaleTimeString();
                },
                type: "unixTime"
            },
            {
                targets: 3,
                data: "last-name"
            },
            {
                targets: 4,
                data: "first-name"
            },
            {
                targets: 5,
                data: "grad-month",
                type: "monthYear"
            },
            {
                targets: 6,
                data: "banner-id"
            },
            {
                targets: 7,
                data: "semester"
            },
            {
                targets: 8,
                data: "crn"
            },
            {
                targets: 9,
                data: "department"
            },
            {
                targets: 10,
                data: "class-number"
            },
            {
                targets: 11,
                data: null,
                defaultContent: "<button class='viewRequest'>"+VIEW_TEXT+"</button>",
                sortable: false,
                searchable: false
            }
        ],
        order: [
            [2, "desc"]
        ],
        initComplete: function() {
                var textBoxCols = [2, 3, 4, 6, 8, 10];
                var selectCols = [1, 5, 7, 9];
                this.api().columns().every(function(i) {
                    var cell = $("#requestsTable thead tr:eq(0) th:eq(" + i + ")");
                    var title = $("#requestsTable thead tr:eq(1) th:eq(" + i + ")").text();
                    var titleRegexSafe = title.replace(/[\W]/g, '-');
                    if (textBoxCols.includes(i)) {
                        // Create Text Input Field
                        $('<input type="text" id="filter-' + titleRegexSafe + '"/>').appendTo(cell);

                        // Define search behavior on key-up and change
                        $('input[type=text]', cell).on('keyup change', function() {
                            if (table.column(i).search() !== this.value) {
                                table
                                    .column(i)
                                    .search(this.value)
                                    .draw();
                            }
                        });
                    } // textBox
                    else if (selectCols.includes(i)) {
                        var column = this;

                        // Append the underlying HTML select element
                        var select = $('<select id="filter-' + titleRegexSafe + '" class="select2"></select>')
                            .appendTo(cell) // .. to the right row
                            .on('change', function() {
                                // Store the selected values

                                //Get the "text" property from each selected data 
                                //regex escape the value and store in array
                                var data = $.map($(this).select2('data'), function(value, key) {
                                    return value.text ? '^' + $.fn.dataTable.util.escapeRegex(value.text) + '$' : null;
                                });

                                //if no data selected use ""
                                if (data.length === 0) {
                                    data = [""];
                                }

                                //join array into string with regex or (|)
                                var val = data.join('|');

                                //search for the option(s) selected
                                column
                                    .search(val ? val : '', true, false)
                                    .draw();
                            });

                        // sort the values ascending
                        column.data().unique().sort().each(function(d, j) {
                            select.append('<option value="' + d + '">' + d + '</option>');
                        });

                        //Invoke Select2
                        $('#filter-' + titleRegexSafe).select2({
                            multiple: true,
                            closeOnSelect: true,
                            // placeholder: title,
                        });

                        //initially clear select otherwise first option is selected
                        // can also set default value(s) here
                        // ex .val(["2020-08", "2020-07"])
                        $('.select2').val(null).trigger('change');
                    } // select
                }); // every column

              $("#selectAll").click(function() {
                  var checked = $(this).prop("checked");
                  $(".requestSelect").each(function() {
                      $(this).prop("checked", checked);
                  });
              }); // selectAll
          
              $(".viewRequest").click(function(){
                const theRow = $(this).closest("tr");
                const wasSelected = $(theRow).hasClass("selected");
                if(wasSelected){
                  // Hide the detailed view
                  theRow.removeClass("selected");
                  $(this).text(VIEW_TEXT);
                  $("#requestInformation").removeClass("visible");
                } else {
                  // Highlight the row
                  $("#requestsTable tbody tr").each(function(){
                    $(this).removeClass("selected");
                    $(this).find("button.viewRequest").text(VIEW_TEXT);
                  });
                  theRow.addClass("selected");

                  setDecisionButtons();

                  // Insert Data
                  const data = table.row(theRow).data();
                  $("#status").text(data.status);
                  $("#classStanding").text(data["class-standing"]);
                  $("#overrideType").text(data.type);
                  $("#explanation").text(data.explanation);
                  $("#studentEmail").html("<a href=\"mailto:" + data.email + "\">" + data.email + "</a>");
                  //TODO: Files
                  //TODO: Messages to student
                  //TODO: Admin Messages

                  $(this).text(HIDE_TEXT);
                  $("#requestInformation").addClass("visible");
                }
              }); // viewRequest
            } // initComplete
    }); // var table

    function setDecisionButtons(){
        var newStatus = table.row($("#requests tbody tr.selected")).data().status;
        $("#decisionBox button").attr("disabled", false);
        switch(newStatus) {
            case "Received":
            case "Awaiting Action":
                $("#markReceived").attr("disabled", true);
                break;
            case "Approved":
                $("#approve").attr("disabled", true);
                break;
            case "Provisionally Approved":
                $("#provApprove").attr("disabled", true);
                break;
            case "Denied":
                $("#deny").attr("disabled", true);
                break;
            case "Requires Faculty Approval":
                $("#sendToChair").attr("disabled", true);
                break;
        }
    }

    function setRequestStatus(theid, newStatus){
        
        // Prevent clicking on status buttons
        $("#decisionBox button").attr("disabled", true);

        // Prevent viewing any other requests
        $(".viewRequest").attr("disabled", true);

        // Request the change
        $.ajax({
            method: "PATCH",
            url: BASE_URL+"/requests",
            contentType: "application/json",
            data: JSON.stringify({
              id: theid,
              status: newStatus
            }),
            complete: function(request, status){
                if (request.status == 200){
                    $("#status").text(newStatus);
                    var row = table.row($("#requests tbody tr.selected"));
                    row.data().status = newStatus;
                    row.invalidate();
                    table.draw(false);
                } else if(request.status == 404){
                    dismissible.error("The specified request was not found. Was it archived?");
                } else {
                    var data = $.parseJSON(request.responseText);
                    dismissible.error("An Error Occurred: " + data.message + " (Code " + data.code + ")");
                }
                // Re-enable view and status buttons
                setDecisionButtons();
                $(".viewRequest").attr("disabled", false);
                $("#requests tbody tr.selected .viewRequest").text(HIDE_TEXT);
            }
          });
    }

    $("#markReceived").click(function(){
        setRequestStatus(table.row($("#requests tbody tr.selected")).data().id, 
                         "Received");        
    });
    $("#sendToChair").click(function(){
        setRequestStatus(table.row($("#requests tbody tr.selected")).data().id, 
                         "Requires Faculty Approval");      
    });
    $("#approve").click(function(){
        setRequestStatus(table.row($("#requests tbody tr.selected")).data().id, 
                         "Approved");        
    });
    $("#provApprove").click(function(){
        setRequestStatus(table.row($("#requests tbody tr.selected")).data().id, 
                         "Provisionally Approved");        
    });
    $("#deny").click(function(){
        setRequestStatus(table.row($("#requests tbody tr.selected")).data().id, 
                         "Denied");        
    });

    function archiveRequest(id){
        var confirmation = confirm("Are you sure you want to archive this request?");
        if (confirmation){
            // Prevent viewing any other requests
            $(".viewRequest").attr("disabled", true);

            // Prevent clicking on status buttons
            $("#decisionBox button").attr("disabled", true);

            // Request the change
            $.ajax({
                method: "DELETE",
                url: BASE_URL+"/requests?id=" + encodeURIComponent(parseInt(id)),
                complete: function(request, status){
                    if (request.status == 200){
                        // Hide the detailed view
                        $("#requestInformation").removeClass("visible");
                        table.row($("#requests tbody tr.selected")).remove().draw(false);
                    } else if(request.status == 404){
                        setDecisionButtons();
                        dismissible.error("The specified request was not found. Was it archived already?");
                    } else {
                        setDecisionButtons();
                        var data = $.parseJSON(request.responseText);
                        dismissible.error("An Error Occurred: " + data.message + " (Code " + data.code + ")");
                    }
                    // Re-enable buttons
                    $(".viewRequest").attr("disabled", false);
                    $("#requests tbody tr.selected .viewRequest").text(HIDE_TEXT);
                }
            });
        }
    }

    $("#archive").click(function(){
        archiveRequest(table.row($("#requests tbody tr.selected")).data().id);
    })
    
}); // document ready

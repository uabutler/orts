$(document).ready(function() {
    const VIEW_TEXT = "View";
    const HIDE_TEXT = "Hide";

    // Clone the header row into the footer and to make a row for later filters
    $("#requests thead tr").clone(true).appendTo("#requests tfoot")
    $("#requests tfoot tr th:eq(0)").empty();
    $("#requests thead tr").clone(true).appendTo("#requests thead");

    var table = $("#requests").DataTable({
        dom: "lrtip",
        pageLength: 10,
        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "All"]
        ],
        fixedHeader: true,
        columnDefs: [{
                targets: 0,
                data: null,
                defaultContent: "<input type='checkbox' class='requestSelect' disabled />",
                sortable: false,
                searchable: false
            },
            {
                targets: 12,
                data: null,
                defaultContent: "<button class='viewRequest'>View</button>",
                sortable: false,
                searchable: false
            }
        ],
        order: [
            [2, "asc"]
        ],
        initComplete: function() {
                $("#requests thead tr:eq(0) th").each(function() { $(this).empty() });
                var textBoxCols = [2, 3, 4, 5, 7, 9, 11];
                var selectCols = [1, 6, 8, 10];
                this.api().columns().every(function(i) {
                    var cell = $("#requests thead tr:eq(0) th:eq(" + i + ")");
                    var title = $("#requests thead tr:eq(1) th:eq(" + i + ")").text();
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
                            select.append('<option value="' + (d === null ? "Ongoing" : d) + '">' + (d === null ? "Ongoing" : d) + '</option>');
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
                var data = table.row($(this.closest("tr"))).data();
                $("#requestInformation").toggleClass("visible");
                $(this).text($("#requestInformation").hasClass("visible") ? HIDE_TEXT : VIEW_TEXT);
              });
            } // initComplete
    }); // var table

    
}); // document ready
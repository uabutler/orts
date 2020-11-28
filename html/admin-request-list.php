<?php
include_once 'database/requests_db.php';

if (isset($_GET['semester']))
{
    $semester = Semester::getByCode(intval($_GET['semester']));
    if(is_null($semester))
        $requests = null;
    else
        $requests = Request::getBySemester($semester);
}
else
{
    $requests = Request::getActive();
}

if(is_null($requests))
{
    header("Location: error404.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="icon" type="image/png" href="https://images.truman.edu/favicon-16x16.png" sizes="16x16">
    <link rel="icon" type="image/png" href="https://images.truman.edu/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="https://images.truman.edu/favicon-96x96.png" sizes="96x96">
    <link rel="stylesheet" href="main.css">
    <title>ORTS - Override Requests</title>
    <style>
        h2 {
            text-align: center;
            font-family: nexabold, Arial, Helvetica, sans-serif;
        }

        /* Originally written by Thao Phung for the table */
        .tiny-search {
            width: 35px;
        }

        .small-search {
            width: 60px;
        }

        .med-search {
            width: 70px;
        }

        .big-search {
            width: 100px;
        }

        #request-table {
            border-collapse: collapse;
            width: 100%;
        }

        #request-table td,
        #request-table th {
            border: 1px solid #ddd;
            padding: 8px;
        }

        #request-table th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: left;
            color: white;
        }

        .request-item:hover {
            background-color: #f2f2f2;
        }

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f1f1f1;
            min-width: 400px;
            box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
            z-index: 1;
        }

        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .dropdown-content a:hover {
            background-color: #ddd;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }
    </style>
</head>

<body class="grid-container">
<div class="grid-item header right truman-dark-bg"></div>
<div class="grid-item header left truman-dark-bg"></div>
<div class="grid-item header center truman-dark-bg">
    <center>
        <span style="float:left;">
          <img id="logo" src="assets/truman.png"/>
        </span>
        <span style="float:right">
          <div id="main-title" style="font-size:50px;font-family:nexabold;">
              Override Tracking System
          </div>
          <div style="font-size:20px;font-family:nexabook;">
              Departments of Mathematics, Computer Science, and Statistics
          </div>
        </span>
    </center>
</div>

<div class="grid-item navbar left truman-dark-bg"></div>
<div class="grid-item navbar right truman-dark-bg"></div>

<div class="grid-item sidebar left"></div>
<div class="grid-item sidebar right"></div>

<div class="grid-item navbar center">
    <ul id="nav-list" class="truman-dark-bg">
        <li class="nav-item"><a class="active" href="admin-request-list.php">Current Semester</a></li>
        <li class="nav-item"><a href="admin-archive.php">Archive</a></li>
        <li class="nav-item" style="float:right;"><a href="#">Log Out</a></li>
        <li class="nav-item" style="float:right;"><a href="admin-profile.php">Profile</a></li>
    </ul>
</div>

<div class="grid-item content">
    <h2>Override Requests</h2>
    <!-- Table Created By: Thao Phung -->
    <table id="request-table">
        <colgroup>
            <!--
            <th>Select</th>
            <th>Status</th>
            <th>Recieved</th>
            <th>Last</th>
            <th>First</th>
            <th>Banner ID</th>
            <th>Dept</th>
            <th>Num</th>
            <th>CRN</th>
            <th>Semester</th>
            -->
            <col style="width:5%;">
            <col style="width:33%;">
            <col style="width:19%;">
            <col style="width:9%;">
            <col style="width:9%;">
            <col style="width:7%;">
            <col style="width:5%;">
            <col style="width:4%;">
            <col style="width:4%;">
            <col style="width:5%;">
        <colgroup>
        <tr>
            <td class="border-none">
                <div class="dropdown">
                    <button class="dropbtn" style="padding-right:0px;padding-left: 0px;">Bulk</button>
                    <div class="dropdown-content">
                        <a href="#">Approve</a>
                        <a href="#">Deny</a>
                        <a href="#">Provisionally Approve</a>
                        <a href="#">Marked As Entered To Banner</a>
                        <a href="#">Archive</a>
                    </div>
                </div>
            </td>
            <td class="border-none">
                <div class="dropdown">
                    <button class="dropbtn">Filter</button>
                    <div class="dropdown-content">
                        <p style="margin-left:10px;">Status:</p>
                        <input type="radio" id="all_stat" name="status" value="all_stat" class="default" style="margin:10px;">
                        <label for="all_stat">All</label><br>
                        <input type="radio" id="received" name="status" value="Received" style="margin:10px;">
                        <label for="received"><i class="material-icons" style="color:orange">warning</i> Received</label><br>
                        <input type="radio" id="approved" name="status" value="Approved" style="margin:10px;">
                        <label for="approved"><i class="material-icons" style="color:green">done</i> Approved</label><br>
                        <input type="radio" id="papproved" name="status" value="Provisionally Approved" style="margin:10px;">
                        <label for="papproved"><i class="material-icons" style="color:yellowgreen">done</i> Provisionally Approved</label><br>
                        <input type="radio" id="denied" name="status" value="Denied" style="margin:10px;">
                        <label for="denied"><i class="material-icons" style="color:red">cancel</i> Denied</label><br>
                        <input type="radio" id="faculty" name="status" value="Needs Faculty" style="margin:10px;">
                        <label for="faculty"><i class="material-icons" style="color:orange">warning</i> Needs Faculty</label><br>
                        <p style="margin-left:10px;">In Banner:</p>
                        <input type="radio" id="both" name="banner" value="both" style="margin:10px;" class="default">
                        <label for="both">Both</label><br>
                        <input type="radio" id="inbanner" name="banner" value="inbanner" style="margin:10px;">
                        <label for="inbanner"><i class="material-icons" style="color:green">done</i> Not In Banner</label><br>
                        <input type="radio" id="notinbanner" name="banner" value="notinbanner" style="margin:10px;">
                        <label for="notinbanner"><i class="material-icons" style="color:green">done_all</i> In Banner</label><br>
                    </div>
                </div>
            </td>
            <td class="border-none">
                <div class="dropdown">
                    <button class="dropbtn">Sort</button>
                    <div class="dropdown-content">
                        <input type="radio" id="datedescending" name="datesort" value="datedescending" style="margin:10px;" class="default">
                        <label for="datedescending">Descending</label><br>
                        <input type="radio" id="dateacending" name="datesort" value="dateascending" style="margin:10px;">
                        <label for="dateacending">Ascending</label><br>
                    </div>
                </div>
            </td>
            <td class="border-none">
                <input type="text" id="last" class="big-search search-form" placeholder="Last">
            </td>
            <td class="border-none">
                <input type="text" id="first" class="big-search search-form" placeholder="First">
            </td>
            <td class="border-none">
                <input type="text" id="bannerid" class="med-search numeric" placeholder="Banner ID">
            </td>
            <td class="border-none">
                <div class="dropdown">
                    <button class="dropbtn" style="padding-right:0px;padding-left: 0px;">Filter</button>
                    <div class="dropdown-content">
                        <input type="radio" id="all_dept" name="dept" value="all_dept" style="margin:10px;" class="default">
                        <label for="all_dept">All</label><br>
                        <input type="radio" id="cs" name="dept" value="CS" style="margin:10px;">
                        <label for="cs">CS</label><br>
                        <input type="radio" id="math" name="dept" value="MATH" style="margin:10px;">
                        <label for="math">MATH</label><br>
                        <input type="radio" id="stat" name="dept" value="STAT" style="margin:10px;">
                        <label for="stat">STAT</label><br>
                        <input type="radio" id="tru" name="dept" value="TRU" style="margin:10px;">
                        <label for="tru">TRU</label><br>
                        <input type="radio" id="jins" name="dept" value="JINS" style="margin:10px;">
                        <label for="jins">JINS</label><br>
                    </div>
                </div>
                </a>
            </td>
            <td class="border-none">
                <input type="text" id="course_num" class="tiny-search numeric" placeholder="#">
            </td>
            <td class="border-none">
                <input type="text" id="crn" class="tiny-search numeric" placeholder="CRN">
            </td>
            <td class="border-none">
                <input type="text" id="semester" class="small-search numeric" placeholder="Semester">
            </td>
        </tr>
        <tr class="truman-dark-bg">
            <th>Select</th>
            <th>Status</th>
            <th>Recieved</th>
            <th>Last</th>
            <th>First</th>
            <th>Banner ID</th>
            <th>Dept</th>
            <th>Num</th>
            <th>CRN</th>
            <th>Semester</th>
        </tr>
    </table>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    let requests;

    function getStatusHtml(request)
    {
        switch (request.status)
        {
            case 'Received':
                return '<i class="material-icons" style="color:orange">warning</i> Received';
            case 'Approved':
                if(request.banner)
                    return '<i class="material-icons" style="color:green">done_all</i> Approved: In Banner';
                else
                    return '<i class="material-icons" style="color:green">done</i> Approved';
            case 'Provisionally Approved':
                if(request.banner)
                    return '<i class="material-icons" style="color:yellowgreen">done_all</i> Provisionally Approved: In Banner';
                else
                    return '<i class="material-icons" style="color:yellowgreen">done</i> Provisionally Approved';
            case 'Denied':
                return '<i class="material-icons" style="color:red">cancel</i> Provisionally Approved';
            case 'Requires Faculty Approval':
                return '<i class="material-icons" style="color:orange">warning</i> Requires Faculty Approval';
        }
    }

    function sortTable()
    {
        requests.sort(function (lhs, rhs) {
            if ($("input[name='datesort']:checked").val() === "datedescending")
                return lhs.last_modified < rhs.last_modified ? -1 : +(lhs.last_modified > rhs.last_modified);
            else
                return lhs.last_modified > rhs.last_modified ? -1 : +(lhs.last_modified < rhs.last_modified);
        });
    }

    function matchFilter(request)
    {
        let out = true;

        // First, Remove anything that doesn't match a filter
        let filter;
        out &= ((filter = $("input[name='status']:checked").val()) === "all_stat") || (filter  === request.status);
        out &= ((filter = $("input[name='banner']:checked").val()) === "both") || ((filter === "inbanner") === request.banner);
        out &= ((filter = $("input[name='dept']:checked").val()) === "all_dept") || (filter === request.section.course.department.department)

        // Next, remove anything that doesn't have a search param as a substring
        out &= request.student.first_name.toLowerCase().startsWith($('#first').val().toLowerCase());
        out &= request.student.last_name.toLowerCase().startsWith($('#last').val().toLowerCase());
        out &= request.section.crn.startsWith($('#crn').val());
        out &= request.student.banner_id.startsWith($('#bannerid').val());
        out &= String(request.section.course.course_num).startsWith($('#course_num').val());
        out &= request.section.semester.semester.startsWith($('#semester').val());

        return out;
    }

    function renderTable()
    {
        $('.request-item').remove();

        requests.forEach(function writeToTable(request)
        {
            if(matchFilter(request))
                $('#request-table').append(request.rowHtml);
        })
    }

    /*
     *  MAIN
     */
    $(function()
    {
        // Import the requests from the database into javascript
        requests = JSON.parse('<?php echo json_encode($requests); ?>');
        // construct the html code representing a row
        requests.forEach(function createHtml(request)
        {
            // The opening tag including the get request for the details page
            const tdOpen = '<td onclick="window.location=\'admin-request-details.php?id=' + request.id + '\'">';

            let out = '<tr class="request-item">';
            out +=      '<td><input type="checkbox"></td>';
            out +=      tdOpen + getStatusHtml(request) + '</td>';
            out +=      tdOpen + request.last_modified + '</td>';
            out +=      tdOpen + request.student.last_name + '</td>';
            out +=      tdOpen + request.student.first_name + '</td>';
            out +=      tdOpen + request.student.banner_id + '</td>';
            out +=      tdOpen + request.section.course.department.department + '</td>';
            out +=      tdOpen + request.section.course.course_num + '</td>';
            out +=      tdOpen + request.section.crn + '</td>';
            out +=      tdOpen + request.section.semester.semester + '</td>';
            out += '</tr>';

            request.rowHtml = out;
        });

        $(document).on("input", ".numeric", function()
        {
            this.value = this.value.replace(/\D/g,'');
        });

        $(".default").prop('checked', true);

        sortTable();
        renderTable();

        $('input[name="datesort"]').on("click", function(){ sortTable(); });
        $('input[type="radio"]').on("click", function(){ renderTable(); });
        $('input[type="text"]').on("keyup", function(){ renderTable(); });
    })
</script>
</body>
</html>

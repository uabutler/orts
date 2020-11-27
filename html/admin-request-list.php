<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="icon" type="image/png" href="https://images.truman.edu/favicon-16x16.png" sizes="16x16">
    <link rel="icon" type="image/png" href="https://images.truman.edu/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="https://images.truman.edu/favicon-96x96.png" sizes="96x96">
    <link rel="stylesheet" href="main.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <title>ORTS - Main Page</title>
    <style>
        h2 {
            text-align: center;
            font-family: nexabold, Arial, Helvetica, sans-serif;
        }

        /* Originally written by Thao Phung for the table */
        .float-right {
            float: right;
        }

        .small-search {
            width: 60px;
        }

        .big-search {
            width: 100px;
        }

        .request-table {
            border-collapse: collapse;
            width: 100%;
        }

        .request-table td,
        .request-table th {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .request-table th {
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
        <li class="nav-item"><a class="active" href="#home">Current Semester</a></li>
        <li class="nav-item"><a href="#news">Archive</a></li>
        <li class="nav-item" style="float:right;"><a href="#about">Log Out</a></li>
        <li class="nav-item" style="float:right;"><a href="#about">Profile</a></li>
    </ul>
</div>

<div class="grid-item content">
    <h2>Override Requests</h2>
    <!-- Table Created By: Thao Phung -->
    <table class="request-table">
        <tr>
            <td class="border-none" style="padding-right:0;">
                <div class="dropdown">
                    <button class="dropbtn">Bulk</button>
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
                        <form action="/action_page.php">
                            <p style="margin-left:10px;">Status:</p>
                            <input type="radio" id="all_stat" name="status" value="all_stat" style="margin:10px;">
                            <label for="all_stat">All</label><br>
                            <input type="radio" id="received" name="status" value="received" style="margin:10px;">
                            <label for="received"><i class="material-icons"
                                                     style="color:orange">warning</i>Received</label><br>
                            <input type="radio" id="approved" name="status" value="approved" style="margin:10px;">
                            <label for="approved"><i class="material-icons" style="color:green">done</i>Approved</label><br>
                            <input type="radio" id="papproved" name="status" value="papproved" style="margin:10px;">
                            <label for="papproved"><i class="material-icons" style="color:yellowgreen">done</i>Provisionally
                                Approved</label><br>
                            <input type="radio" id="denied" name="status" value="denied" style="margin:10px;">
                            <label for="denied"><i class="material-icons" style="color:red">cancel</i>Denied</label><br>
                            <input type="radio" id="faculty" name="status" value="faculty" style="margin:10px;">
                            <label for="faculty"><i class="material-icons" style="color:orange">warning</i>Needs Faculty</label><br>
                            <p style="margin-left:10px;">In Banner:</p>
                            <input type="radio" id="both" name="banner" value="both" style="margin:10px;">
                            <label for="both">Both</label><br>
                            <input type="radio" id="inbanner" name="banner" value="inbanner" style="margin:10px;">
                            <label for="inbanner"><i class="material-icons" style="color:green">done</i>Not In
                                Banner</label><br>
                            <input type="radio" id="notinbanner" name="banner" value="notinbanner" style="margin:10px;">
                            <label for="notinbanner"><i class="material-icons" style="color:green">done_all</i>In Banner</label><br>
                        </form>
                    </div>
                </div>
            </td>
            <td class="border-none">
                <div class="dropdown">
                    <button class="dropbtn">Sort</button>
                    <div class="dropdown-content">
                        <a href="#"><i class="material-icons">arrow_drop_up</i>Newest to Oldest</a>
                        <a href="#"><i class="material-icons">arrow_drop_down</i>Oldest to Newest</a>
                    </div>
                </div>
            </td>
            <td class="border-none">
                <form action="/request" method='POST'>
                    <input
                            type="text"
                            name="search_value"
                            class="small-search search-form" placeholder="First"
                    />
                    <input
                            style="display: none;"
                            type="text"
                            name="sort_type"
                            value="search_by_first_name"
                    />
                </form>
            </td>
            <td class="border-none">
                <form action="/request" method='POST'>
                    <input
                            type="text"
                            name="search_value"
                            class="small-search search-form" placeholder="Last"
                    />
                    <input
                            style="display: none;"
                            type="text"
                            name="sort_type"
                            value="search_by_last_name"
                    />
                </form>
            </td>
            <td class="border-none" style="padding-right:0;">
                <input type="text" class="big-search" placeholder="Banner ID">
            </td>
            <td class="border-none" style="padding-right:0;">
                <div class="dropdown">
                    <button class="dropbtn">Filter</button>
                    <div class="dropdown-content">
                        <form action="/action_page.php">
                            <input type="radio" id="all_dept" name="dept" value="all_dept" style="margin:10px;">
                            <label for="all_dept">All</label><br>
                            <input type="radio" id="cs" name="dept" value="cs" style="margin:10px;">
                            <label for="cs">CS</label><br>
                            <input type="radio" id="math" name="dept" value="math" style="margin:10px;">
                            <label for="math">MATH</label><br>
                            <input type="radio" id="stat" name="dept" value="stat" style="margin:10px;">
                            <label for="stat">STAT</label><br>
                            <input type="radio" id="tru" name="dept" value="tru" style="margin:10px;">
                            <label for="tru">TRU</label><br>
                            <input type="radio" id="jins" name="dept" value="jins" style="margin:10px;">
                            <label for="jins">JINS</label><br>
                        </form>
                    </div>
                </div>
                </a>
            </td>
            <td class="border-none" style="padding-right:0;">
                <div class="dropdown">
                    <button class="dropbtn">Sort</button>
                    <div class="dropdown-content">
                        <a href="#"><i class="material-icons">arrow_drop_up</i>Ascending</a>
                        <a href="#"><i class="material-icons">arrow_drop_down</i>Descending</a>
                    </div>
                </div>
            </td>
            <td class="border-none" style="padding-right:0;">
                <input type="text" class="small-search" placeholder="CRN">
            </td>
            <td class="border-none" style="padding-right:0;">
                <input type="text" class="small-search" placeholder="Semester">
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
            <th>#</th>
            <th>CRN</th>
            <th>Semester</th>
        </tr>
        <tr class="request-item">
            <td><input type="checkbox"></td>
            <td><i class="material-icons" style="color: green;">done</i>Approved: Not in Banner
            </td>
            <td>10/06/2020</td>
            <td>John</td>
            <td>Doe</td>
            <td>232443301</td>
            <td>CS</td>
            <td>310</td>
            <td>3128</td>
            <td>202160</td>
        </tr>
        <tr class="request-item">
            <td><input type="checkbox"></td>
            <td><i class="material-icons" style="color: yellowgreen;">done</i>Provisionally Approved: Not in Banner</td>
            <td>10/06/2020</td>
            <td>John</td>
            <td>Doe</td>
            <td>232443301</td>
            <td>CS</td>
            <td>310</td>
            <td>3128</td>
            <td>202160</td>
        </tr>
        <tr class="request-item">
            <td><input type="checkbox"></td>
            <td><i class="material-icons" style="color:green;">done_all</i>Approved: In Banner</td>
            <td>10/06/2020</td>
            <td>John</td>
            <td>Doe</td>
            <td>232443301</td>
            <td>CS</td>
            <td>310</td>
            <td>3128</td>
            <td>202140</td>
        </tr>
        <tr class="request-item">
            <td><input type="checkbox"></td>
            <td><i class="material-icons" style="color:yellowgreen;">done_all</i>Provisionally Approved: In Banner
                <i class="material-icons float-right">info</i>
            </td>
            <td>10/06/2020</td>
            <td>John</td>
            <td>Doe</td>
            <td>232443301</td>
            <td>CS</td>
            <td>310</td>
            <td>3128</td>
            <td>202140</td>
        </tr>
        <tr class="request-item">
            <td><input type="checkbox"></td>
            <td><i class="material-icons" style="color:yellowgreen;">done_all</i>Provisionally Approved: In Banner <i
                    class="material-icons float-right">attach_file</i>
            </td>
            <td>10/06/2020</td>
            <td>John</td>
            <td>Doe</td>
            <td>232443301</td>
            <td>CS</td>
            <td>310</td>
            <td>3128</td>
            <td>202140</td>
        </tr>
        <tr class="request-item">
            <td><input type="checkbox"></td>
            <td><i class="material-icons" style="color: orange;">warning</i>Needs Approval</td>
            <td>10/06/2020</td>
            <td>John</td>
            <td>Doe</td>
            <td>232443301</td>
            <td>CS</td>
            <td>310</td>
            <td>3128</td>
            <td>202140</td>
        </tr>
        <tr class="request-item">
            <td><input type="checkbox"></td>
            <td><i class="material-icons" style="color: orange;">warning</i>Needs Approval
                <i class="material-icons float-right">info</i>
            </td>
            <td>10/06/2020</td>
            <td>John</td>
            <td>Doe</td>
            <td>232443301</td>
            <td>CS</td>
            <td>310</td>
            <td>3128</td>
            <td>202140</td>
        </tr>
        <tr class="request-item">
            <td><input type="checkbox"></td>
            <td><i class="material-icons" style="color: orange;">warning</i>Needs Approval<i
                    class="material-icons float-right">attach_file</i>
            </td>
            <td>10/06/2020</td>
            <td>John</td>
            <td>Doe</td>
            <td>232443301</td>
            <td>CS</td>
            <td>310</td>
            <td>3128</td>
            <td>202140</td>
        </tr>
        <tr class="request-item">
            <td><input type="checkbox"></td>
            <td><i class="material-icons" style="color:red;">cancel</i>Denied</td>
            <td>10/06/2020</td>
            <td>John</td>
            <td>Doe</td>
            <td>232443301</td>
            <td>CS</td>
            <td>310</td>
            <td>3128</td>
            <td>202160</td>
        </tr>
    </table>
</div>
</body>
</html>

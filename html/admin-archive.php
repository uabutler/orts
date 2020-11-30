<?php include_once 'database/courses_db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Use Truman's default favicons -->
    <link rel="icon" type="image/png" href="https://images.truman.edu/favicon-16x16.png" sizes="16x16">
    <link rel="icon" type="image/png" href="https://images.truman.edu/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="https://images.truman.edu/favicon-96x96.png" sizes="96x96">

    <link rel="stylesheet" href="main.css">

    <title>ORTS - Archives</title>
    <style>
        .content-grid-container {
            display: grid;
            grid-gap: 10px;
            grid-template-columns: 1fr 1fr;
            padding: 0;
        }

        .active-semesters {
            grid-column: 1;
            grid-row: 1;
        }

        .inactive-semesters {
            grid-column: 2;
            grid-row: 1;
        }

        h2 {
            font-family: nexabold, sans-serif;
            color: white;
            padding: 10px
        }

        #semester-table {
            border-collapse: collapse;
            width: 100%;
        }

        #semester-table td,
        #semester-table th {
            border: 1px solid #ddd;
            padding: 8px;
        }

        #semester-table th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: left;
            color: white;
        }

        .semester-item:hover {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body class="grid-container">
<div class="grid-item header right truman-dark-bg"></div>
<div class="grid-item header left truman-dark-bg"></div>
<div class="grid-item header center truman-dark-bg">
    <div style="text-align: center;">
        <span style="float:left;">
          <img id="logo" src="assets/truman.png" alt="Truman State University"/>
        </span>
        <span style="float:right">
          <div id="main-title" style="font-size:50px;font-family:nexabold;">
              Override Tracking System
          </div>
          <div style="font-size:20px;font-family:nexabook;">
              Departments of Mathematics, Computer Science, and Statistics
          </div>
        </span>
    </div>
</div>

<div class="grid-item navbar left truman-dark-bg"></div>
<div class="grid-item navbar right truman-dark-bg"></div>

<div class="grid-item sidebar left"></div>
<div class="grid-item sidebar right"></div>

<div class="grid-item navbar center">
    <ul id="nav-list" class="truman-dark-bg">
        <li class="nav-item"><a href="admin-request-list.php">Current Semester</a></li>
        <li class="nav-item"><a class="active" href="admin-archive.php">Archive</a></li>
        <li class="nav-item"><a href="admin-functions.php">Admin Functions</a></li>
        <li class="nav-item" style="float:right;"><a href="#">Log Out</a></li>
        <li class="nav-item" style="float:right;"><a href="admin-profile.php">Profile</a></li>
    </ul>
</div>

<div class="grid-item content content-grid-container">
    <div class="grid-item active-semesters">
        <h2 class="truman-dark-bg">Active Semesters</h2>
        <table id="semester-table">
            <tr class="truman-dark-bg">
                <th>Semester</th>
                <th>Code</th>
            </tr>
            <?php
            $active = Semester::listActive();
            foreach ($active as $semester)
            {
                echo '<tr class="semester-item" onclick="window.location=\'admin-request-list.php?semester=' . $semester->getCode() . '\'">';
                echo '<td>' . $semester->getDescription() . '</td>';
                echo '<td>' . $semester->getCode() . '</td>';
                echo '</tr>';
            }
            ?>
        </table>
    </div>

    <div class="grid-item inactive-semesters">
        <h2 class="truman-dark-bg">Inactive Semesters</h2>
        <table id="semester-table">
            <tr class="truman-dark-bg">
                <th>Semester</th>
                <th>Code</th>
            </tr>
            <?php
            $inactive = Semester::listInactive();
            foreach ($inactive as $semester)
            {
                echo '<tr class="semester-item" onclick="window.location=\'admin-request-list.php?semester=' . $semester->getCode() . '\'">';
                echo '<td>' . $semester->getDescription() . '</td>';
                echo '<td>' . $semester->getCode() . '</td>';
                echo '</tr>';
            }
            ?>
        </table>
    </div>
</div>
</body>
</html>

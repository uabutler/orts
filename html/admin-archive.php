<?php include_once 'database/courses_db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>ORTS - Archives</title>
    <?php require 'php/common-head.php'; ?>
    <link rel="stylesheet" href="css/admin-archive.css">
</head>

<body class="grid-container">
<?php require_once 'php/header.php'; ?>
<?php require_once 'php/navbar.php'; facultyNavbar("Archive"); ?>

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
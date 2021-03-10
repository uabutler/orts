<?php
require_once '../php/database/courses.php';
require_once '../php/database/faculty.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');

$active_semesters = Semester::listActive();
$inactive_semesters = Semester::listInactive();

$faculty = Faculty::list();

$majors = Major::list();
$minors = Minor::list();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>ORTS - Administration</title>
    <?php require '../php/common-head.php'; ?>
    <link rel="stylesheet" href="/css/admin/administation.css">
    <script src="/js/admin/administration.js"></script>
</head>
<body class="grid-container">
<?php require_once '../php/header.php'; ?>
<?php require_once '../php/navbar.php'; facultyNavbar("Administration"); ?>

<div class="grid-item content content-grid-container">
    <div>
        <h2>Semesters</h2>
        <table>
            <tr>
                <td><input type="text" name="semester_code" id="semester_code" placeholder="Code"></td>
                <td><input type="text" name="semester_name" id="semester_name" placeholder="Name"></td>
                <td><button id="semester">Add</button></td>
            </tr>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Action</th>
            </tr>
            <?php foreach ($active_semesters as $semester): ?>
            <tr>
                <td><?= $semester->getCode() ?></td>
                <td><?= $semester->getDescription() ?></td>
                <td><button id="semester">Archive</button></td>
            </tr>
            <?php endforeach; ?>
            <?php foreach ($inactive_semesters as $semester): ?>
            <tr>
                <td><?= $semester->getCode() ?></td>
                <td><?= $semester->getDescription() ?></td>
                <td><button id="semester">Delete</button></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <div>
        <h2>Faculty</h2>
        <table>
            <tr>
                <td><input type="text" name="faculty_user" id="faculty_user" placeholder="Username"></td>
                <td><input type="text" name="faculty_first" id="faculty_first" placeholder="First Name"></td>
                <td><input type="text" name="faculty_last" id="faculty_last" placeholder="Last Name"></td>
                <td><button id="faculty">Add</button></td>
            </tr>
            <tr>
                <th>Username</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Action</th>
            </tr>
            <?php foreach ($faculty as $fac): ?>
            <tr>
                <td><?= $fac->getEmail() ?></td>
                <td><?= $fac->getFirstName() ?></td>
                <td><?= $fac->getLastName() ?></td>
                <td><button id="semester">Delete</button></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <div>
        <h2>Majors</h2>
        <table>
            <colgroup>
                <col style="width: 100%;">
            </colgroup>
            <tr>
                <th>Major Name</th>
                <th><button id="major-add">Bulk Add</button></th>
            </tr>
            <?php foreach ($majors as $major): ?>
            <tr>
                <td><?= $major ?></td>
                <td><button id="semester">Delete</button></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <div>
        <h2>Minors</h2>
        <table>
            <colgroup>
                <col style="width: 100%;">
            </colgroup>
            <tr>
                <th>Minor Name</th>
                <th><button id="minor-add">Bulk Add</button></th>
            </tr>
            <?php foreach ($minors as $minor): ?>
            <tr>
                <td><?= $minor ?></td>
                <td><button id="semester">Delete</button></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <div style="grid-column: 1 / span 2;">
        <h2>Courses</h2>
    </div>
    <div id="overlay">
        <div id="popup">
            <h3>Bulk Add</h3>
            <p>Put each entry on its own line.</p>
            <textarea id="entries" rows="10"></textarea>
            <button class="ol-btn" id="add-programs">Submit</button>
            <button class="ol-btn" id="cancel">Cancel</button>
        </div>
    </div>
</div>
</body>
</html>
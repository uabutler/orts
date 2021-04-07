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
<body>
<?php require_once '../php/header.php'; ?>
<?php require_once '../php/navbar.php'; facultyNavbar("Administration"); ?>

<section class="content-grid-container">
    <div>
        <h2>Semesters</h2>
        <table>
            <tr>
                <td class="ui form field">
                    <input type="text" name="semester_code" id="semester_code" placeholder="Code">
                </td>
                <td class="ui form field">
                    <input type="text" name="semester_name" id="semester_name" placeholder="Name">
                </td>
                <td><div class="ui button" id="semester">Add</div></td>
            </tr>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Action</th>
            </tr>
            <?php foreach ($active_semesters as $semester): ?>
            <tr>
                <td class="clickable-row" onclick="window.location='administration.php?semester=<?= $semester->getCode() ?>'"><?= $semester->getCode() ?></td>
                <td class="clickable-row" onclick="window.location='administration.php?semester=<?= $semester->getCode() ?>'"><?= $semester->getDescription() ?></td>
                <td><div class="ui button" id="semester">Archive</div></td>
            </tr>
            <?php endforeach; ?>
            <?php foreach ($inactive_semesters as $semester): ?>
            <tr>
                <td><?= $semester->getCode() ?></td>
                <td><?= $semester->getDescription() ?></td>
                <td><div class="ui button" id="semester">Delete</div></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <div>
        <h2>Faculty</h2>
        <table>
            <tr>
                <td class="ui form field">
                    <input type="text" name="faculty_user" id="faculty_user" placeholder="Username">
                </td>
                <td class="ui form field">
                    <input type="text" name="faculty_first" id="faculty_first" placeholder="First Name">
                </td>
                <td class="ui form field">
                    <input type="text" name="faculty_last" id="faculty_last" placeholder="Last Name">
                </td>
                <td><div class="ui button" id="faculty">Add</div></td>
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
                <td><div class="ui button" id="semester">Delete</div></td>
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
                <th><div class="ui button" id="major-add">Bulk Add</div></th>
            </tr>
            <?php foreach ($majors as $major): ?>
            <tr>
                <td><?= $major ?></td>
                <td><div class="ui button major-del" data-value="<?= $major ?>">Delete</div></td>
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
                <th><div class="ui button" id="minor-add">Bulk Add</div></th>
            </tr>
            <?php foreach ($minors as $minor): ?>
            <tr>
                <td><?= $minor ?></td>
                <td><div class="ui button minor-del" data-value="<?= $minor ?>">Delete</div></td>
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
            <div class="ui form">
                <div class="field">
                   <label>Put each entry on its own line.</label>
                    <textarea id="entries" rows="30"></textarea>
                </div>
                <div id="add-programs" class="ui right floated button" tabindex="0">Submit</div>
                <div id="cancel" class="ui right floated button">Cancel</div>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</section>
</body>
</html>
<?php
include_once '../php/database/courses.php';
$active = Semester::listActive();
$inactive = Semester::listInactive();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>ORTS - Archives</title>
    <?php require '../php/common-head.php'; ?>
    <link rel="stylesheet" href="/css/admin/archive.css">
</head>

<body>
<?php require_once '../php/header.php'; ?>
<?php require_once '../php/navbar.php'; facultyNavbar("Archive"); ?>

<section class="content-grid-container">
    <div class="grid-item active-semesters">
        <h2 class="truman-dark-bg">Active Semesters</h2>
        <table id="semester-table">
            <tr class="truman-dark-bg">
                <th>Semester</th>
                <th>Code</th>
            </tr>
            <?php foreach ($active as $semester): ?>
            <tr class="semester-item" onclick="window.location='/admin/request-list.php?semester=<?= $semester->getCode() ?>'">
                <td><?= $semester->getDescription() ?></td>
                <td><?= $semester->getCode() ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="grid-item inactive-semesters">
        <h2 class="truman-dark-bg">Inactive Semesters</h2>
        <table id="semester-table">
            <tr class="truman-dark-bg">
                <th>Semester</th>
                <th>Code</th>
            </tr>
            <?php foreach ($inactive as $semester): ?>
                <tr class="semester-item" onclick="window.location='/admin/request-list.php?semester=<?= $semester->getCode() ?>'">
                    <td><?= $semester->getDescription() ?></td>
                    <td><?= $semester->getCode() ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</section>
</body>
</html>
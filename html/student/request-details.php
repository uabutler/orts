<?php
include_once '../php/database/requests.php';

if (isset($_GET['id']))
    $request = Request::getById(intval($_GET['id']));
else
    $request = null;

if (is_null($request))
    include '../error/error400.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>ORTS - Request Details</title>
    <?php require '../php/common-head.php'; ?>
    <link rel="stylesheet" href="/css/student/request-details.css">
    <script>
        REQUEST_ID = <?= $_GET['id'] ?>;
        REQUEST_STATUS = "<?= $request->getStatus() ?>";
    </script>
    <script src="/js/student/request-details.js"></script>
</head>

<body class="grid-container">
<?php require_once '../php/header.php'; ?>
<?php require_once '../php/navbar.php'; studentNavbar("Active Requests"); ?>

<div class="grid-item content content-grid-container">
    <div class="orstatus">
        <h2 class="truman-dark-bg">Override Status</h2>
        <table>
            <tr>
                <th>Status:</th>
                <td><?= $request->getStatusHtml() ?></td>
            </tr>
            <tr>
                <th>Date Modified:</th>
                <td><?= $request->getLastModified() ?></td>
            </tr>
            <tr>
                <th>Date Received:</th>
                <td>1970-01-01T00:00:00</td>
            </tr>
            <tr>
                <th style="padding-right:1em">Designated Faculty:</th>
                <td><?= $request->getFaculty()->getLastName() ?>, <?= $request->getFaculty()->getFirstName() ?></td>
            </tr>
        </table>
    </div>
    <div class="courseinfo">
        <h2 class="truman-dark-bg">Course Information</h2>
        <table>
            <tr>
                <th style="padding-right:1em">Semester:</th>
                <td id="semester"><?= $request->getSection()->getSemester()->getDescription() ?></td>
                <td rowspan="3">
                    <a class="edit" id="course-edit"><i class="material-icons" style="color:white">create</i></a>
                </td>
            </tr>
            <tr>
                <th>Course:</th>
                <td>
                    <?= $request->getSection()->getCourse()->getDepartment()->getDept() ?>
                    <?= $request->getSection()->getCourse()->getCourseNum() ?>
                    <?php
                        if($request->getSection()->getSectionNum() < 10)
                            echo'0';
                        echo $request->getSection()->getSectionNum();
                    ?>:
                    <?= $request->getSection()->getCourse()->getTitle() ?>
                </td>
            </tr>
            <tr>
                <th>CRN:</th>
                <td><?= $request->getSection()->getCrn() ?></td>
            </tr>
        </table>
    </div>
    <div class="additional">
        <h2 class="truman-dark-bg">Additional Information</h2>
        <table>
            <tr>
                <th>Reason:</th>
                <td><?= $request->getReason() ?></td>
            </tr>
            <tr>
                <th>Explanation:</th>
                <td><textarea readonly><?= $request->getExplanation() ?></textarea></td>
            </tr>
            <td rowspan="2">
                <a class="edit" id="additional-edit"><i class="material-icons" style="color:white">create</i></a>
            </td>
        </table>
    </div>
</div>
</body>
</html>

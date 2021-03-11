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
        REASONS =
            [
                <?php foreach (Request::listReasons() as $reason): ?>
                    '<?= $reason ?>',
                <?php endforeach; ?>
            ];
        CURRENT_REASON = "<?= $request->getReason(); ?>";
    </script>
    <script src="/js/student/request-details.js"></script>
</head>

<body class="grid-container">
<?php require_once '../php/header.php'; ?>
<?php require_once '../php/navbar.php'; studentNavbar("Active Requests"); ?>

<div class="grid-item content content-grid-container">
    <div id="status">
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
    <div id="course">
        <h2 class="truman-dark-bg">Course Information</h2>
        <div class="course-display">
            <table>
                <tr>
                    <th style="padding-right:1em">Semester:</th>
                    <td id="semester"><?= $request->getSection()->getSemester()->getDescription() ?></td>
                    <td rowspan="3">
                        <button class="edit course-edit"><i class="material-icons">create</i></button>
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
        <div class="edit course-edit">
            <button class="submit course-submit">Submit</button>
            <button class="cancel course-cancel">Cancel</button>
        </div>
    </div>
    <div id="additional">
        <h2 class="truman-dark-bg">Additional Information</h2>
        <div class="additional-display">
            <table>
                <tr>
                    <th>Reason:</th>
                    <td id="reason-display"><?= $request->getReason() ?></td>
                    <td rowspan="2">
                        <button class="edit additional-edit"><i class="material-icons">create</i></button>
                    </td>
                </tr>
                <tr>
                    <th>Explanation:</th>
                    <td><textarea readonly id="explanation-display"><?= $request->getExplanation() ?></textarea></td>
                </tr>
            </table>
        </div>
        <div class="edit additional-edit">
            <table>
                <tr>
                    <th>Reason:</th>
                    <td id="reason-cell"></td>
                </tr>
                <tr>
                    <th>Explanation:</th>
                    <td><textarea id="explanation"><?= $request->getExplanation() ?></textarea></td>
                </tr>
            </table>
            <button class="submit additional-submit">Submit</button>
            <button class="cancel additional-cancel">Cancel</button>
        </div>
    </div>
</div>
</body>
</html>

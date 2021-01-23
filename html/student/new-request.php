<?php
require_once '../php/database/students.php';
require_once '../php/database/requests.php';
require_once '../php/database/programs.php';
require_once '../php/auth.php';

Auth::createClient();
Auth::forceAuthenticationStudent();
$student_email = Auth::getUser();
$student = Student::get($student_email);
$student_id = $student->getId();

$departments = Department::list();
$reasons = Request::listReasons();
$semesters = Semester::listActive();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>ORTS - New Request</title>
    <?php require '../php/common-head.php';?>
    <link rel="stylesheet" href="/css/student/new-request.css">
    <script>
        STUDENT_ID = <?php echo $student_id; ?>;
    </script>
    <script src="/js/student/new-request.js"></script>
</head>

<body class="grid-container">
<?php require_once '../php/header.php'; ?>
<?php require_once '../php/navbar.php'; studentNavbar("New Request"); ?>

<div class="grid-item content">
    <div class="info">
        <?php require '../php/new-request-info.php'; ?>
    </div>
    <div>
        <h2 class="truman-dark-bg">Request</h2>
        <table style="width: 100%">
            <colgroup>
                <col>
                <col style="width: 100%;">
            </colgroup>
            <tr>
                <td>Semester:</td>
                <td>
                    <select class="select" id="semester">
                        <?php
                        foreach($semesters as $semester)
                            echo '<option value="'.$semester->getCode().'">'.$semester->getDescription().'</option>';
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Course Dept:</td>
                <td>
                    <select class="select" id="department">
                        <?php
                        foreach($departments as $department)
                            echo '<option value="'.$department.'">'.$department.'</option>';
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Course Num:</td>
                <td><input class="numeric" type="text" placeholder="101" id="course_num"></td>
            </tr>
            <tr>
                <td>Section:</td>
                <td><input class="numeric" type="text" placeholder="01" id="section"></td>
            </tr>
            <tr>
                <td>Title:</td>
                <td><input type="text" id="title" readonly></td>
            </tr>
            <tr>
                <td>CRN:</td>
                <td><input type="text" id="crn" readonly></td>
            </tr>
            <tr>
                <td>Reason:</td>
                <td>
                    <select class="select" id="reason">
                        <?php
                        foreach($reasons as $reason)
                            echo '<option value="'.$reason.'">'.$reason.'</option>';
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Explanation:</td>
                <td><textarea rows="2" id="explanation"></textarea></td>
            </tr>
        </table>
    </div>
    <div>
        <a id="next">Submit &raquo;</a>
    </div>
</div>
</body>
</html>

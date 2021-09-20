<?php
require_once __DIR__ . '/../../php/error/error-handling.php' ;
require_once '../../php/database/students.php';
require_once '../../php/database/requests.php';
require_once '../../php/database/programs.php';
require_once '../../php/auth.php';

Auth::createClient();
Auth::forceAuthenticationStudent(null);
$student_email = Auth::getUser();
$student = Student::get($student_email);
$student_id = $student->getId();

$departments = Department::listActive();
$reasons = Request::listReasons();
$semesters = Semester::listActive();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>ORTS - New Request</title>
    <?php require '../../php/common-head.php';?>
    <link rel="stylesheet" href="/css/student/new-request.css">
    <link rel="stylesheet" href="/css/common/message.css">
    <script>
        STUDENT_ID = <?= $student_id ?>;
    </script>
    <script src="/js/student/course-form.js"></script>
    <script src="/js/student/new-request.js"></script>
    <script src="/js/common/message.js"></script>
</head>

<body>
<?php require_once '../../php/header.php'; ?>
<?php require_once '../../php/navbar.php'; studentNavbar("New Request"); ?>
<?php require_once '../../php/message.php'; ?>

<section>
    <form class="ui form">
        <div class="info">
            <?php require '../../php/new-request-info.php'; ?>
        </div>
        <div>
            <h2 class="truman-dark-bg">Request</h2>

            <div class="field">
                <label>Semester</label>
                <select class="ui dropdown" id="semester">
                    <?php foreach ($semesters as $semester): ?>
                        <option value="<?= $semester->getCode() ?>"><?= $semester->getDescription() ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="fields">
                <div class="five wide field">
                    <label>Course Department</label>
                    <select class="ui dropdown" id="department">
                        <option value="">Select Department</option>
                        <?php foreach ($departments as $department): ?>
                            <option value="<?= $department ?>"><?= $department ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="six wide field">
                    <label>Course Number</label>
                    <input class="numeric" type="text" placeholder='Ex. "101"' id="course_num">
                </div>
                <div class="five wide field">
                    <label>Section</label>
                    <input class="numeric" type="text" placeholder='Ex. "01"' id="section">
                </div>
            </div>
            <div class="fields">
                <div class="eleven wide field">
                    <label>Title</label>
                    <div class="ui icon input disabled">
                        <input type="text" id="course_title" readonly tabindex="-1">
                        <i class="icon"></i>
                    </div>
                </div>
                <div class="five wide field">
                    <label>CRN</label>
                    <div class="ui icon input disabled">
                        <input type="text" id="crn" readonly tabindex="-1">
                        <i class="icon"></i>
                    </div>
                </div>
            </div>
            <div class="field">
                <label>Reason</label>
                <select class="ui dropdown" id="reason">
                    <option value="">Select a reason</option>
                    <?php foreach ($reasons as $reason): ?>
                        <option value="<?= $reason ?>"><?= $reason ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Explanation</label>
                <textarea id="explanation" rows="3"></textarea>
            </div>
            <div id="next" class="ui right floated button">Submit</div>
        </div>
    </form>
</section>
</body>
</html>

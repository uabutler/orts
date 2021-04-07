<?php
include_once '../php/database/requests.php';

if (isset($_GET['id']))
    $request = Request::getById(intval($_GET['id']));
else
    $request = null;

if (is_null($request))
    include '../error/error400.php';

$departments = Department::list();
$semesters = Semester::listActive();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>ORTS - Request Details</title>
    <?php require '../php/common-head.php'; ?>
    <link rel="stylesheet" href="/css/student/request-details.css">
    <script>
        REQUEST_ID = <?= $_GET['id'] ?>;
    </script>
    <script src="/js/student/course-form.js"></script>
    <script src="/js/student/request-details.js"></script>
</head>

<body>
<?php require_once '../php/header.php'; ?>
<?php require_once '../php/navbar.php'; studentNavbar("Active Requests"); ?>

<section class="content-grid-container">
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
        <div id="course-display">
            <table>
                <tr>
                    <th style="padding-right:1em">Semester:</th>
                    <td id="semester-display"><?= $request->getSection()->getSemester()->getDescription() ?></td>
                    <td rowspan="3">
                        <button id="course-edit-button" class="edit"><i class="material-icons">create</i></button>
                    </td>
                </tr>
                <tr>
                    <th>Course:</th>
                    <td id="section-display">
                        <?= $request->getSection()->getCourse()->getDepartment()->getDept() ?>
                        <?= $request->getSection()->getCourse()->getCourseNum() ?>
                        <?php
                        if($request->getSection()->getSectionNum() < 10)
                            echo '0';
                        echo $request->getSection()->getSectionNum();
                        ?>:
                        <?= $request->getSection()->getCourse()->getTitle() ?>
                    </td>
                </tr>
                <tr>
                    <th>CRN:</th>
                    <td id="crn-display"><?= $request->getSection()->getCrn() ?></td>
                </tr>
            </table>
        </div>
        <div id="course-edit" class="edit">
            <form id="course-form" class="ui form">
                <div class="field">
                    <label>Semester</label>
                    <select class="ui dropdown" id="semester">
                        <option value="<?= $request->getSection()->getSemester()->getCode() ?>"><?= $request->getSection()->getSemester()->getDescription() ?></option>
                        <?php foreach ($semesters as $semester): ?>
                            <?php if ($semester->getCode() !== $request->getSection()->getSemester()->getCode()): ?>
                                <option value="<?= $semester->getCode() ?>"><?= $semester->getDescription() ?></option>
                            <?php endif;?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="fields">
                    <div class="five wide field">
                        <label>Department</label>
                        <select class="ui dropdown" id="department">
                            <option value="<?= $request->getSection()->getCourse()->getDepartment()->getDept() ?>"><?= $request->getSection()->getCourse()->getDepartment()->getDept() ?></option>
                            <?php foreach ($departments as $department): ?>
                                <?php if ($department !== $request->getSection()->getCourse()->getDepartment()->getDept()): ?>
                                    <option value="<?= $department ?>"><?= $department ?></option>
                                <?php endif;?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="six wide field">
                        <label>Course Number</label>
                        <input class="numeric" type="text" id="course_num" value="<?= $request->getSection()->getCourse()->getCourseNum() ?>">
                    </div>
                    <div class="five wide field">
                        <label>Section</label>
                        <input class="numeric" type="text" placeholder="01" id="section" value="<?= $request->getSection()->getSectionNum() ?>">
                    </div>
                </div>
                <div class="fields">
                    <div class="eleven wide field">
                        <label>Title</label>
                        <div class="ui icon input disabled">
                            <input type="text" id="course_title" readonly tabindex="-1" value="<?= $request->getSection()->getCourse()->getTitle() ?>">
                            <i class="icon"></i>
                        </div>
                    </div>
                    <div class="five wide field">
                        <label>CRN</label>
                        <div class="ui icon input disabled">
                            <input type="text" id="crn" readonly tabindex="-1" value="<?= $request->getSection()->getCrn() ?>">
                            <i class="icon"></i>
                        </div>
                    </div>
                </div>
                <div id="course-submit-button" class="ui right floated button" tabindex="0">Submit</div>
                <div id="course-cancel-button" class="ui right floated button course-cancel">Cancel</div>
            </form>
        </div>
    </div>
    <div id="additional">
        <h2 class="truman-dark-bg">Additional Information</h2>
        <div id="additional-display">
            <table>
                <tr>
                    <td>
                        <form class="ui form">
                            <div class="field">
                                <label>Reason</label>
                                <select id="reason-display" class="ui dropdown disabled">
                                    <option value="<?= $request->getReason() ?>"><?= $request->getReason() ?></option>
                                </select>
                            </div>
                            <div class="field">
                                <label>Explanation</label>
                                <div class="ui input disabled">
                                    <textarea rows="2" id="explanation-display" readonly><?= $request->getExplanation() ?></textarea>
                                </div>
                            </div>
                        </form>
                    </td>
                    <td>
                        <button id="additional-edit-button" class="edit"><i class="material-icons">create</i></button>
                    </td>
                </tr>
            </table>
        </div>
        <div id="additional-edit" class="edit">
            <form id="additional-form" class="ui form">
                <div class="field">
                    <label>Reason</label>
                    <select id="reason" class="ui dropdown">
                        <option value="<?= $request->getReason() ?>"><?= $request->getReason() ?></option>
                        <?php foreach (Request::listReasons() as $reason): ?>
                            <?php if ($reason !== $request->getReason()): ?>
                                <option value="<?= $reason ?>"><?= $reason ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label>Explanation</label>
                    <textarea rows="2" id="explanation"><?= $request->getExplanation() ?></textarea>
                </div>
                <div id="additional-submit-button" class="ui right floated button" tabindex="0">Submit</div>
                <div id="additional-cancel-button" class="ui right floated button course-cancel">Cancel</div>
            </form>
        </div>
    </div>
</section>
</body>
</html>

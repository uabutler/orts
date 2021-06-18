<?php
include_once '../../php/database/requests.php';
include_once '../../php/database/students.php';
include_once '../../php/auth.php';

if (isset($_GET['id']))
    $request = Request::getById(intval($_GET['id']));
else
    $request = null;

if (is_null($request))
    include '../error/error400.php';

Auth::createClient();
Auth::forceAuthenticationStudent($request->getStudent()->getEmail());

$departments = Department::listActive();
$semesters = Semester::listActive();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>ORTS - Request Details</title>
    <?php require '../../php/common-head.php'; ?>
    <link rel="stylesheet" href="/css/student/request-details.css">
    <script>
        REQUEST_ID = <?= $_GET['id'] ?>;
    </script>
    <script src="/js/student/course-form.js"></script>
    <script src="/js/student/request-details.js"></script>
</head>

<body>
<?php require_once '../../php/header.php'; ?>
<?php require_once '../../php/navbar.php'; studentNavbar("Active Requests"); ?>

<div class="ui modal">
    <div class="header">
        Select a File
    </div>
    <div class="content">

        <div class="ui placeholder segment">
        <!--TODO: Drag and drop-->
            <div class="ui icon header">
                <i class="file outline icon"></i>
                <span id="default-upload-text">Select a file to upload</span>
                <span id="file-upload-name" class="hidden"></span>
            </div>

            <div id="upload-browse-button">
                <input type="file" class="inputfile" id="file-selector" style="display: none;"/>
                <label for="file-selector" class="ui primary right labeled icon button">
                    Browse
                    <i class="open folder icon"></i>
                </label>
            </div>

            <div id="upload-progress-bar" class="ui progress hidden">
                <div class="bar">
                    <div class="progress"></div>
                </div>
                <div class="label">Uploading File</div>
            </div>
        </div>

    </div>
    <div class="actions">
        <div id="file-cancel" class="ui black deny button">
            Cancel
        </div>
        <div id="upload-file-button" class="ui disabled positive right labeled icon button">
            Upload
            <i class="upload icon"></i>
        </div>
    </div>
</div>

<section class="content-grid-container">
    <div id="status">
        <h2 class="truman-dark-bg">Override Status</h2>
        <table class="status-table">
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
                <td><?= $request->getCreationTime() ?></td>
            </tr>
            <tr>
                <th>Explanation:</th>
                <td class="ui form">
                    <div class="field disabled">
                        <textarea rows="2" readonly><?= $request->getJustification() ?></textarea>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div id="course">
        <h2 class="truman-dark-bg">Course Information</h2>
        <div id="course-display" class="editable">
            <div>
                <table class="status-table">
                    <tr>
                        <th>Semester:</th>
                        <td id="semester-display"><?= $request->getSection()->getSemester()->getDescription() ?></td>
                    </tr>
                    <tr>
                        <th>Course:</th>
                        <td id="course-info-display"><?= $request->getSection()->getCourse()->getDepartment()->getDept() ?> <?= $request->getSection()->getCourse()->getCourseNum() ?></td>
                    </tr>
                    <tr>
                        <th>Title:</th>
                        <td id="course-title-display"><?= $request->getSection()->getCourse()->getTitle() ?></td>
                    </tr>
                    <tr>
                        <th>Section:</th>
                        <td id="section-display">
                            <?php
                            $section = $request->getSection()->getSectionNum();
                            if ($section < 10)
                                echo "0";
                            echo $section;
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>CRN:</th>
                        <td id="crn-display"><?= $request->getSection()->getCrn() ?></td>
                    </tr>
                </table>
            </div>
            <div class="edit-button-container">
                <button id="course-edit-button" class="edit"><i class="material-icons">create</i></button>
            </div>
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
        <div id="additional-display" class="editable">
            <div>
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
            </div>
            <div class="edit-button-container">
                <button id="additional-edit-button" class="edit"><i class="material-icons">create</i></button>
            </div>
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
    <div id="attachments">
        <h2 class="truman-dark-bg">Attachments</h2>
        <div id="attachment-preview-container">
            <div id="file-list">
                <div class="header-button">
                    <button id="upload-window-button" class="ui small right labeled icon button">
                        <i class="upload icon"></i>
                        Upload New File
                    </button>
                </div>
                <h3 class="file-section-header">Files</h3>
                <div id="file-list-table">
                    <div class="ui active centered inline loader"></div>
                </div>
            </div>
            <div id="file-preview-container">
                <div class="header-button">
                    <button id="close-file-preview" class="ui small basic icon button">
                        <i class="close icon"></i>
                    </button>
                </div>
                <h3 class="file-section-header">Preview</h3>
                <div id="file-preview">
                    <embed  src="https://uabutler.com/files/resume.pdf"
                            type="application/pdf"
                            scrolling="auto"
                            width="100%"
                            style="min-height: 50vw;"
                    ></embed>
                </div>
            </div>
        </div>
    </div>
</section>
</body>
</html>

<?php
include_once '../../php/database/requests.php';

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
    <?php require '../../php/common-head.php'; ?>
    <link rel="stylesheet" href="/css/admin/request-details.css">
    <script>
        REQUEST_ID = <?= $_GET['id'] ?>;
        REQUEST_STATUS = "<?= $request->getStatus() ?>";
    </script>
    <script src="/js/admin/request-details.js"></script>
    <script src="/js/common/attachments.js"></script>
</head>

<body>
<?php require_once '../../php/header.php'; ?>
<?php require_once '../../php/navbar.php'; facultyNavbar($request->isActive() ? "Current Semester" : "Archive"); ?>

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
    <div class="ui message hidden">
        <i class="close icon"></i>
        <div class="header">
            TEST
        </div>
        <p>TEST</p>
    </div>
    <div id="orinfo" class="grid-item">
        <h2 class="truman-dark-bg">Override Request Info</h2>
        <table style="padding-bottom:20px;">
            <tr>
                <th>Status:</th>
                <td id="status_info"><?= $request->getStatusHtml() ?></td>
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
        <table id="class-info">
            <?php $section = $request->getSection(); ?>
            <tr>
                <th>CRN</th>
                <th>Course</th>
                <th>Section</th>
                <th>Title</th>
            </tr>
            <tr>
                <td><?= $section->getCrn() ?></td>
                <td><?= $section->getCourse()->getDepartment()->getDept() ?> <?= $section->getCourse()->getCourseNum() ?></td>
                <td><?= $section->getSectionNum() ?></td>
                <td><?= $section->getCourse()->getTitle() ?></td>
            </tr>
        </table>
    </div>

    <div id="stuinfo" class="grid-item">
        <h2 class="truman-dark-bg">Student Info</h2>
        <table style="padding-bottom:20px;">
            <?php $student = $request->getStudent(); ?>
            <tr>
                <th>First Name:</th>
                <td><?= $student->getFirstName() ?></td>
            </tr>
            <tr>
                <th>Last Name:</th>
                <td><?= $student->getLastName() ?></td>
            </tr>
            <tr>
                <th>Majors:</th>
                <td><?= implode(', ', Major::buildStringList($student->getMajors())) ?></td>
            </tr>
            <tr>
                <th>Minors:</th>
                <td><?= implode(', ', Minor::buildStringList($student->getMinors())) ?></td>
            </tr>
            <tr>
                <th>Email:</th>
                <td><?= $student->getEmail() ?>@truman.edu</td>
            </tr>
            <tr>
                <th>Student ID:</th>
                <td><?= $student->getBannerId() ?></td>
            </tr>
            <tr>
                <th>Academic Level:</th>
                <td><?= $student->getStanding() ?></td>
            </tr>
            <tr>
                <th style="padding-right:1em">Expected Graduation:</th>
                <td><?= $student->getGradMonth() ?></td>
            </tr>
        </table>
    </div>

    <div id="explain" class="grid-item">
        <h2 class="truman-dark-bg">Explaination</h2>
        <table style="padding-bottom:20px;">
            <tr>
                <th style="padding-right:1em">Reason:</th>
                <td><?= $request->getReason() ?></td>
            </tr>
        </table>
        Student Request Explanation:
        <div class="ui form">
            <div class="field disabled">
                <textarea readonly><?= $request->getExplanation() ?></textarea>
            </div>
        </div>
    </div>

    <div id="action-title" class="grid-item">
        <h2 class="truman-dark-bg">Actions</h2>
    </div>

    <div id="status" class="grid-item">
        <h3 style="margin:0;">Approve or Deny</h3>
        <form class="ui form">
            <div class="field">
                <select id="status_input" class="ui dropdown">
                    <option value="">Change Status</option>
                    <option value="Approved">Approved</option>
                    <option value="Provisionally Approved">Provisionally Approved</option>
                    <option value="Denied">Denied</option>
                </select>
            </div>
            <div class="field">
                <textarea id="justification" placeholder="Note for student"><?= $request->getJustification() ?></textarea>
            </div>
            <div class="ui <?php if($request->isInBanner()) echo "checked"; ?> checkbox">
                <input id="banner" type="checkbox" <?php if($request->isInBanner()) echo "checked"; ?>>
                <label>In Banner</label>
            </div>
            <div id="submit" class="ui right floated button">Submit</div>
        </form>
    </div>
    <div id="send" class="grid-item">
        <h3 style="margin:0;">Delegate to Faculty</h3>
        <form class="ui form">
            <div class="field">
                <div class="ui right labeled input">
                    <input required class="email-input" type="text" name="email" placeholder="Truman email">
                    <div class="ui label">@truman.edu</div>
                </div>
            </div>
            <div class="field">
                <textarea id="justification" placeholder="Note for faculty"></textarea>
            </div>
            <div id="submit-faculty" class="ui right floated button">Submit</div>
        </form>
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
                <div id="file-preview"></div>
            </div>
        </div>
    </div>
</section>
</body>
</html>

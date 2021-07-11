<?php
include_once '../../php/database/requests.php';
include_once '../../php/database/faculty.php';

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
    <link rel="stylesheet" href="/css/common/message.css">
    <script>
        REQUEST_ID = <?= $_GET['id'] ?>;
        REQUEST_STATUS = "<?= $request->getStatus() ?>";
    </script>
    <script src="/js/admin/request-details.js"></script>
    <script src="/js/common/attachments.js"></script>
    <script src="/js/common/message.js"></script>
</head>

<body>
<?php require_once '../../php/header.php'; ?>
<?php require_once '../../php/navbar.php'; facultyNavbar($request->isActive() ? "Current Semester" : "Archive"); ?>
<?php require_once '../../php/message.php'; ?>

<div id="delete-confirmation" class="ui basic modal">
    <div class="ui icon header">
        <i class="trash icon"></i>
        Delete Attachment
    </div>
    <div class="content">
        <p>Are you sure you want to delete this file? This cannot be undone.</p>
    </div>
    <div class="actions">
        <div class="ui red basic cancel inverted button">
            <i class="remove icon"></i>
            No
        </div>
        <div class="ui green ok inverted button">
            <i class="checkmark icon"></i>
            Yes
        </div>
    </div>
</div>

<div id="upload-popup" class="ui modal">
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
                <td><?= $request->getCreationTime() ?></td>
            </tr>
            <tr>
                <th style="padding-right:1em">Designated Faculty:</th>
                <td id="faculty_info"><?= $request->getFaculty()->getLastName() ?>, <?= $request->getFaculty()->getFirstName() ?></td>
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
                <select id="faculty_input" class="ui search dropdown">
                    <option value="">Select faculty</option>
                    <?php foreach(Faculty::list() as $faculty): ?>
                        <option value="<?= $faculty->getEmail() ?>"><?= $faculty->getLastName() ?>, <?= $faculty->getFirstName() ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <textarea id="note" placeholder="Include note in email"></textarea>
            </div>
            <div id="submit-faculty" class="ui right floated button">Submit</div>
        </form>
    </div>

    <div id="attachments">
        <h2 class="truman-dark-bg">Attachments</h2>
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
    </div>
</section>
</body>
</html>

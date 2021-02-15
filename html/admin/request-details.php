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
    <link rel="stylesheet" href="/css/admin/request-details.css">
    <script>
        REQUEST_ID = <?= $_GET['id'] ?>;
        REQUEST_STATUS = "<?= $request->getStatus() ?>";
    </script>
    <script src="/js/admin/request-details.js"></script>
</head>

<body class="grid-container">
<?php require_once '../php/header.php'; ?>
<?php require_once '../php/navbar.php'; facultyNavbar($request->isActive() ? "Current Semester" : "Archive"); ?>

<div class="grid-item content content-grid-container">
    <div class="grid-item orinfo">
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

    <div class="grid-item stuinfo">
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
                <td><?= $student->getEmail() ?></td>
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

    <div class="grid-item explain">
        <h2 class="truman-dark-bg">Explaination</h2>
        <table style="padding-bottom:20px;">
            <tr>
                <th style="padding-right:1em">Reason:</th>
                <td><?= $request->getReason() ?></td>
            </tr>
        </table>
        Student Request Explanation:
        <textarea readonly><?= $request->getExplanation() ?></textarea>
    </div>

    <div class="grid-item action-title">
        <h2 class="truman-dark-bg">Actions</h2>
    </div>

    <div class="grid-item status">
        <h3 style="margin:0;">Approve or Deny</h3>
        <form>
            <label for="status">Status:</label>
            <select name="status" id="status_input">
                <option value="none" hidden></option>
                <option value="Approved">Approved</option>
                <option value="Provisionally Approved">Provisionally Approved</option>
                <option value="Denied">Denied</option>
            </select>
            <label style="padding-left:50px">In Banner:</label>
            <input type="checkbox" id="banner" name="banner" <?php if($request->isInBanner()) echo "checked"; ?> >
            <br>
            <textarea id="justification" placeholder="Note for student"><?= $request->getJustification(); ?></textarea>
        </form>
        <button id="submit">Submit</button>
    </div>
    <div class="grid-item send">
        <h3 style="margin:0;">Delegate to Faculty</h3>
        <form>
            <label for="email">Faculty email:</label>
            <input type="email">
            <br>
            <textarea placeholder="Notes to send to faculty"></textarea>
        </form>
        <button id="submit">Submit</button>
    </div>

    <!--
    <div class="grid-item log">
        <h2 class="truman-dark-bg">Log</h2>
        <center>
            <div class="log-box">
                <div class="log-item log-other">
                    <b>James Kirk</b> (student)
                    <div class="log-other log-info">
                        Student submitted request at 1970-01-01T00:00:00<br>
                        <b>Reason</b>: Prerequisite restriction<br><br>
                        <b>MSG</b>:
                        See, the thing is, I don't actaully know Java, but this course requires Java. But also, I'm
                        like, really smart, so I should be exempt. Right? The rules only apply to *normal* people
                        anyway. As we all know, I'm better than those guys, so go ahead and approve the request, will
                        ya?
                    </div>
                </div>

                <div class="log-item log-mine">
                    <b>Diane Sandefur</b> to <b>James Kirk</b>
                    <div class="log-mine log-msg">
                        You can't seriouly expect me to approve this?
                    </div>
                    <div class="log-mine log-info">
                        Request denied at 2020-10-08T16:19:34<br><br>
                        <b>MSG</b>:
                        This doesn't include any of the required information
                    </div>
                </div>

                <div class="log-item log-mine">
                    <b>Diane Sandefur</b> to <b>Dr. Beck</b>
                    <div class="log-mine log-msg">
                        Can you believe this kid?
                    </div>
                </div>

                <div class="log-item log-other">
                    <b>Dr. Beck</b> to <b>Diane Sandefur</b>
                    <div class="log-other log-msg">
                        lol
                    </div>
                </div>

                <div class="log-item log-other">
                    <b>James Kirk</b> to <b>Diane Sandefur</b>
                    <div class="log-other log-msg">
                        I forgot to mention that I'm taking Calc II over the summer at SEMO
                    </div>
                </div>
            </div>
        </center>
        <br>
        <div style="width:95%;padding-left:2.5%;">
            <form>
                <label for="email">User:</label>
                <input type="email">
                <br>
                <textarea placeholder="Message"></textarea>
            </form>
            <button>Send</button>
        </div>
    </div>
    -->
</div>
</body>
</html>

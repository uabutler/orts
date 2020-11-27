<?php
include_once 'database/requests_db.php';

if (isset($_GET['id']))
    $request = Request::getById(intval($_GET['id']));
else
    $request = null;

if(is_null($request))
{
    header("Location: error404.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="icon" type="image/png" href="https://images.truman.edu/favicon-16x16.png" sizes="16x16">
    <link rel="icon" type="image/png" href="https://images.truman.edu/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="https://images.truman.edu/favicon-96x96.png" sizes="96x96">
    <link rel="stylesheet" href="main.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <title>ORTS - Request Details</title>
    <style>
        .content-grid-container {
            display: grid;
            grid-gap: 10px;
            grid-template-columns: 1fr 1fr;
            padding: 0;
        }

        .orinfo {
            grid-column: 1;
            grid-row: 1;
        }

        .stuinfo {
            grid-column: 2;
            grid-row: 1;
        }

        .explain {
            grid-column: 1 / span 2;
            grid-row: 2;
        }

        .action-title {
            grid-column: 1 / span 2;
            grid-row: 3;
        }

        .status {
            grid-column: 1;
            grid-row: 4;
        }

        .send {
            grid-column: 2;
            grid-row: 4;
        }

        .log {
            grid-column: 1 / span 2;
            grid-row: 5;
            padding-bottom: 100px;
        }

        .log-box {
            overflow: auto;
            width: 95%;
            height: 500px;
            border: 1px solid grey;
        }

        .log-item {
            width: 60%;
            height: auto;
            margin: 10px;
        }

        .log-other {
            float: left;
            text-align: left;
        }

        .log-mine {
            float: right;
            text-align: left;
        }

        .log-info, .log-msg {
            height: auto;
            width: 100%;
            padding: 10px;
            margin: 5px;
        }

        .log-msg {
            border-radius: 25px;
            /* The fact that I have to do this proves that, if there is a God, he wants nothing to do with css */
            font-family: monospace, monospace;
        }

        .log-other.log-msg, .log-other.log-info {
            background: lightgrey;
        }

        .log-mine.log-msg, .log-mine.log-info {
            background: darkgrey;
            color: white;
        }

        #class-info {
            border-collapse: collapse;
            width: 90%;
        }

        #class-info td, #class-info th {
            border: 1px solid #ddd;
            padding: 8px;
        }

        #class-info th {
            padding-top: 12px;
            padding-bottom: 12px;
            background-color: #f2f2f2;
        }

        th {
            text-align: left;
        }

        /* Shamelessly stolen from Stack Overflow */
        textarea {
            width: 100%;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
        }

        h2 {
            font-family: nexabold, sans-serif;
            color: white;
            padding: 10px
        }
    </style>
</head>

<body class="grid-container">
<div class="grid-item header right truman-dark-bg"></div>
<div class="grid-item header left truman-dark-bg"></div>
<div class="grid-item header center truman-dark-bg">
    <div style="text-align: center;">
        <span style="float:left;">
          <img id="logo" src="assets/truman.png" alt="Truman State University"/>
        </span>
        <span style="float:right">
          <div id="main-title" style="font-size:50px;font-family:nexabold;">
Override Tracking System
          </div>
          <div style="font-size:20px;font-family:nexabook;">
Departments of Mathematics, Computer Science, and Statistics
          </div>
        </span>
    </div>
</div>

<div class="grid-item navbar left truman-dark-bg"></div>
<div class="grid-item navbar right truman-dark-bg"></div>

<div class="grid-item sidebar left"></div>
<div class="grid-item sidebar right"></div>

<div class="grid-item navbar center">
    <ul id="nav-list" class="truman-dark-bg">
        <li class="nav-item"><a class="active" href="#">Current Semester</a></li>
        <li class="nav-item"><a href="#">Archive</a></li>
        <li class="nav-item" style="float:right;"><a href="#">Log Out</a></li>
        <li class="nav-item" style="float:right;"><a href="#">Profile</a></li>
    </ul>
</div>

<div class="grid-item content content-grid-container">
    <div class="grid-item orinfo">
        <h2 class="truman-dark-bg">Override Request Info</h2>
        <table style="padding-bottom:20px;">
            <tr>
                <th>Status:</th>

                <td><?php echo Request::getStatusHtml($request->getStatus(), $request->isInBanner()); ?></td>
            </tr>
            <tr>
                <th>Date Modified:</th>
                <td><?php echo $request->getLastModified(); ?></td>
            </tr>
            <tr>
                <th>Date Received:</th>
                <td>1970-01-01T00:00:00</td>
            </tr>
            <tr>
                <th style="padding-right:1em">Designated Faculty:</th>
                <td><?php echo $request->getFaculty()->getLastName().", ".$request->getFaculty()->getFirstName(); ?></td>
            </tr>
        </table>
        <table id="class-info">
            <?php
            $section = $request->getSection();
            ?>
            <tr>
                <th>CRN</th>
                <th>Course</th>
                <th>Section</th>
                <th>Title</th>
            </tr>
            <tr>
                <td><?php echo $section->getCrn(); ?></td>
                <td><?php echo $section->getCourse()->getDepartment()->getDept()." "
                        .$section->getCourse()->getCourseNum(); ?></td>
                <td><?php echo $section->getSectionNum(); ?></td>
                <td><?php echo $section->getCourse()->getTitle(); ?></td>
            </tr>
        </table>
    </div>

    <div class="grid-item stuinfo">
        <h2 class="truman-dark-bg">Student Info</h2>
        <table style="padding-bottom:20px;">
            <?php
            $student = $request->getStudent();
            ?>
            <tr>
                <th>First Name:</th>
                <td><?php echo $student->getFirstName(); ?></td>
            </tr>
            <tr>
                <th>Last Name:</th>
                <td><?php echo $student->getLastName(); ?></td>
            </tr>
            <tr>
                <th>Majors:</th>
                <td><?php echo implode(', ', Major::buildStringList($student->getMajors())); ?></td>
            </tr>
            <tr>
                <th>Minors:</th>
                <td><?php echo implode(', ', Minor::buildStringList($student->getMinors())); ?></td>
            </tr>
            <tr>
                <th>Email:</th>
                <td><?php echo $student->getEmail(); ?></td>
            </tr>
            <tr>
                <th>Student ID:</th>
                <td><?php echo $student->getBannerId(); ?></td>
            </tr>
            <tr>
                <th>Academic Level:</th>
                <td><?php echo $student->getStanding(); ?></td>
            </tr>
            <tr>
                <th style="padding-right:1em">Expected Graduation:</th>
                <td><?php echo $student->getGradMonth(); ?></td>
            </tr>
        </table>
    </div>

    <div class="grid-item explain">
        <h2 class="truman-dark-bg">Explaination</h2>
        <table style="padding-bottom:20px;">
            <tr>
                <th style="padding-right:1em">Reason:</th>
                <td><?php echo $request->getReason(); ?></td>
            </tr>
        </table>
        Student Request Explanation:
        <textarea readonly><?php echo $request->getExplanation(); ?></textarea>
    </div>

    <div class="grid-item action-title">
        <h2 class="truman-dark-bg">Actions</h2>
    </div>

    <div class="grid-item status">
        <h3 style="margin:0;">Approve or Deny</h3>
        <form>
            <label for="status">Status:</label>
            <select name="status" id="status">
                <option value="none"></option>
                <option value="approved">Approved</option>
                <option value="provisional">Provisionaly Approved</option>
                <option value="denied">Denied</option>
            </select>
            <label for="vehicle1" style="padding-left:50px">In Banner:</label>
            <input type="checkbox" id="vehicle1" name="vehicle1" value="Bike">
            <br>
            <textarea placeholder="Notes to send to student"></textarea>
        </form>
        <button onclick="document.location='detailed_status_change.html'">Submit</button>
    </div>
    <div class="grid-item send">
        <h3 style="margin:0;">Delegate to Faculty</h3>
        <form>
            <label for="email">Faculty email:</label>
            <input type="email">
            <br>
            <textarea placeholder="Notes to send to faculty"></textarea>
        </form>
        <button onclick="document.location='detailed_status_change.html'">Submit</button>
    </div>

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
</div>
</body>
</html>

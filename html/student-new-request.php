<?php
include_once 'database/students_db.php';
include_once 'database/requests_db.php';
include_once 'database/programs_db.php';

if (isset($_GET['student']))
{
    if(ctype_alnum($_GET['student']))
        $student_email = $_GET['student'];
    else
        $student_email = null;

    $student = Student::get($student_email);
}
else
{
    $student_email = null;
}

if (is_null($student_email))
{
    header("Location: error400.html");
    exit;
}

$majors = Major::list();
$minors = Minor::list();
$departments = Department::list();
$standings = Student::listStandings();
$reasons = Request::listReasons();
$semesters = Semester::listActive();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Used for status icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Use Truman's default favicons -->
    <link rel="icon" type="image/png" href="https://images.truman.edu/favicon-16x16.png" sizes="16x16">
    <link rel="icon" type="image/png" href="https://images.truman.edu/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="https://images.truman.edu/favicon-96x96.png" sizes="96x96">

    <link rel="stylesheet" href="main.css">

    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <!-- Select2 for multi-select form -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>

    <title>ORTS - New Request</title>

    <style>
        .content-grid-container {
            display: grid;
            grid-gap: 10px;
            grid-template-columns: 1fr 1fr;
            padding: 0;
        }

        .info {
            grid-column: 1 / span 2;
            grid-row: 1;
        }

        .profile {
            grid-column: 1;
            grid-row: 2;
        }

        .request {
            grid-column: 2;
            grid-row: 2;
        }

        .footer {
            grid-column: 1 / span 2;
            grid-row: 3;
            height: 250px;
        }

        h2 {
            font-family: nexabold, sans-serif;
            color: white;
            padding: 10px
        }

        input, select, textarea {
            width: 100%;
        }

        input, select {
            box-sizing: border-box;
        }

        textarea {
            resize: none;
        }

        td {
            white-space: nowrap;
            height: 40px;
        }

        .error {
            outline: none;
            border-color: red;
            box-shadow: 0 0 10px pink;
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
        <li class="nav-item"><a class="active" href="student-new-request.php">New Request</a></li>
        <li class="nav-item"><a href="student-request-list.php">Current Requests</a></li>
        <li class="nav-item" style="float:right;"><a href="#">Log Out</a></li>
        <li class="nav-item" style="float:right;"><a href="student-profile.php">Profile</a></li>
    </ul>
</div>

<div class="grid-item content content-grid-container">
    <div class="grid-item info">
        <h3 style="font-family: nexabold,sans-serif">PLEASE READ ALL OF THE INSTRUCTIONS BEFORE FILLING OUT THE ENTIRE
            FORM. ONLY FORMS FILLED OUT CORRECTLY WILL BE CONSIDERED.</h3>

        While the Departments of Mathematics, Computer Science, and Statistics have implemented wait lists for some
        of their courses, they are offered strictly on a first come, first served basis (there is no re-ordering or
        prioritizing of the wait lists). There is no mechanism to request priority consideration in a wait list.<br>
        <br>
        However, if you have <i>EXCEPTIONAL NEED</i> for a course, you may use this form to instead request a
        registration override, which will allow you immediate registration access. <i>If your need is not
        exceptional, you should stop now.</i> You may still place your name on a wait list if one exists, take a
        different section if one is open, or delay taking the course to a later semester.<br>
        <h3 style="font-family: nexabold, sans-serif">INSTRUCTIONS:</h3>
        Overrides will be granted on the basis of <i>EXCEPTIONAL NEED</i> after all information has been verified by our
        office for accuracy.<br>
        <br>
        When filling out the form, fill out the semester you're registering for, the course department code, course
        number, and section number. Then, verify that the title and CRN are for the correct course before submitting.<br>
        <br>
        <span style="font-family: nexabold, sans-serif"><b>If the override is granted,</b></span> an announcement
        will appear under your Student Tab → Registration → Registration Status in TruView.<br>
        <br>
        <span style="font-family: nexabold, sans-serif"><b>If the override is denied,</b></span> you will be notified
        by an email from the department office administrative assistant.<br>
        <br>
        Most override requests are granted or denied within 2 days.<br>
        <br>
        <span style="font-family: nexabold, sans-serif"><b>If the override is approved,</b></span> the student has 3
        DAYS to add the course. After three days, the override may be revoked. If the student applies the override
        after the Free Add/Drop Period, a $50 fee will be assessed to the student’s account regardless of when the
        override was granted.
    </div>
    <div class="grid-item profile">
        <h2 class="truman-dark-bg">Profile</h2>
        <table style="width: 100%">
            <colgroup>
                <col>
                <col style="width: 100%;">
            </colgroup>
            <tr>
                <td>Email:</td>
                <td><input type="text" readonly value="<?php echo $student_email; ?>@truman.edu"></td>
            </tr>
            <tr>
                <td>First Name:</td>
                <td><input type="text" name="first_name"></td>
            </tr>
            <tr>
                <td>Last Name:</td>
                <td><input type="text" name="last_name"></td>
            </tr>
            <tr>
                <td>Banner ID:</td>
                <td><input class="numeric" type="text" name="banner_id"></td>
            </tr>
            <tr>
                <td>Grad Month:</td>
                <td><input type="text" placeholder="MM/YYYY" name="grad_month"></td>
            </tr>
            <tr>
                <td>Class:</td>
                <td>
                    <select class="select" name="standing">
                        <?php
                        foreach($standings as $standing)
                            echo '<option value="'.$standing.'">'.$standing.'</option>';
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Major(s):</td>
                <td>
                    <select class="select" name="majors[]" multiple="multiple">
                        <?php
                            foreach($majors as $major)
                                echo '<option value="'.$major.'">'.$major.'</option>';
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Minor(s):</td>
                <td>
                    <select class="select" name="minors[]" multiple="multiple">
                        <?php
                        foreach($minors as $minor)
                            echo '<option value="'.$minor.'">'.$minor.'</option>';
                        ?>
                    </select>
                </td>
            </tr>
        </table>
    </div>

    <div class="grid-item request">
        <h2 class="truman-dark-bg">Request</h2>
        <table style="width: 100%">
            <colgroup>
                <col>
                <col style="width: 100%;">
            </colgroup>
            <tr>
                <td>Semester:</td>
                <td>
                    <select class="select" name="semester">
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
                    <select class="select" name="department">
                        <?php
                        foreach($departments as $department)
                            echo '<option value="'.$department.'">'.$department.'</option>';
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Course Num:</td>
                <td><input class="numeric" type="text" placeholder="101" name="course_num"></td>
            </tr>
            <tr>
                <td>Section:</td>
                <td><input class="numeric" type="text" placeholder="01" name="section"></td>
            </tr>
            <tr>
                <td>Title:</td>
                <td><input type="text" name="title" readonly></td>
            </tr>
            <tr>
                <td>CRN:</td>
                <td><input type="text" name="crn" readonly></td>
            </tr>
            <tr>
                <td>Reason:</td>
                <td>
                    <select class="select" name="reason">
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

    <div class="grid-item footer"> </div>
</div>
<script>
    function validateElement(element_name, regex)
    {
        console.log(`Validating ${element_name} against ${regex}`);

        let element = $(`input[name="${element_name}"]`);

        if(regex.test(String(element.val())))
            element.removeClass("error");
        else
            element.addClass("error");
    }

    function validateNotEmpty(element_name)
    {
        console.log(`Validating ${element_name} is not empty`);

        let element = $(`input[name="${element_name}"]`);

        if (element.length === 0)
            element = $(`textarea[id="${element_name}"]`);

        if(element.val())
            element.removeClass("error");
        else
            element.addClass("error");
    }

    function validateBannerId() { validateElement("banner_id", /^001\d{6}$/); }
    function validateGradMonth() { validateElement("grad_month", /^[01]\d\/20\d{2}$/); }
    function validateCourseNum() { validateElement("course_num", /^\d{3}$/); }
    function validateSection() { validateElement("section", /^\d{1,2}$/); }
    function validateFirstName() { validateNotEmpty("first_name"); }
    function validateLastName() { validateNotEmpty("last_name"); }
    function validateExplanation() { validateNotEmpty("explanation"); }

    function validate()
    {
        validateBannerId();
        validateGradMonth();
        validateCourseNum();
        validateSection();
        validateFirstName();
        validateLastName();
        validateExplanation();
    }

    function getSection()
    {
        // TODO: GET request for title and crn
    }

    $(function (events, handler)
    {
        $('.select').select2();

        $('input[name="course_num"]').on("keyup", function () { getSection(); })
        $('input[name="section"]').on("keyup", function () { getSection(); })

        $(document).on("input", ".numeric", function () {
            this.value = this.value.replace(/\D/g, '');
        });

        $('input[name="banner_id"]').on("focusout", function () { validateBannerId(); })
        $('input[name="grad_month"]').on("focusout", function () { validateGradMonth(); })
        $('input[name="course_num"]').on("focusout", function () { validateCourseNum(); })
        $('input[name="section"]').on("focusout", function () { validateSection(); })
        $('input[name="first_name"]').on("focusout", function () { validateFirstName(); })
        $('input[name="last_name"]').on("focusout", function () { validateLastName(); })
        $('textarea[id="explanation"]').on("focusout", function () { validateExplanation(); })
    });
</script>
</body>
</html>

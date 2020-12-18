<?php
include_once 'database/students_db.php';
include_once 'database/requests_db.php';
include_once 'database/programs_db.php';

if (isset($_GET['id']))
{
    if(ctype_digit($_GET['id']))
        $student_id = $_GET['id'];
    else
        $student_id = null;
}
else
{
    $student_id = null;
}

if (is_null($student_id))
{
    header("Location: error400.html");
    exit;
}

$departments = Department::list();
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

        button {
            float: right;
        }

        .error {
            outline: none;
            border-color: #ff4d61;
            box-shadow: 0 0 5px #ff4d61;
        }

        #next:hover {
            background-color: #ddd;
            color: black;
        }

        #next {
            background-color: rgb(81,12,118);
            color: white;
            text-decoration: none;
            display: inline-block;
            padding: 8px 16px;
            float:right;
            margin-top: 16px
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

<div class="grid-item content">
    <div class="info">
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
    <div>
        <a id="next">Submit &raquo;</a>
    </div>
</div>
<script>
    /*
     * INPUT VALIDATION
     */
    function validateRegex(element_name, regex)
    {
        console.log(`Validating ${element_name} against ${regex}`);

        let element = $(`input[name="${element_name}"]`);

        return regex.test(String(element.val()));
    }

    function validateNotEmpty(element_name)
    {
        console.log(`Validating ${element_name} is not empty`);

        let element = $(`input[name="${element_name}"]`);
        if (element.length === 0)
            element = $(`textarea[id="${element_name}"]`);

        return element.val() != "";
    }

    function validateCourseNum() { return validateRegex("course_num", /^\d{3}$/); }
    function validateSection() { return validateRegex("section", /^\d{1,2}$/); }
    function validateExplanation() { return validateNotEmpty("explanation"); }
    function validateCrn() { return validateNotEmpty("crn"); }

    function validate()
    {
        // Return true iff all are true
        switch(false)
        {
            case validateExplanation():
            case validateCrn(): // This includes course number and section
                return false;
            default:
                return true;
        }
    }

    /*
     * Create error notice
     */
    function setError(valid, element_name)
    {
        let element = $(`input[name="${element_name}"]`);

        // Included in case this is the explanation field
        if (element.length === 0)
            element = $(`textarea[id="${element_name}"]`);

        if(valid)
            element.removeClass("error");
        else
            element.addClass("error");
    }

    /*
     * Get the section CRN and Title from the server
     */
    function setSection()
    {
        if(!(validateCourseNum() && validateSection()))
        {
            $('input[name="crn"]').val("");
            $('input[name="title"]').val("");
            return;
        }

        let data = {};

        data.semester = $('select[name="semester"]').val();
        data.department = $('select[name="department"]').val();
        data.course_num = $('input[name="course_num"]').val();
        data.section = parseInt($('input[name="section"]').val(), 10);

        let request = $.get("api/section.php", data, function (data, status, xhr)
        {
            if(status === "success")
            {
                $('input[name="crn"]').val(data.crn);
                $('input[name="title"]').val(data.course.title);
            }
            else
            {
                $('input[name="crn"]').val("");
                $('input[name="title"]').val("");
            }
        }, "json");
    }

    /*
     * INPUT DISABLE
     */
    function inputEnable(bool)
    {
        bool = !bool;

        $('input[name="course_num"]').attr("readonly", bool);
        $('input[name="section"]').attr("readonly", bool);

        $('select[name="semester"]').attr("disabled", bool);
        $('select[name="department"]').attr("disabled", bool);
        $('select[name="reason"]').attr("disabled", bool);

        $('textarea[id="explanation"]').attr("readonly", bool);
    }

    function createRequest()
    {
        if(!validate())
            return false;

        inputEnable(false);

        let data = {};

        data.student_id = <?php echo $student_id ?>;
        data.semester = $('select[name="semester"]').val();
        data.crn = $('input[name="crn"]').val();
        data.reason = $('select[name="reason"]').val();
        data.explanation = $('textarea[id="explanation"]').val();

        $.post("api/request.php", JSON.stringify(data), function(data)
        {
            console.log("SUCESS!");
        }, "json")
        .fail(function(response)
        {
            inputEnable(true);
        });
    }

    /*
     * MAIN
     */
    $(function ()
    {
        $('.select').select2();

        $(document).on("input", ".numeric", function ()
        {
            this.value = this.value.replace(/\D/g, '');
        });

        $('input[name="course_num"]').on("keyup", function () { setSection(); })
        $('input[name="section"]').on("keyup", function () { setSection(); })
        $('select[name="semester"]').on("change", function () { setSection(); })
        $('select[name="department"]').on("change", function () { setSection(); })

        $('input[name="course_num"]').on("focusout", function () { setError(validateCourseNum(), "course_num"); })
        $('input[name="section"]').on("focusout", function () { setError(validateSection(), "section"); })
        $('textarea[id="explanation"]').on("focusout", function () { setError(validateExplanation(), "explanation"); })

        $('#next').on("click", createRequest);
    });
</script>
</body>
</html>

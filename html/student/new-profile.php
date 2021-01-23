<?php
require_once '../php/database/students.php';
require_once '../php/database/requests.php';
require_once '../php/database/programs.php';
require_once '../php/auth.php';

Auth::createClient();
Auth::forceAuthentication();

if(Auth::isAuthenticatedStudent())
    header("Location: request-list.php");

$student_email = Auth::getUser();

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
    <title>ORTS - New Request</title>
    <?php require '../php/common-head.php';?>
    <link rel="stylesheet" href="/css/student/new-request.css">
    <script>
        STUDENT_EMAIL = "<?php echo $student_email; ?>";
    </script>
    <script src="/js/student/new-profile.js"></script>
</head>

<body class="grid-container">
<?php require_once '../php/header.php'; ?>
<!-- TODO: This person won't be have a profile at this point -->
<?php require_once '../php/navbar.php'; studentNavbar("New Request"); ?>

<div class="grid-item content">
    <div class="info">
        <?php require '../php/new-request-info.php'; ?>
    </div>
    <div>
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
                <td><input type="text" id="first_name"></td>
            </tr>
            <tr>
                <td>Last Name:</td>
                <td><input type="text" id="last_name"></td>
            </tr>
            <tr>
                <td>Banner ID:</td>
                <td><input class="numeric" type="text" id="banner_id"></td>
            </tr>
            <tr>
                <td>Grad Month:</td>
                <td><input type="text" placeholder="MM/YYYY" id="grad_month"></td>
            </tr>
            <tr>
                <td>Class:</td>
                <td>
                    <select class="select" id="standing">
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
                    <select class="select" id="majors" multiple="multiple">
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
                    <select class="select" id="minors" multiple="multiple">
                        <?php
                        foreach($minors as $minor)
                            echo '<option value="'.$minor.'">'.$minor.'</option>';
                        ?>
                    </select>
                </td>
            </tr>
        </table>
    </div>
    <div>
        <a id="next">Submit &raquo;</a>
    </div>
</div>
</body>
</html>

<?php
require_once '../php/database/students.php';
require_once '../php/database/requests.php';
require_once '../php/database/programs.php';
require_once '../php/auth.php';

Auth::createClient();
Auth::forceAuthentication();

if(Auth::isAuthenticatedStudent(null))
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
    <title>ORTS - New Student</title>
    <?php require '../php/common-head.php';?>
    <link rel="stylesheet" href="/css/student/new-request.css">
    <script>
        STUDENT_EMAIL = "<?= $student_email ?>";
    </script>
    <script src="/js/student/new-profile.js"></script>
</head>

<body>
<?php require_once '../php/header.php'; ?>
<!-- TODO: This person won't be have a profile at this point -->
<?php require_once '../php/navbar.php'; studentNavbar("Profile"); ?>

<section>
    <form class="ui form">
        <div class="info">
            <?php require '../php/new-request-info.php'; ?>
        </div>
        <h2 class="truman-dark-bg">Profile</h2>
        <div class="field">
            <label>Name</label>
            <div class="two fields">
                <div class="field">
                    <input type="text" id="first_name" placeholder="First Name">
                </div>
                <div class="field">
                    <input type="text" id="last_name" placeholder="Last Name">
                </div>
            </div>
        </div>
        <div class="field">
            <label>Standing</label>
            <select class="ui dropdown" id="standing">
                <option value="">Select Standing</option>
                <?php foreach($standings as $standing): ?>
                    <option value="<?= $standing ?>"><?= $standing ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="two fields">
            <div class="field">
                <label>Majors</label>
                <select id="majors" multiple="" class="ui fluid search dropdown">
                    <option value="">Select Majors</option>
                    <?php foreach($majors as $major): ?>
                        <option value="<?= $major ?>"><?= $major ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Minors</label>
                <select id="minors" multiple="" class="ui search dropdown">
                    <option value="">Select Minors</option>
                    <?php foreach($minors as $minor): ?>
                        <option value="<?= $minor ?>"><?= $minor ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="two fields">
            <div class="field">
                <label>Banner ID</label>
                <input class="numeric" type="text" id="banner_id" placeholder="Banner ID">
            </div>
            <div class="field">
                <label>Graduation Month</label>
                <div class="two fields">
                    <div class="field">
                        <select class="ui dropdown" id="grad_month">
                            <option value="">Month</option>
                            <option value="05/">May</option>
                            <option value="12/">December</option>
                        </select>
                    </div>
                    <div class="field">
                        <input class="numeric" type="text" id="year" placeholder="Year">
                    </div>
                </div>
            </div>
        </div>
        <div id="next" class="ui right floated button">Submit</div>
    </form>
</section>
</body>
</html>

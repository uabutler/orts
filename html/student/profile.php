<?php
require_once '../php/database/students.php';
require_once '../php/database/requests.php';
require_once '../php/database/programs.php';
require_once '../php/auth.php';

Auth::createClient();
Auth::forceAuthenticationStudent(null);

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
    <title>ORTS - Profile</title>
    <?php require '../php/common-head.php';?>
    <link rel="stylesheet" href="/css/student/new-request.css">
    <script>
        STUDENT_EMAIL = "<?= $student_email ?>";
    </script>
    <script src="/js/student/new-profile.js"></script>
</head>

<body>
<?php require_once '../php/header.php'; ?>
<?php require_once '../php/navbar.php'; studentNavbar("Profile"); ?>

<section>
    <h1>Profile</h1>
    <form class="ui form">
        <div class="field">
            <label>Name</label>
            <div class="two fields">
                <div class="field">
                    <input type="text" name="first_name" placeholder="First Name">
                </div>
                <div class="field">
                    <input type="text" name="last_name" placeholder="Last Name">
                </div>
            </div>
        </div>
        <div class="field">
            <label>Standing</label>
            <select class="ui dropdown" name="grad_month">
                <option value="">Standing</option>
                <?php foreach($standings as $standing): ?>
                    <option value="<?= $standing ?>"><?= $standing ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="two fields">
            <div class="field">
                <label>Majors</label>
                <select multiple="" class="ui fluid search dropdown">
                    <option value="">Select Majors</option>
                    <?php foreach($majors as $major): ?>
                        <option value="<?= $major ?>"><?= $major ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Minors</label>
                <select multiple="" class="ui search dropdown">
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
                <input class="numeric" type="text" name="banner_id" placeholder="Banner ID">
            </div>
            <div class="field">
                <label>Graduation Month</label>
                <div class="two fields">
                    <div class="field">
                        <select class="ui fluid search dropdown" name="grad_month">
                            <option value="">Month</option>
                            <option value="05/">May</option>
                            <option value="12/">December</option>
                        </select>
                    </div>
                    <div class="field">
                        <input type="text" name="year" placeholder="Year">
                    </div>
                </div>
            </div>
        </div>
        <div class="ui right floated button">Submit</div>
    </form>
</section>
</body>
</html>

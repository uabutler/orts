<?php
require_once '../../php/database/students.php';
require_once '../../php/database/requests.php';
require_once '../../php/database/programs.php';
require_once '../../php/auth.php';

Auth::createClient();
Auth::forceAuthenticationStudent(null);

$student_email = Auth::getUser();

$majors = Major::listActive();
$minors = Minor::listActive();
$departments = Department::listActive();
$standings = Student::listStandings();
$reasons = Request::listReasons();
$semesters = Semester::listActive();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>ORTS - Profile</title>
    <?php require '../../php/common-head.php';?>
    <link rel="stylesheet" href="/css/student/editable.css">
    <link rel="stylesheet" href="/css/student/profile.css">
    <script>
        STUDENT_EMAIL = "<?= $student_email ?>";
    </script>
    <script src="/js/student/editable.js"></script>
    <script src="/js/student/profile-form.js"></script>
    <script src="/js/student/profile.js"></script>
</head>

<body>
<?php require_once '../../php/header.php'; ?>
<?php require_once '../../php/navbar.php'; studentNavbar("Profile"); ?>

<section class="ui loading form">
    <h1>Profile</h1>

    <div>
        <div id="name-display" class="editable">
            <form class="ui form">
                <div class="field">
                    <label>Name</label>
                    <div class="two fields">
                        <div class="disabled field">
                            <input id="first-name-input-display" type="text" name="first_name" disabled placeholder="First Name">
                        </div>
                        <div class="disabled field">
                            <input id="last-name-input-display" type="text" name="last_name" disabled placeholder="Last Name">
                        </div>
                    </div>
                </div>
            </form>
            <div class="edit-button-container">
                <button id='name-edit-button' class="edit"><i class="material-icons">create</i></button>
            </div>
        </div>
        <div id="name-edit" class="edit">
            <form class="ui form">
                <div class="field">
                    <label>Name</label>
                    <div class="two fields">
                        <div class="field">
                            <input id="first_name" type="text" name="first_name" placeholder="First Name">
                        </div>
                        <div class="field">
                            <input id="last_name" type="text" name="last_name" placeholder="Last Name">
                        </div>
                    </div>
                </div>
            </form>
            <div id="name-submit-button" class="ui right floated button" tabindex="0">Submit</div>
            <div id="name-cancel-button" class="ui right floated button course-cancel">Cancel</div>
        </div>
    </div>

    <div>
        <div id="standing-display" class="editable">
            <form class="ui form">
                <div class="field">
                    <label>Standing</label>
                    <div class="disabled field">
                        <select id="standing-input-display" class="ui dropdown single-form-margin-fix" disabled name="grad_month">
                            <option value="">Standing</option>
                        </select>
                    </div>
                </div>
            </form>
            <div class="edit-button-container">
                <button id="standing-edit-button" class="edit"><i class="material-icons">create</i></button>
            </div>
        </div>
        <div id="standing-edit" class="edit">
            <form class="ui form">
                <div class="field">
                    <label>Standing</label>
                    <select id="standing" class="ui dropdown single-form-margin-fix" name="grad_month">
                        <option value="">Standing</option>
                        <?php foreach($standings as $standing): ?>
                            <option value="<?= $standing ?>"><?= $standing ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
            <div id="standing-submit-button" class="ui right floated button" tabindex="0">Submit</div>
            <div id="standing-cancel-button" class="ui right floated button course-cancel">Cancel</div>
        </div>
    </div>

    <div>
        <div id="program-display" class="editable">
            <form class="ui form">
                <div class="two fields">
                    <div class="field">
                        <label>Majors</label>
                        <div class="disabled field">
                            <select id="major-input-display" multiple="" class="ui fluid search dropdown">
                                <option value="">Select Majors</option>
                                <?php foreach($majors as $major): ?>
                                    <option value="<?= $major ?>"><?= $major ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="field">
                        <label>Minors</label>
                        <div class="disabled field">
                            <select id="minor-input-display" multiple="" class="ui search dropdown">
                                <option value="">Select Minors</option>
                                <?php foreach($minors as $minor): ?>
                                    <option value="<?= $minor ?>"><?= $minor ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
            <div class="edit-button-container">
                <button id="program-edit-button" class="edit"><i class="material-icons">create</i></button>
            </div>
        </div>
        <div id="program-edit" class="edit">
            <form class="ui form">
                <div class="two fields">
                    <div class="field">
                        <label>Majors</label>
                        <select id="major-input-edit" multiple="" class="ui fluid search dropdown">
                            <option value="">Select Majors</option>
                            <?php foreach($majors as $major): ?>
                                <option value="<?= $major ?>"><?= $major ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label>Minors</label>
                        <select id="minor-input-edit" multiple="" class="ui search dropdown">
                            <option value="">Select Minors</option>
                            <?php foreach($minors as $minor): ?>
                                <option value="<?= $minor ?>"><?= $minor ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </form>
            <div id="program-submit-button" class="ui right floated button" tabindex="0">Submit</div>
            <div id="program-cancel-button" class="ui right floated button course-cancel">Cancel</div>
        </div>
    </div>

    <div>
        <div id="banner-display" class="editable">
            <form class="ui form">
                <div class="field">
                    <label>Banner ID</label>
                    <div class="disabled field">
                        <input id="banner-input-display" class="numeric single-form-margin-fix" type="text" name="banner_id" placeholder="Banner ID">
                    </div>
                </div>
            </form>
            <div class="edit-button-container">
                <button id="banner-edit-button" class="edit"><i class="material-icons">create</i></button>
            </div>
        </div>
        <div id="banner-edit" class="edit">
            <form class="ui form">
                <div class="field">
                    <label>Banner ID</label>
                    <input id="banner_id" class="numeric single-form-margin-fix" type="text" name="banner_id" placeholder="Banner ID">
                </div>
            </form>
            <div id="banner-submit-button" class="ui right floated button" tabindex="0">Submit</div>
            <div id="banner-cancel-button" class="ui right floated button course-cancel">Cancel</div>
        </div>
    </div>

    <div>
        <div id="grad-display" class="editable">
            <form class="ui form">
                <div class="field">
                    <label>Graduation Month</label>
                    <div class="two fields">
                        <div class="disabled field">
                            <select id="grad-month-input-display" class="ui fluid dropdown" disabled name="grad_month">
                                <option value="">Month</option>
                                <option value="05/">May</option>
                                <option value="12/">December</option>
                            </select>
                        </div>
                        <div class="disabled field">
                            <input id="grad-year-input-display" type="text" name="year" disabled placeholder="Year">
                        </div>
                    </div>
                </div>
            </form>
            <div class="edit-button-container">
                <button id="grad-edit-button" class="edit"><i class="material-icons">create</i></button>
            </div>
        </div>
        <div id="grad-edit" class="edit">
            <form class="ui form">
                <div class="field">
                    <label>Graduation Month</label>
                    <div class="two fields">
                        <div class="field">
                            <select id="grad_month" class="ui fluid dropdown" name="grad_month">
                                <option value="">Month</option>
                                <option value="05/">May</option>
                                <option value="12/">December</option>
                            </select>
                        </div>
                        <div class="field">
                            <input id="year" type="text" name="year" placeholder="Year">
                        </div>
                    </div>
                </div>
            </form>
            <div id="grad-submit-button" class="ui right floated button" tabindex="0">Submit</div>
            <div id="grad-cancel-button" class="ui right floated button course-cancel">Cancel</div>
        </div>
    </div>

</section>
</body>
</html>

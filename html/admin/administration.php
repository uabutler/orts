<?php
require_once '../../php/database/courses.php';
require_once '../../php/database/faculty.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');

$active_semesters = Semester::listActive();
$inactive_semesters = Semester::listInactive();

$faculty = Faculty::list();

$majors = Major::listActive();
$minors = Minor::listActive();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>ORTS - Administration</title>
    <?php require '../../php/common-head.php'; ?>
    <link rel="stylesheet" href="/css/admin/administration.css">
    <link rel="stylesheet" href="/css/admin/administration-semesters.css">
    <link rel="stylesheet" href="/css/admin/administration-faculty.css">
    <link rel="stylesheet" href="/css/admin/administration-programs.css">
    <script src="/js/admin/administration.js"></script>
    <script src="/js/admin/administration-semesters.js"></script>
    <script src="/js/admin/administration-faculty.js"></script>
    <script src="/js/admin/administration-programs.js"></script>
    <script>
        let STATE = ""
    </script>
</head>
<body>
<?php require_once '../../php/header.php'; ?>
<?php require_once '../../php/navbar.php'; facultyNavbar("Administration"); ?>

<section id="administration-menu">
    <div class="ui fluid vertical pointing menu">
        <a id="semester-menu-button" class="active administration-menu item">
            Semesters
        </a>
        <a id="faculty-menu-button" class="administration-menu item">
            Faculty
        </a>
        <a id="program-menu-button" class="administration-menu item">
            Programs
        </a>
        <a id="student-menu-button" class="administration-menu item">
            Students
        </a>
        <a id="course-menu-button" class="administration-menu item">
            Courses
        </a>
    </div>
</section>

<section id="semester-administration" class="administration-section">
    <div id="new-semester-popup" class="ui modal">
        <div class="header">
            New Semester
        </div>
        <div class="content">
            <form class="ui form">
                <div class="field">
                    <label>Semester Info</label>
                    <div class="two fields">
                        <div class="field">
                            <input id="semester-description" type="text" placeholder='Ex. "Spring 2021"'>
                        </div>
                        <div class="field">
                            <input id="semester-code" type="text" class="numeric" placeholder='Ex. "202110"'>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="actions">
            <button id="new-semester-cancel-button" class="ui button">
                Cancel
            </button>
            <button id="new-semester-submit-button" class="ui button">
                Submit
            </button>
        </div>
    </div>
    <div>
        <h1 class="left floated">Semesters</h1>
        <button id="new-semester-popup-button" class="right floated ui labeled icon button">
            <i class="plus icon"></i>
            New
        </button>
    </div>
    <div id="semester-primary-content-display">
        <div class="ui centered inline active loader"></div>
    </div>
</section>

<section id="faculty-administration" class="administration-section hidden">
    <div id="new-faculty-popup" class="ui modal">
        <div class="header">
            New Faculty
        </div>
        <div class="content">
            <form class="ui form">
                <div class="field">
                    <label>Faculty Info</label>
                    <div class="two fields">
                        <div class="field">
                            <input id="faculty-first-name" type="text" placeholder="First Name">
                        </div>
                        <div class="field">
                            <input id="faculty-last-name" type="text" placeholder="Last Name">
                        </div>
                    </div>
                    <div class="field">
                        <div class="ui right labeled input">
                            <input required id="faculty-email" type="text" name="email" placeholder="Truman email">
                            <div class="ui label">@truman.edu</div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="actions">
            <button id="new-faculty-cancel-button" class="ui button">
                Cancel
            </button>
            <button id="new-faculty-submit-button" class="ui button">
                Submit
            </button>
        </div>
    </div>
    <div id="faculty-default-confirmation" class="ui basic modal">
        <div class="ui icon header">
            <i class="check circle icon"></i>
            Make Default
        </div>
        <div class="content">
            <p>Are you sure you want to make this the default faculty? The default faculty member cannot be deleted, and all requests assigned to the current default will be reassigned.</p>
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
    <div id="faculty-delete-confirmation" class="ui basic modal">
        <div class="ui icon header">
            <i class="trash icon"></i>
            Delete Faculty
        </div>
        <div class="content">
            <p>Are you sure you want to delete this faculty member? All requests assigned to them will be reassigned to the default faculty member.</p>
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
    <div>
        <h1 class="left floated">Faculty</h1>
        <button id="new-faculty-popup-button" class="right floated ui labeled icon button">
            <i class="plus icon"></i>
            New
        </button>
    </div>
    <div id="faculty-primary-content-display">
    </div>
</section>

<section id="program-administration" class="administration-section hidden">
    <div id="new-program-popup" class="ui modal">
        <div id="new-program-popup-header" class="header">
            New Programs
        </div>
        <div class="scrolling content">
            <form class="ui form">
                <div class="field">
                    <label id="new-program-popup-description">Place each program on a separate line</label>
                    <textarea id="program-input" rows="25"></textarea>
                </div>
            </form>
        </div>
        <div class="actions">
            <button id="new-program-cancel-button" class="ui button">
                Cancel
            </button>
            <button id="new-program-submit-button" class="ui button">
                Submit
            </button>
        </div>
    </div>
    <div id="program-primary-content-display">
        <div>
            <div>
                <h1 class="left floated">Majors</h1>
                <button id="new-majors-popup-button" class="right floated ui labeled icon button">
                    <i class="plus icon"></i>
                    New
                </button>
            </div>
            <div id="majors-primary-content-display">
                <div class="ui centered inline active loader"></div>
            </div>
        </div>
        <div>
            <div>
                <h1 class="left floated">Minors</h1>
                <button id="new-minors-popup-button" class="right floated ui labeled icon button">
                    <i class="plus icon"></i>
                    New
                </button>
            </div>
            <div id="minors-primary-content-display">
                <div class="ui centered inline active loader"></div>
            </div>
        </div>
    </div>
</section>

<section id="student-administration" class="administration-section hidden">
    <div>
        <h1>Students</h1>
    </div>
    <div id="student-primary-content-display">
        <div class="ui centered inline active loader"></div>
    </div>
</section>

<section id="course-administration" class="administration-section hidden">
    <div>
        <h1 class="left floated">Courses</h1>
        <button id="new-courses-popup-button" class="right floated ui labeled icon button">
            <i class="plus icon"></i>
            New
        </button>
    </div>
    <div id="course-primary-content-display">
    </div>
</section>

</body>
</html>
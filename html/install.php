<?php
require_once __DIR__ . '/php/database/tables.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');

if(file_exists('../conf/app.ini'))
    header("Location: /index.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $installing = true;
    $missing = [];

    $config = "[Database]\n";
    if(isset($_POST['db_name']))
        $config .= 'db_name = "' . $_POST['db_name'] . "\"\n";
    else
        array_push($missing, 'db_name');

    if(isset($_POST['db_host']))
        $config .= 'host = "' . $_POST['db_host'] . "\"\n";
    else
        array_push($missing, 'db_host');

    if(isset($_POST['db_user']))
        $config .= 'user = "' . $_POST['db_user'] . "\"\n";
    else
        array_push($missing, 'db_user');

    if(isset($_POST['db_pw']))
        $config .= 'passwd = "' . $_POST['db_pw'] . "\"\n";
    else
        array_push($missing, 'db_pw');

    $config .= "\n[CAS]\n";
    if(isset($_POST['cas_version']))
        $config .= 'version = "' . $_POST['cas_version'] . "\"\n";
    else
        array_push($missing, 'cas_version');

    if(isset($_POST['cas_host']))
        $config .= 'host = "' . $_POST['cas_host'] . "\"\n";
    else
        array_push($missing, 'cas_host');

    if(isset($_POST['cas_port']) && ctype_digit($_POST['cas_port']))
        $config .= 'port = ' . $_POST['cas_port'] . "\n";
    else
        array_push($missing, 'cas_port');

    if(isset($_POST['cas_context']))
        $config .= 'context = "' . $_POST['cas_context'] . "\"\n";
    else
        array_push($missing, 'cas_context');

    if(isset($_POST['cas_cert_path']))
        $config .= 'cert_path = "' . $_POST['cas_cert_path'] . "\"\n";
    else
        array_push($missing, 'cas_cert_path');

    $config .= "\n[Server]\n";
    if(isset($_POST['name']))
        $config .= 'name = "' . $_POST['name'] . "\"\n";
    else
        array_push($missing, 'name');

    if(isset($_POST['attachment_loc']))
        $config .= 'attachment_loc = "' . $_POST['attachment_loc'] . "\"\n";
    else
        array_push($missing, 'attachment_loc');

    if (count($missing) == 0)
    {
        $file = fopen(__DIR__ . "/../conf/app.ini", "w");
        fwrite($file, $config);
        fclose($file);
    }
}
else
{
    $installing = false;

    $required = ["pdo_mysql"];

    $missing = [];

    foreach($required as $module)
    {
        if(!extension_loaded($module))
            array_push($missing, $module);
    }
}

function dropTables($pdo)
{
    $pdo->exec("DROP TABLES IF EXISTS
    notifications,
    attachments,
    requests,
    sections,
    courses,
    departments,
    faculty,
    student_majors,
    student_minors,
    majors,
    minors,
    students,
    semesters;");
}

// create table
function createStudentTbl($pdo)
{
    global $student_tbl, $semester_tbl;
    echo "&emsp;$student_tbl<br>";
    $pdo->exec("CREATE TABLE $student_tbl (
    id int PRIMARY KEY AUTO_INCREMENT,
    email varchar(64) NOT NULL UNIQUE,
    first_name varchar(255) NOT NULL,
    last_name varchar(255),
    banner_id varchar(9) NOT NULL UNIQUE,
    grad_month varchar(7) NOT NULL,
    standing enum('Freshman', 'Sophomore', 'Junior', 'Senior') NOT NULL,
    last_active_sem int,
    FOREIGN KEY (last_active_sem) REFERENCES $semester_tbl(id))");
}

// Major and minor tables
function createMajorTbl($pdo)
{
    global $major_tbl;
    echo "&emsp;$major_tbl<br>";
    $pdo->exec("CREATE TABLE $major_tbl (
    id int PRIMARY KEY AUTO_INCREMENT,
    major varchar(255) NOT NULL UNIQUE,
    active boolean NOT NULL DEFAULT true)");
}

function createMinorTbl($pdo)
{
    global $minor_tbl;
    echo "&emsp;$minor_tbl<br>";
    $pdo->exec("CREATE TABLE $minor_tbl (
    id int PRIMARY KEY AUTO_INCREMENT,
    minor varchar(255) NOT NULL UNIQUE,
    active boolean NOT NULL DEFAULT true)");
}

// Student's majors and minors
function createStudentMajorTbl($pdo)
{
    global $student_major_tbl, $student_tbl, $major_tbl;
    echo "&emsp;$student_major_tbl<br>";
    $pdo->exec("CREATE TABLE $student_major_tbl (
    id int PRIMARY KEY AUTO_INCREMENT,
    student_id int NOT NULL,
    major_id int NOT NULL,
    FOREIGN KEY (student_id) REFERENCES $student_tbl(id),
    FOREIGN KEY (major_id) REFERENCES $major_tbl(id),
    CONSTRAINT pair UNIQUE (student_id, major_id))");
}

function createStudentMinorTbl($pdo)
{
    global $student_minor_tbl, $student_tbl, $minor_tbl;
    echo "&emsp;$student_minor_tbl<br>";
    $pdo->exec("CREATE TABLE $student_minor_tbl (
    id int PRIMARY KEY AUTO_INCREMENT,
    student_id int NOT NULL,
    minor_id int NOT NULL,
    FOREIGN KEY (student_id) REFERENCES $student_tbl(id),
    FOREIGN KEY (minor_id) REFERENCES $minor_tbl(id),
    CONSTRAINT pair UNIQUE (student_id, minor_id))");
}

// Faculty table
function createFacultyTbl($pdo)
{
    global $faculty_tbl;
    echo "&emsp;$faculty_tbl<br>";
    $pdo->exec("CREATE TABLE $faculty_tbl (
    id int PRIMARY KEY AUTO_INCREMENT,
    email varchar(64) NOT NULL UNIQUE,
    first_name varchar(255),
    last_name varchar(255) NOT NULL)");
}

// Class Tables
function createDepartmentTbl($pdo)
{
    global $department_tbl;
    echo "&emsp;$department_tbl<br>";
    $pdo->exec("CREATE TABLE $department_tbl (
    id int PRIMARY KEY AUTO_INCREMENT,
    department varchar(4) NOT NULL UNIQUE,
    active boolean NOT NULL DEFAULT true)");
}

function createCourseTbl($pdo)
{
    global $course_tbl, $department_tbl;
    echo "&emsp;$course_tbl<br>";
    $pdo->exec("CREATE TABLE $course_tbl (
    id int PRIMARY KEY AUTO_INCREMENT,
    department_id int NOT NULL,
    course_num int NOT NULL,
    title varchar(255) NOT NULL,
    active boolean NOT NULL DEFAULT true,
    FOREIGN KEY (department_id) REFERENCES $department_tbl(id),
    CONSTRAINT pair UNIQUE (department_id, course_num))");
}

function createSemesterTbl($pdo)
{
    global $semester_tbl;
    echo "&emsp;$semester_tbl<br>";
    $pdo->exec("CREATE TABLE $semester_tbl (
    id int PRIMARY KEY AUTO_INCREMENT,
    semester varchar(6) NOT NULL UNIQUE,
    description varchar(255) NOT NULL UNIQUE,
    active boolean NOT NULL DEFAULT true)");
}

function createSectionTbl($pdo)
{
    global $section_tbl, $course_tbl, $semester_tbl;
    echo "&emsp;$section_tbl<br>";
    $pdo->exec("CREATE TABLE $section_tbl (
    id int PRIMARY KEY AUTO_INCREMENT,
    course_id int NOT NULL,
    semester_id int NOT NULL,
    section int NOT NULL,
    crn varchar(4) NOT NULL,
    active boolean NOT NULL DEFAULT true,
    FOREIGN KEY (course_id) REFERENCES $course_tbl(id),
    FOREIGN KEY (semester_id) REFERENCES $semester_tbl(id),
    CONSTRAINT section_unq UNIQUE (course_id, semester_id, section),
    CONSTRAINT crn_unq UNIQUE (semester_id, crn))");
}

// Override request table
function createRequestTbl($pdo)
{
    global $request_tbl, $student_tbl, $section_tbl, $faculty_tbl;
    echo "&emsp;$request_tbl<br>";
    $pdo->exec("CREATE TABLE $request_tbl (
    id int PRIMARY KEY AUTO_INCREMENT,
    student_id int NOT NULL,
    last_modified datetime NOT NULL,
    section_id int NOT NULL,
    faculty_id int NOT NULL,
    status enum('Received', 'Approved', 'Provisionally Approved', 'Denied', 'Requires Faculty Approval') NOT NULL,
    justification text,
    banner boolean NOT NULL DEFAULT false,
    reason enum('Closed Class', 'Prerequisite', 'Other') NOT NULL,
    explanation text,
    active bool NOT NULL DEFAULT true,
    FOREIGN KEY (student_id) REFERENCES $student_tbl(id),
    FOREIGN KEY (section_id) REFERENCES $section_tbl(id),
    FOREIGN KEY (faculty_id) REFERENCES $faculty_tbl(id),
    CONSTRAINT pair UNIQUE (section_id, student_id))");
}

function createAttachmentTbl($pdo)
{
    global $attachment_tbl, $request_tbl;
    echo "&emsp;$attachment_tbl<br>";
    $pdo->exec("CREATE TABLE $attachment_tbl (
    id int PRIMARY KEY AUTO_INCREMENT,
    request_id int NOT NULL,
    name varchar(255) NOT NULL,
    path varchar(255) NOT NULL UNIQUE,
    FOREIGN KEY (request_id) REFERENCES $request_tbl(id))");
}

// Notifications table
function createNotificationTbl($pdo)
{
    global $notification_tbl;
    echo "&emsp;$notification_tbl<br>";
    $pdo->exec("CREATE TABLE $notification_tbl (
    id int PRIMARY KEY AUTO_INCREMENT,
    sender_email varchar(64) NOT NULL,
    receiver_email varchar(64) NOT NULL,
    creation datetime NOT NULL,
    body text)");
}

// Indicies
function createStudentIdx($pdo)
{
    global $student_tbl;
    echo "&emsp;$student_tbl<br>";
    $pdo->exec("CREATE INDEX email_inx ON $student_tbl (email)");
}

function createFacultyIdx($pdo)
{
    global $faculty_tbl;
    echo "&emsp;$faculty_tbl<br>";
    $pdo->exec("CREATE INDEX email_inx ON $faculty_tbl (email)");
}

function populateFaculty($pdo)
{
    global $faculty_tbl;
    echo "&emsp;$faculty_tbl<br>";
    $email = $_POST['email'];
    $first = $_POST['first_name'];
    $last = $_POST['last_name'];
    $pdo->exec("INSERT INTO $faculty_tbl (email, first_name, last_name) VALUES ('$email', '$first', '$last')");
}

function populateDepartments($pdo)
{
    global $departments, $department_tbl;
    $departments = implode(", ", preg_filter('/^/', "('", preg_filter('/$/', "')", explode(",", $_POST['departments']))));
    echo "&emsp;$department_tbl<br>";
    $pdo->exec("INSERT INTO $department_tbl (department) VALUES $departments");
}

function populateSemester($pdo)
{
    global $semester_tbl;
    echo "  $semester_tbl\n";
    $code = $_POST['semester_code'];
    $desc = $_POST['semester_name'];
    $pdo->exec("INSERT INTO $semester_tbl (semester, description) VALUES ('$code', '$desc')");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>ORTS - Main Page</title>
    <link rel="stylesheet" href="/css/root/install.css">
    <?php require 'php/common-head.php' ?>
</head>

<body class="grid-container">
<?php require_once 'php/header.php'; ?>

<div class="grid-item content">
    <?php if (!$installing): ?>
    <div class="warning">
        <b>WARNING:</b> Please ensure the database has been created before beginning
        the installation.

        This can be done using the following commands<br><br>
        <code>
            mysql> CREATE DATABASE orts DEFAULT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci';<br>
            mysql> GRANT ALL PRIVILEGES ON orts.* TO 'orts'@'localhost' IDENTIFIED BY 'password';<br>
            mysql> FLUSH PRIVILEGES;<br>
            mysql> EXIT
        </code>
        <br><br>
        All fields are <em>REQUIRED</em>.
    </div>
    <?php if(count($missing) != 0): ?>
    <div class="warning">
        <b>WARNING:</b> The following PHP modules are required, but could not be loaded.
        Please ensure they are installed properly before continuing.
        <ul>
            <?php foreach($missing as $module): ?>
            <li><?= $module ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    <noscript style="color:red;">
        <b>WARNING:</b> This site will <em>not</em> function without javascript. Please whitelist it before continuing.
    </noscript>
    <h2 class="truman-dark-bg">Server Information</h2>
    <form method="POST" action="/install.php">
        <table>
            <colgroup>
                <col style="width: 15em;">
            </colgroup>
            <tr>
                <td>Server Name:</td>
                <td><input required type="text" name="name" value="https://orts.truman.edu"></td>
            </tr>
            <tr>
                <td>Attachment Location:</td>
                <td><input required type="text" name="attachment_loc" value="/var/orts/attachments"></td>
            </tr>
        </table>
        <h2 class="truman-dark-bg">MySQL Database</h2>
        <table>
            <colgroup>
                <col style="width: 15em;">
            </colgroup>
            <tr>
                <td>Database Name:</td>
                <td><input required type="text" name="db_name" value="orts"></td>
            </tr>
            <tr>
                <td>Host:</td>
                <td><input required type="text" name="db_host" value="localhost"></td>
            </tr>
            <tr>
                <td>User Name:</td>
                <td><input required type="text" name="db_user" value="orts"></td>
            </tr>
            <tr>
                <td>Password:</td>
                <td><input required type="password" name="db_pw"></td>
            </tr>
        </table>
        <h2 class="truman-dark-bg">CAS</h2>
        <table>
            <colgroup>
                <col style="width: 15em;">
            </colgroup>
            <tr>
                <td>Version:</td>
                <td><input required type="text" name="cas_version" value="1.0"></td>
            </tr>
            <tr>
                <td>Host:</td>
                <td><input required type="text" name="cas_host" value="cas.truman.edu"></td>
            </tr>
            <tr>
                <td>Port:</td>
                <td><input required type="number" name="cas_port" value="8443"></td>
            </tr>
            <tr>
                <td>Context:</td>
                <td><input required type="text" name="cas_context" value="/cas"></td>
            </tr>
            <tr>
                <td>Certificate:</td>
                <td><input required type="text" name="cas_cert_path" value="/var/www/orts/html/assets/cert/truman.pem"></td>
            </tr>
        </table>
        <h2 class="truman-dark-bg">Defaults</h2>
        <table>
            <colgroup>
                <col style="width: 15em;">
            </colgroup>
            <tr>
                <td>Admin Email:</td>
                <td class="input-group suffix">
                    <input required class="email-input" type="text" name="email">
                    <span class="input-group-addon ">@truman.edu</span>
                </td>
            </tr>
            <tr>
                <td>Admin First Name:</td>
                <td><input required type="text" name="first_name"></td>
            </tr>
            <tr>
                <td>Admin Last Name:</td>
                <td><input required type="text" name="last_name"></td>
            </tr>
            <tr>
                <td>Semester Name:</td>
                <td><input required type="text" name="semester_name" value="Spring 2021"></td>
            </tr>
            <tr>
                <td>Semester Code:</td>
                <td><input required type="text" name="semester_code" value="202110"></td>
            </tr>
            <tr>
                <td>Departments:</td>
                <td><input required type="text" name="departments" value="CS,MATH,STAT,JINS,TRU"></td>
            </tr>
        </table>
        <div>
            <button type="submit" id="next">Install &raquo;</button>
        </div>
    </form>
    <?php else: ?>
    Connecting to DB<br>

    <?php
        $dsn = "mysql:host=".$_POST['db_host'].";dbname=".$_POST['db_name'];
        $pdo = new PDO($dsn, $_POST['db_user'], $_POST['db_pw']);
    ?>

    Creating tables...<br>

    <?php
    dropTables($pdo);
    createSemesterTbl($pdo);
    createStudentTbl($pdo);
    createMajorTbl($pdo);
    createMinorTbl($pdo);
    createStudentMajorTbl($pdo);
    createStudentMinorTbl($pdo);
    createFacultyTbl($pdo);
    createDepartmentTbl($pdo);
    createCourseTbl($pdo);
    createSectionTbl($pdo);
    createRequestTbl($pdo);
    createAttachmentTbl($pdo);
    createNotificationTbl($pdo);
    ?>

    done<br><br>
    Creating indicies...<br>

    <?php
    createStudentIdx($pdo);
    createFacultyIdx($pdo);
    ?>

    done<br><br>
    Populating tables...<br>

    <?php
    populateDepartments($pdo);
    populateFaculty($pdo);
    populateSemester($pdo);
    ?>

    done<br><br>

    Basic installation complete.
    Next, we recommend adding majors and minors on the admin page.
    <?php endif; ?>
</div>
</body>
</html>

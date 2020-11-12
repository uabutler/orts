<?php
include 'common_db.php';

/* The inital lists to populate tables */
$majors = ['Computer Science', 'Mathematics', 'Statistics'];
$minors = ['Computer Science', 'Mathematics', 'Statistics'];
$departments = ['CS', 'MATH', 'STAT'];

// Student table
function createStudentTbl($pdo)
{
  global $student_tbl;
  echo "  $student_tbl\n";
  $pdo->exec("CREATE TABLE $student_tbl (
    id int PRIMARY KEY AUTO_INCREMENT,
    email varchar(64) NOT NULL UNIQUE,
    first_name varchar(255) NOT NULL,
    last_name varchar(255),
    banner_id varchar(9) NOT NULL UNIQUE,
    grad_month varchar(7) NOT NULL,
    standing enum('Freshman', 'Sophomore', 'Junior', 'Senior') NOT NULL)");
}

// Major and minor tables
function createMajorTbl($pdo)
{
  global $major_tbl;
  echo "  $major_tbl\n";
  $pdo->exec("CREATE TABLE $major_tbl (
    id int PRIMARY KEY AUTO_INCREMENT,
    major varchar(255) NOT NULL UNIQUE,
    active boolean NOT NULL DEFAULT true)");
}

function createMinorTbl($pdo)
{
  global $minor_tbl;
  echo "  $minor_tbl\n";
  $pdo->exec("CREATE TABLE $minor_tbl (
    id int PRIMARY KEY AUTO_INCREMENT,
    minor varchar(255) NOT NULL UNIQUE,
    active boolean NOT NULL DEFAULT true)");
}

// Student's majors and minors
function createStudentMajorTbl($pdo)
{
  global $student_major_tbl, $student_tbl, $major_tbl;
  echo "  $student_major_tbl\n";
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
  echo "  $student_minor_tbl\n";
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
  echo "  $faculty_tbl\n";
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
  echo "  $department_tbl\n";
  $pdo->exec("CREATE TABLE $department_tbl (
    id int PRIMARY KEY AUTO_INCREMENT,
    department varchar(4) NOT NULL UNIQUE,
    active boolean NOT NULL DEFAULT true)");
}

function createCourseTbl($pdo)
{
  global $course_tbl, $department_tbl;
  echo "  $course_tbl\n";
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
  echo "  $semester_tbl\n";
  $pdo->exec("CREATE TABLE $semester_tbl (
    id int PRIMARY KEY AUTO_INCREMENT,
    semester varchar(6) NOT NULL UNIQUE,
    description varchar(255) NOT NULL UNIQUE,
    active boolean NOT NULL DEFAULT true)");
}

function createSectionTbl($pdo)
{
  global $section_tbl, $course_tbl, $semester_tbl;
  echo "  $section_tbl\n";
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
  global $request_tbl, $student_tbl, $section_tbl;
  echo "  $request_tbl\n";
  $pdo->exec("CREATE TABLE $request_tbl (
    id int PRIMARY KEY AUTO_INCREMENT,
    student_id int NOT NULL,
    last_modified datetime NOT NULL,
    section_id int NOT NULL,
    status enum('Received', 'Approved', 'Provisionally Approved', 'Denied', 'Requires Faculty Approval') NOT NULL,
    type enum('Closed Class', 'Prerequisite', 'Other') NOT NULL,
    explanation text,
    FOREIGN KEY (student_id) REFERENCES $student_tbl(id),
    FOREIGN KEY (section_id) REFERENCES $section_tbl(id),
    CONSTRAINT pair UNIQUE (section_id, student_id))");
}

function createAttachmentTbl($pdo)
{
  global $attachment_tbl, $request_tbl;
  echo "  $attachment_tbl\n";
  $pdo->exec("CREATE TABLE $attachment_tbl (
    id int PRIMARY KEY AUTO_INCREMENT,
    request_id int NOT NULL,
    name varchar(255) NOT NULL,
    path text NOT NULL,
    FOREIGN KEY (request_id) REFERENCES $request_tbl(id))");
}

// Notifications table
function createNotificationTbl($pdo)
{
  global $notification_tbl;
  echo "  $notification_tbl\n";
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
  echo "  $student_tbl\n";
  $pdo->exec("CREATE INDEX email_inx ON $student_tbl (email)");
}

function createFacultyIdx($pdo)
{
  global $faculty_tbl;
  echo "  $faculty_tbl\n";
  $pdo->exec("CREATE INDEX email_inx ON $faculty_tbl (email)");
}

function createValsList(array $arr): string
{
  return implode(", ", preg_filter('/^/', "('", preg_filter('/$/', "')", $arr)));
}

function populateMajors($pdo)
{
  global $majors, $major_tbl;
  echo "  $major_tbl\n";
  $major_param = createValsList($majors);
  $smt = $pdo->exec("INSERT INTO $major_tbl (major) VALUES $major_param");
}

function populateMinors($pdo)
{
  global $minors, $minor_tbl;
  echo "  $minor_tbl\n";
  $minor_param = createValsList($minors);
  $smt = $pdo->exec("INSERT INTO $minor_tbl (minor) VALUES $minor_param");
}

function populateDepartments($pdo)
{
  global $departments, $department_tbl;
  echo "  $department_tbl\n";
  $department_param = createValsList($departments);
  $smt = $pdo->exec("INSERT INTO $department_tbl (department) VALUES $department_param");
}

/**
 * Script starts here
 */
echo "Connecting to DB\n";
$pdo = connectDB();

echo "Creating tables...\n";

createStudentTbl($pdo);
createMajorTbl($pdo);
createMinorTbl($pdo);
createStudentMajorTbl($pdo);
createStudentMinorTbl($pdo);
createFacultyTbl($pdo);
createDepartmentTbl($pdo);
createCourseTbl($pdo);
createSemesterTbl($pdo);
createSectionTbl($pdo);
createRequestTbl($pdo);
createAttachmentTbl($pdo);
createNotificationTbl($pdo);

echo "done\n\n";
echo "Creating indicies...\n";

createStudentIdx($pdo);
createFacultyIdx($pdo);

echo "done\n\n";
echo "Populating tables...\n";

populateMajors($pdo);
populateMinors($pdo);
populateDepartments($pdo);

echo "done\n\n";

echo "[DEPLOY SCRIPT COMPLETE]\n";

?>

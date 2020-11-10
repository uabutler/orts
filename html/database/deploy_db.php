<?php
include 'common_db.php';

echo "Connecting to DB\n";
$pdo = connectDB();

// Student table
echo "Creating tables...\n";
echo "  $student_tbl\n";
$pdo->exec("CREATE TABLE $student_tbl (" .
  "id int PRIMARY KEY AUTO_INCREMENT," .
  "email varchar(64) NOT NULL UNIQUE," .
  "first_name varchar(255)," .
  "last_name varchar(255)," .
  "banner_id varchar(9) UNIQUE," .
  "grad_month varchar(7))");

// Major and minor tables
echo "  $major_tbl\n";
$pdo->exec("CREATE TABLE $major_tbl (" .
  "id int PRIMARY KEY AUTO_INCREMENT," .
  "major varchar(255) NOT NULL UNIQUE," .
  "active boolean NOT NULL DEFAULT true)");
echo "  $minor_tbl\n";
$pdo->exec("CREATE TABLE $minor_tbl (" .
  "id int PRIMARY KEY AUTO_INCREMENT," .
  "minor varchar(255) NOT NULL UNIQUE," .
  "active boolean NOT NULL DEFAULT true)");

// Student's majors and minors
echo "  $student_major_tbl\n";
$pdo->exec("CREATE TABLE $student_major_tbl (" .
  "id int PRIMARY KEY AUTO_INCREMENT," .
  "student_id int NOT NULL," .
  "major_id int NOT NULL)");

echo "  $student_minor_tbl\n";
$pdo->exec("CREATE TABLE $student_minor_tbl (" .
  "id int PRIMARY KEY AUTO_INCREMENT," .
  "student_id int NOT NULL," .
  "minor_id int NOT NULL)");

// Faculty table
echo "  $faculty_tbl\n";
$pdo->exec("CREATE TABLE $faculty_tbl (" .
  "id int PRIMARY KEY AUTO_INCREMENT," .
  "email varchar(64) NOT NULL UNIQUE," .
  "first_name varchar(255)," .
  "last_name varchar(255))");

// Class Tables
echo "  $department_tbl\n";
$pdo->exec("CREATE TABLE $department_tbl (" .
  "id int PRIMARY KEY AUTO_INCREMENT," .
  "department varchar(4) NOT NULL UNIQUE," .
  "active boolean NOT NULL DEFAULT true)");

echo "  $class_tbl\n";
$pdo->exec("CREATE TABLE $class_tbl (" .
  "id int PRIMARY KEY AUTO_INCREMENT," .
  "department_id int NOT NULL," .
  "class_num int NOT NULL," .
  "title varchar(255) NOT NULL," .
  "active boolean NOT NULL DEFAULT true)");

echo "  $semester_tbl\n";
$pdo->exec("CREATE TABLE $semester_tbl (" .
  "id int PRIMARY KEY AUTO_INCREMENT," .
  "semester varchar(6) NOT NULL," .
  "description varchar(255) NOT NULL," .
  "active boolean NOT NULL DEFAULT true)");

echo "  $section_tbl\n";
$pdo->exec("CREATE TABLE $section_tbl (" .
  "id int PRIMARY KEY AUTO_INCREMENT," .
  "class_id int NOT NULL," .
  "semester_id int NOT NULL," .
  "section int NOT NULL," .
  "crn varchar(4) NOT NULL," .
  "info varchar(255)," .
  "active boolean NOT NULL DEFAULT true)");

// Override request table
echo "  $request_tbl\n";
$pdo->exec("CREATE TABLE $request_tbl (" .
  "id int PRIMARY KEY AUTO_INCREMENT," .
  "student_id int NOT NULL," .
  "last_modified datetime NOT NULL," .
  "section_id int NOT NULL," .
  "status enum('Received', 'Approved', 'Provisionally Approved', 'Denied', 'Requires Faculty Approval') NOT NULL," .
  "type enum('Closed Class', 'Prerequisite', 'Other') NOT NULL," .
  "explaination text)");

// Notifications table
echo "  $notification_tbl\n";
$pdo->exec("CREATE TABLE $notification_tbl (" .
  "id int PRIMARY KEY AUTO_INCREMENT," .
  "sender_email varchar(64) NOT NULL," .
  "receiver_email varchar(64) NOT NULL," .
  "creation datetime NOT NULL," .
  "body text)");

echo "done\n\n";
echo "Creating indicies...\n";

echo "  $student_tbl\n";
$pdo->exec("CREATE INDEX email_inx ON $student_tbl (email)");
echo "  $faculty_tbl\n";
$pdo->exec("CREATE INDEX email_inx ON $faculty_tbl (email)");
echo "done\n\n";

echo "[DEPLOY SCRIPT COMPLETE]\n";

?>

<?php
include 'common_db.php';

$pdo = connectDB();

// Student table
$pdo->exec("CREATE TABLE $student_tbl (" .
  "id int PRIMARY KEY AUTO_INCREMENT," .
  "email varchar(64) NOT NULL UNIQUE," .
  "first_name varchar(255)," .
  "last_name varchar(255)," .
  "banner_id varchar(9) UNIQUE," .
  "grad_month varchar(7))");

// Major and minor tables
$pdo->exec("CREATE TABLE $major_tbl (" .
  "id int PRIMARY KEY AUTO_INCREMENT," .
  "major varchar(255) NOT NULL UNIQUE," .
  "active BOOLEAN)");

$pdo->exec("CREATE TABLE $minor_tbl (" .
  "id int PRIMARY KEY AUTO_INCREMENT," .
  "minor varchar(255) NOT NULL UNIQUE," .
  "active BOOLEAN)");

// Student's majors and minors
$pdo->exec("CREATE TABLE $student_major_tbl (" .
  "id int PRIMARY KEY AUTO_INCREMENT," .
  "student_id int NOT NULL," .
  "major_id int NOT NULL)");

$pdo->exec("CREATE TABLE $student_minor_tbl (" .
  "id int PRIMARY KEY AUTO_INCREMENT," .
  "student_id int NOT NULL," .
  "minor_id int NOT NULL)");

// Faculty table
$pdo->exec("CREATE TABLE $student_tbl (" .
  "id int PRIMARY KEY AUTO_INCREMENT," .
  "email varchar(64) NOT NULL UNIQUE," .
  "first_name varchar(255)," .
  "last_name varchar(255))");

// Override requests helper tables
$pdo->exec("CREATE TABLE $department_tbl (" .
  "id int PRIMARY KEY AUTO_INCREMENT," .
  "department varchar(4) NOT NULL UNIQUE," .
  "active BOOLEAN)");

$pdo->exec("CREATE TABLE $class_tbl (" .
  "id int PRIMARY KEY AUTO_INCREMENT," .
  "department_id int NOT NULL," .
  "class_num int NOT NULL," .
  "active BOOLEAN)");

$pdo->exec("CREATE TABLE $status_tbl (" .
  "id int PRIMARY KEY AUTO_INCREMENT," .
  "status varchar(255) NOT NULL UNIQUE," .
  "active BOOLEAN)");

// Override request table
$pdo->exec("CREATE TABLE $request_tbl (" .
  "id int PRIMARY KEY AUTO_INCREMENT," .
  "student_id int NOT NULL," .
  "last_modified datetime NOT NULL," .
  "crn varchar(4)," .
  "department_id int," .
  "class_id int," .
  "status_id int)");

// Notifications table
$pdo->exec("CREATE TABLE $notification_tbl (" .
  "id int PRIMARY KEY AUTO_INCREMENT," .
  "sender_email varchar(64) NOT NULL," .
  "receiver_email varchar(64) NOT NULL," .
  "creation datetime NOT NULL," .
  "body text)");
?>

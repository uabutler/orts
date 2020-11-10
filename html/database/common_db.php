<?php

/**
 * Here, we store the names of the tables as global variables
 */

// The database storing the information for students
$student_tbl = "students";
$major_tbl = "majors";
$minor_tbl = "minors";
$student_major_tbl = "student_majors";
$student_minor_tbl = "student_minors";
$faculty_tbl = "faculty";
$department_tbl = "departments";
$class_tbl = "classes";
$semester_tbl = "semesters";
$section_tbl = "sections";
$request_tbl = "requests";
$notification_tbl = "notifications";

/**
 * This funciton returns a connection to the MySQL DB
 */
function connectDB()
{
  $dbname = "group1";
  $dbhost = "borax.truman.edu";
  $user = "group1";
  $passwd = "370145";

  $dsn = "mysql:host=$dbhost;dbname=$dbname";

  return new PDO($dsn, $user, $passwd);
}

?>

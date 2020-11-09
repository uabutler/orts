<?php

/**
 * Here, we store the names of the tables as global variables
 */

// The database storing the information for students
$student_tbl = "test_tbl";
$major_tbl;
$minor_tbl;
$student_major_tbl;
$student_minor_tbl;
$student_tbl;
$department_tbl;
$class_tbl;
$status_tbl;
$request_tbl;
$notification_tbl;

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

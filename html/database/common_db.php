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
$course_tbl = "courses";
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

/**
 * This function takes in a multi-dim array where each smaller array has a single elements and return a
 * one-dim array.
 */
function flattenResult(array $result)
{
  $out = [];

  foreach($result as $row)
    array_push($out, $row[0]);

  return $out;
}

function getEnums($table, $field, $pdo=null)
{
  if(is_null($pdo))
    $pdo = connectDB();
    
  $smt = $pdo->prepare("SHOW COLUMNS FROM $table WHERE Field=:field");
  $smt->bindParam(":field", $field, PDO::PARAM_STR);
  $smt->execute();

  return explode("','",substr($smt->fetch(PDO::FETCH_ASSOC)['Type'],6,-2));
}

?>

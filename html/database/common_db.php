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
$attachment_tbl = "attachments";
$notification_tbl = "notifications";

// This function returns a connection to the MySQL DB
function connectDB(): PDO
{
  $dbname = "group1";
  $dbhost = "borax.truman.edu";
  $user = "group1";
  $passwd = "370145";

  $dsn = "mysql:host=$dbhost;dbname=$dbname";

  return new PDO($dsn, $user, $passwd);
}

function flattenResult(array $result): array
{
  $out = [];

  foreach($result as $row)
    array_push($out, $row[0]);

  return $out;
}

function arrayToDbList(array $arr): string
{
  return implode(', ', preg_filter('/^/', "'", preg_filter('/$/', "'", $arr)));
}

function getEnums(string $table, string $field, $pdo=null): array
{
  if(is_null($pdo))
    $pdo = connectDB();
    
  $smt = $pdo->prepare("SHOW COLUMNS FROM $table WHERE Field=:field");
  $smt->bindParam(":field", $field, PDO::PARAM_STR);
  $smt->execute();

  return explode("','",substr($smt->fetch(PDO::FETCH_ASSOC)['Type'],6,-2));
}

?>

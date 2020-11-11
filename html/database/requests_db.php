<?php
include_once 'classes_db.php';
include_once 'students_db.php';

class OverrideRequest
{
  public $course; // Course
  public $students; // Student
  public $last_modified; // Current time at function call
  public $status; // string
  public $type; // string
  public $explaination; // string
}

function listStatuses()
{
  global $request_tbl;
  return getEnums($request_tbl, "status");
}

function listOverrideTypes()
{
  global $request_tbl;
  return getEnums($request_tbl, "type");
}

function addOverrideRequest(Student $student, Section $section, string $status, string $type, string $explanation)
{
  // TODO: Implement minimum
  global $request_tbl;
  
  $pdo = connectDB();

  $smt = $pdo->prepare("INSERT INTO $request_tbl (student_id, last_modified, section_id, status, type, explanation) VALUES (:student_id, :last_modified, :section_id, :status, :type, :explanation)");
  $smt->bindParam(":student_id", $student->id, PDO::PARAM_STR); //STR?

  $time = gmmktime();
  $now = date("Y-m-d H:i:s", $time);

  $smt->bindParam(":last_modified", $now, PDO::PARAM_STR);  //STR I think?
  $smt->bindParam(":section_id", $section->id, PDO::PARAM_STR); //STR?
  $smt->bindParam(":status", $status, PDO::PARAM_STR);
  $smt->bindParam(":type", $type, PDO::PARAM_STR);
  $smt->bindParam(":explanation", $explanation, PDO::PARAM_STR);

  $smt->execute();

  // TODO: validate status, type
}

//TESTING

$me = new Student(mmk5213, Micah, Kuan, 123456789, 2021, null, null);
$me->id = 1;
$sec = new Section();
$sec->id = 1;

addOverrideRequest($me, $sec, teststatus, testtype, testexplanation);


?>

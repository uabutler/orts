<?php
include 'classes_db.php';
include 'students_db.php';

class OverrideRequest
{
  public $course; // Course
  public $students; // Student
  public $last_modified; // Current time at function call
  public $status; // string
  public $type; // string
  public $explaination; // string
}

function addOverrideRequest(Student $student, Section $section, string $status, string $type, string $explaination)
{
  // TODO: Implement minimum

  // TODO: validate status, type
}

?>

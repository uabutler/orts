<?php
include 'common_db.php';

function addDepartment(string $department)
{

}

function listDepartments(): array
{

}

function removeDepartment($department)
{

}

class Course
{
  public $id; // int
  public $department; // string
  public $course_num; // int
  public $title; // string

  function __construct(string $department
}

/**
 * Add a course to the database.
 */
function addCourse(Course $course)
{
  global $class_tbl;

  $pdo = connectDB();

  // Insert basic course info
  //$smt = $pdo->prepare("INSERT INTO $class_tbl (
}

function searchCourse($department, $course_num): Course
{

}

class Semester
{
  public $semester;
  public $description;
}

function addSemester(Semester $semester)
{
  // TODO: Make current active false
  INSERT INTO semesters (semester, description) VALUES ('123', 'Fall 2021');
}

class Section
{
  public $id; // int
  public $course; // Course
  public $semester; // Semester
  public $info; // String

  function __construct()
  {

  }
}

function addSection(Section $section)
{

}

function searchSection(string $department, int $class_num, int $section_number): Section
{
  $course = searchCourse($department, $class_num);
  // if null, return

  // use course_id, find current semester, and section number to find section
}

?>

mys<?php
include_once 'common_db.php';

function addDepartment(string $department)
{
  global $student_tbl;
  $pdo = connectDB();

  $smt = $pdo->prepare("INSERT INTO $department_tbl(department) VALUES (:department)")

  $smt-> bindParam(":department", $department, PDO::PARAM_STR);

  $smt-> execute();

}

function listDepartments(): array
{
  global $department_tbl;
  $department = array();

  $pdo = connectDB();
  $smt = $pdo->prepare("SELECT * FROM $department_tbl")
  $smt->execute();

  $data = $smt->fetch(PDO::FETCH_ASSOC);

  $arrlength = count($data);

  for($i = 0; $i < $arrlength; $i++) {
    $department[$i] = $data['department'];
  }

  return $department;
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

function __construct(int $id ,string $department,int $course_num, string $title){
    $this->id = $id;
    $this->department = $department;
    $this->course_num= $course_num;
    $this->title = $title;
  }


function addCourse(Course $course)
{
  global $class_tbl;

  $pdo = connectDB();

  // Insert basic course info
  //$smt = $pdo->prepare("INSERT INTO $class_tbl (
}

function searchCourse($department, $course_num): Course
{
  global , $class_tbl;
  $pdo = connectDB();
  $smt = $pdo->prepare("SELECT * FROM $class_tbl WHERE department = :department AND course_num=:course_num");
  $smt->bindParam(":department", $department,PDO::PARAM_STR);
  $smt->bindParam(":course_num", $course_num,PDO::PARAM_STR);
  $smt->execute();

  $course = $smt->fetch(PDO::FETCH_ASSOC);

  return new Course($course['id'],$course['department'], $course['course_num'], $course['title']);

}

class Semester
{
  public $semester;
  public $description;
}

function addSemester(Semester $semester)
{
  // TODO: Make current active false
  //INSERT INTO semesters (semester, description) VALUES ('123', 'Fall 2021');
}

class Section
{
  public $id; // int
  public $course; // Course
  public $semester; // Semester
  public $info; // String
  public $crn;//String


  function __construct(Course $course, Semester $semester, string $info, string $crn)
  {
    $this->course = $course;
    $this->semester = $semester;
    $this->info = $info;
    $this->crn = $crn;
  }
}

function addSection(Section $section)
{

}

function searchSection(string $department, int $class_num, int $section_number): Section
{
  global $section_tlb, $semester_tbl;
  $course = searchCourse($department, $class_num);


  if($course){
    $pdo = connectDB();

    $smt = $pdo->prepare("SELECT * FROM $semester_tbl WHERE active=:true");
    $smt->bindParam(":true", "true",PDO::PARAM_STR);
    $smt->execute();

    $data = $smt->fetch(PDO::FETCH_ASSOC);

    $smt = $pdo->prepare("SELECT * FROM $section_tbl WHERE semester_id = :semester_id AND class_id=:class_id");
    $smt->bindParam(":semester_id", $data['id'],PDO::PARAM_INT);
    $smt->bindParam(":class_id", $course->id,PDO::PARAM_STR);
    $smt->execute();

    $section = $smt->fetch(PDO::FETCH_ASSOC);

    return new Section($section['crn'], $course, $section['info'],$section['semester_id']);
  }else{
    return null;
  }

  // use course_id, find current semester, and section number to find section
}

?>

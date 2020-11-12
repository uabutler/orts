<?php
include_once 'common_db.php';

function addDepartment(string $department)
{
  global $department_tbl;
  $pdo = connectDB();

  $smt = $pdo->prepare("INSERT INTO $department_tbl(department) VALUES (:department)");

  $smt->bindParam(":department", $department, PDO::PARAM_STR);

  $smt->execute();

}

function listDepartments()
{
  global $department_tbl;
   $department = array();

  $pdo = connectDB();
  $smt = $pdo->prepare("SELECT * FROM $department_tbl");
  $smt->execute();

 // $data = $smt->fetch(PDO::FETCH_ASSOC);

  $i = 0;
  while ($data = $smt->fetch(PDO::FETCH_ASSOC)) {
    $department[$i] = $data['department'];
    $i++;
}

  return $department;
}

function removeDepartment($department)
{

}

class Course
{
  public $id; // int
  public $department_id; // string
  public $course_num; // int
  public $title; // string

  function __construct(int $id, int $department_id,int $course_num, string $title){
    $this->id = $id;
    $this->department = $department_id;
    $this->course_num= $course_num;
    $this->title = $title;
  }

  public static function addCourse(Course $course)
  {
    //global $class_tbl;

    //$pdo = connectDB();

  // Insert basic course info
  //$smt = $pdo->prepare("INSERT INTO $class_tbl (
  }

  public static function searchCourse(int $department_id, int $course_num): Course
  {
    global $class_tbl;
    $pdo = connectDB();
    $smt = $pdo->prepare("SELECT * FROM $class_tbl WHERE department_id = :department AND class_num=:course_num");
    $smt->bindParam(":department", $department_id,PDO::PARAM_INT);
    $smt->bindParam(":course_num", $course_num,PDO::PARAM_INT);
    $smt->execute();

    $data = $smt->fetch(PDO::FETCH_ASSOC);

    return new Course($data['id'],$data['department_id'], $data['class_num'], $data['title']);

  }
}


class Semester
{
  public $semester;
  public $description;


  function addSemester(Semester $semester)
  {
  // TODO: Make current active false
  //INSERT INTO semesters (semester, description) VALUES ('123', 'Fall 2021');
  }
}

class Section
{
  public $id; // int
  public $course; // Course
  public $semester; // Semester
  public $info; // String
  public $crn;//String


  function __construct(Course $course, int $semester_id, string $info, string $crn)
  {
    $this->course = $course;
    $this->semester = $semester_id;
    $this->info = $info;
    $this->crn = $crn;
  }

  function addSection(Section $section)
  {

  }

  public static function searchSection(string $department, int $class_num, int $section_number): Section
  {
    global $section_tbl, $semester_tbl,$department_tbl;
    $true = 1;
    $pdo = connectDB();
    $smt = $pdo->prepare("SELECT * FROM $department_tbl WHERE department=:department");
    $smt->bindParam(":department", $department,PDO::PARAM_STR);
    $smt->execute();

    $dept = $smt->fetch(PDO::FETCH_ASSOC);

    $course = Course::searchCourse($dept['id'], $class_num);


    if($course){
      $pdo = connectDB();

      $smt = $pdo->prepare("SELECT * FROM $semester_tbl WHERE active=:true");
      $smt->bindParam(":true", $true,PDO::PARAM_INT);
      $smt->execute();

      $data = $smt->fetch(PDO::FETCH_ASSOC);

      $smt = $pdo->prepare("SELECT * FROM $section_tbl WHERE semester_id = :semester_id AND class_id=:class_id");
      $smt->bindParam(":semester_id", $data['id'],PDO::PARAM_INT);
      $smt->bindParam(":class_id", $course->id,PDO::PARAM_STR);
      $smt->execute();

      $section = $smt->fetch(PDO::FETCH_ASSOC);

      return new Section($course,$section['semester_id'],$section['info'],$section['crn']);
    }else{
      return null;
    }

    // use course_id, find current semester, and section number to find section
  }

}
?>

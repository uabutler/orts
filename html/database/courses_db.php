<?php
include_once 'common_db.php';

class Department
{
  private $id;
  private $department;

  public function getId() { return $this->id; }
  public function getDept() { return $this->department; }

  /**
   * Setters
   */
  public function setDept(string $department)
  {
    $this->department = $department;
  }

  private function __construct(string $department, int $id=null)
  {
    $this->department = $department;
    $this->id = $id;
  }

  public static function listDepartments(): array
  {
    global $major_tbl;
    $pdo = connectDB();
    $smt = $pdo->query("SELECT department FROM $department_tbl");
    return flattenResult($smt->fetchAll(PDO::FETCH_NUM));
  }

  private function insertDB()
  {
    global $department_tbl;
    $pdo = connectDB();

    $smt = $pdo->prepare("INSERT INTO $department_tbl(department) VALUES (:department)");
    $smt->bindParam(":department", $this->department, PDO::PARAM_STR);
    $smt->execute();

    $smt = $pdo->prepare("SELECT id FROM $department_tbl WHERE department=:department");
    $smt->bindParam(":department", $this->department, PDO::PARAM_STR);
    $smt->execute();
    $this->id = $smt->fetch(PDO::FETCH_ASSOC)['id'];
  }

  private function updateDB()
  {
    global $department_tbl;
    $pdo = connectDB();

    $smt = $pdo->prepare("UPDATE $department_tbl SET department=:department WHERE id=:id");
    $smt->bindParam(":id", $this->id, PDO::PARAM_INT);
    $smt->bindParam(":department", $this->department, PDO::PARAM_STR);
    $smt->execute();
  }

  /**
   * Stores the current object in the database. If the object is newly created,
   * a new entry into the DB is made. If the student has been stored in the DB,
   * we update the existing entry
   */
  public function storeInDB()
  {
    // The id is set only when the student is already in the databse
    if(is_null($this->id))
      $this->insertDB();
    else
      $this->updateDB();
  }

  public static function buildDepartment(string $department)
  {
    return new Department($department);
  }

  public static function getDepartment(string $department)
  {
    global $department_tbl;
    $pdo = connectDB();

    $smt = $pdo->prepare("SELECT * FROM $department_tbl WHERE department=:department LIMIT 1");
    $smt->bindParam(":department", $department, PDO::PARAM_STR);
    $smt->execute();

    $data = $smt->fetch(PDO::FETCH_ASSOC);

    if(!$data) return null;

    return new Department($data['department'], $data['id']);
  }

  public static function getDepartmentById(string $department)
  {
    global $department_tbl;
    $pdo = connectDB();

    $smt = $pdo->prepare("SELECT * FROM $department_tbl WHERE id=:id LIMIT 1");
    $smt->bindParam(":id", $id, PDO::PARAM_INT);
    $smt->execute();

    $data = $smt->fetch(PDO::FETCH_ASSOC);

    if(!$data) return null;

    return new Department($data['department'], $data['id']);
  }
}

class Course
{
  private $id; // int
  private $department; // string
  private $course_num; // int
  private $title; // string

  private function __construct(Department $department, int $course_num, string $title, int $id=null)
  {
    $this->id = $id;
    $this->department = $department_id;
    $this->course_num= $course_num;
    $this->title = $title;
  }

  public function getId(){ return $this->id; }
  public function getDepartment(){ return $this->department; }
  public function getCourseNum(){ return $this->course_num; }
  public function getTitle(){ return $this->title; }

  public function setDepartment(Department $department)
  {

  }

  public static function getCourse(Department $department, int $course_num): Course
  {
    global $course_tbl;
    $pdo = connectDB();
    $smt = $pdo->prepare("SELECT * FROM $course_tbl WHERE department_id = :department AND course_num=:course_num");
    $smt->bindParam(":department", $department_id,PDO::PARAM_INT);
    $smt->bindParam(":course_num", $course_num,PDO::PARAM_INT);
    $smt->execute();

    $data = $smt->fetch(PDO::FETCH_ASSOC);

    return new Course($data['id'],$data['department_id'], $data['course_num'], $data['title']);
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
  public function getId(){return $this->id;}
  public function getCourse(){return $this->course;}
  public function getSemester(){return $this->semester}
  public function getInfo(){return $this->info}
  public function getCrn(){return $this->crn}
  
  function addSection(Section $section)
  {

  }

  public static function searchSection(string $department, int $course_num, int $section_number): Section
  {
    global $section_tbl, $semester_tbl,$department_tbl;
    $true = 1;
    $pdo = connectDB();
    $smt = $pdo->prepare("SELECT * FROM $department_tbl WHERE department=:department");
    $smt->bindParam(":department", $department,PDO::PARAM_STR);
    $smt->execute();

    $dept = $smt->fetch(PDO::FETCH_ASSOC);

    $course = Course::searchCourse($dept['id'], $course_num);


    if($course){
      $pdo = connectDB();

      $smt = $pdo->prepare("SELECT * FROM $semester_tbl WHERE active=:true");
      $smt->bindParam(":true", $true,PDO::PARAM_INT);
      $smt->execute();

      $data = $smt->fetch(PDO::FETCH_ASSOC);

      $smt = $pdo->prepare("SELECT * FROM $section_tbl WHERE semester_id = :semester_id AND course_id=:course_id");
      $smt->bindParam(":semester_id", $data['id'],PDO::PARAM_INT);
      $smt->bindParam(":course_id", $course->id,PDO::PARAM_STR);
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

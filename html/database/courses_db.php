<?php
include_once 'common_db.php';

// TODO: Deactivation

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

  public static function buildDepartment(string $department): Department
  {
    return new Department($department);
  }

  public static function getDepartment(string $department): ?Department
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

  public static function getDepartmentById(string $department): ?Department
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
  private $id;
  private $department;
  private $course_num;
  private $title;

  private function __construct(Department $department, int $course_num, string $title, int $id=null)
  {
    $this->id = $id;
    $this->department = $department;
    $this->course_num = $course_num;
    $this->title = $title;
  }

  public function getId(){ return $this->id; }
  public function getDepartment(){ return $this->department; }
  public function getCourseNum(){ return $this->course_num; }
  public function getTitle(){ return $this->title; }

  public function setDepartment(Department $department)
  {
    $this->department = $department;
  }

  public function setCourseNum(int $course_num)
  {
    $this->course_num = $course_num;
  }

  public function setTitle(int $title)
  {
    $this->title = $title;
  }

  private function insertDB()
  {
    global $course_tbl;
    $pdo = connectDB();

    $department_id = $this->department->getId();
    $smt = $pdo->prepare("INSERT INTO $course_tbl (department_id, course_num, title) VALUES (:department_id, :course_num, :title)");
    $smt->bindParam(":department_id", $department_id, PDO::PARAM_INT);
    $smt->bindParam(":course_num", $this->course_num, PDO::PARAM_INT);
    $smt->bindParam(":title", $this->title, PDO::PARAM_STR);
    $smt->execute();

    $smt = $pdo->prepare("SELECT id FROM $course_tbl WHERE department_id=:department_id AND course_num=:course_num");
    $smt->bindParam(":department_id", $department_id, PDO::PARAM_INT);
    $smt->bindParam(":course_num", $this->course_num, PDO::PARAM_INT);
    $smt->execute();
    $this->id = $smt->fetch(PDO::FETCH_ASSOC)['id'];
  }

  private function updateDB()
  {
    global $course_tbl;
    $pdo = connectDB();

    $department_id = $this->department->getId();
    $smt = $pdo->prepare("UPDATE $course_tbl SET department_id:department_id, course_num=:course_num, title=:title WHERE id=:id");
    $smt->bindParam(":id", $this->id, PDO::PARAM_INT);
    $smt->bindParam(":department_id", $department_id, PDO::PARAM_INT);
    $smt->bindParam(":course_num", $this->course_num, PDO::PARAM_INT);
    $smt->bindParam(":title", $this->title, PDO::PARAM_STR);
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

  public static function buildCourse(Department $department, int $course_num, string $title): Course
  {
    return new Course($department, $course_num, $title);
  }

  public static function getCourse(Department $department, int $course_num): ?Course
  {
    global $course_tbl;
    $pdo = connectDB();
    $department_id = $department->getId();
    $smt = $pdo->prepare("SELECT * FROM $course_tbl WHERE department_id=:department_id AND course_num=:course_num");
    $smt->bindParam(":department_id", $department_id, PDO::PARAM_INT);
    $smt->bindParam(":course_num", $course_num, PDO::PARAM_INT);
    $smt->execute();

    $data = $smt->fetch(PDO::FETCH_ASSOC);

    if(!$data) return null;

    return new Course($department, $data['course_num'], $data['title'], $data['id']);
  }

  public static function getCourseById(int $id): ?Course
  {
    global $course_tbl;
    $pdo = connectDB();
    $smt = $pdo->prepare("SELECT * FROM $course_tbl WHERE id=:id");
    $smt->bindParam(":id", $id, PDO::PARAM_INT);
    $smt->execute();

    $data = $smt->fetch(PDO::FETCH_ASSOC);

    if(!$data) return null;

    return new Course(Department::getDepartmentById($data['department_id']), $data['course_num'], $data['title'], $data['id']);
  }
}

class Semester
{
  private $id;
  private $semester;
  private $description;

  public function getId() { return $this->id; }
  public function getSemesterCode() { return $this->semester; }
  public function getDescription() { return $this->description; }

  /**
   * Setters
   */
  public function setSemsterCode(string $semester)
  {
    $this->semester = $semester;
  }

  public function setDescription(string $description)
  {
    $this->description = $description;
  }

  private function __construct(string $semester, string $description, int $id=null)
  {
    $this->id = $id;
    $this->semester = $semester;
    $this->description = $description;
  }

  public static function activeSemester(): Semester
  {
    // TODO
  }

  private function insertDB()
  {
    // TODO: exactly one item is active
    global $semester_tbl;
    $pdo = connectDB();

    $smt = $pdo->prepare("INSERT INTO $semester_tbl (semester, description) VALUES (:semester, :description)");
    $smt->bindParam(":semester", $this->semester, PDO::PARAM_STR);
    $smt->bindParam(":description", $this->description, PDO::PARAM_STR);
    $smt->execute();

    $smt = $pdo->prepare("SELECT id FROM $semester_tbl WHERE semester=:semester");
    $smt->bindParam(":semester", $this->semester, PDO::PARAM_STR);
    $smt->execute();
    $this->id = $smt->fetch(PDO::FETCH_ASSOC)['id'];
  }

  private function updateDB()
  {
    // TODO: exactly one item is active
    global $semester_tbl;
    $pdo = connectDB();

    $smt = $pdo->prepare("UPDATE $semester_tbl SET semester=:semester, description=:description WHERE id=:id");
    $smt->bindParam(":id", $this->id, PDO::PARAM_INT);
    $smt->bindParam(":semester", $this->semester, PDO::PARAM_STR);
    $smt->bindParam(":description", $this->description, PDO::PARAM_STR);
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

  public static function buildSemester(string $semester, string $description)
  {
    return new Semster($semester, $description);
  }

  public static function getSemester(string $description): ?Semester
  {
    global $semester_tbl;
    $pdo = connectDB();

    $smt = $pdo->prepare("SELECT * FROM $semester_tbl WHERE description=:description LIMIT 1");
    $smt->bindParam(":description", $description, PDO::PARAM_STR);
    $smt->execute();

    $data = $smt->fetch(PDO::FETCH_ASSOC);

    if(!$data) return null;

    return new Semester($data['semester'], $description, $data['id']);
  }

  public static function getSemesterByCode(string $semester): ?Semester
  {
    global $semester_tbl;
    $pdo = connectDB();

    $smt = $pdo->prepare("SELECT * FROM $semester_tbl WHERE semester=:semester LIMIT 1");
    $smt->bindParam(":semester", $semester, PDO::PARAM_STR);
    $smt->execute();

    $data = $smt->fetch(PDO::FETCH_ASSOC);

    if(!$data) return null;

    return new Semester($semester, $data['description'], $data['id']);
  }

  public static function getSemesterById(int $id): ?Semester
  {
    global $semester_tbl;
    $pdo = connectDB();

    $smt = $pdo->prepare("SELECT * FROM $semester_tbl WHERE id=:id LIMIT 1");
    $smt->bindParam(":id", $id, PDO::PARAM_INT);
    $smt->execute();

    $data = $smt->fetch(PDO::FETCH_ASSOC);

    if(!$data) return null;

    return new Semester($data['semester'], $data['description'], $id);
  }
}

class Section
{
  private $id; // int
  private $course; // Course
  private $semester; // Semester
  private $section; // int
  private $crn; // String


  function __construct(Course $course, Semester $semester, int $section, string $crn)
  {
    $this->course = $course;
    $this->semester = $semester;
    $this->section = $section;
    $this->crn = $crn;
  }

  public function getId() { return $this->id; }
  public function getCourse() { return $this->course; }
  public function getSemester() { return $this->semester; }
  public function getSectionNum() { return $this->section; }
  public function getCrn() { return $this->crn; }

  public function setCourse(Course $course)
  {
    $this->course = $course;
  }

  public function setSemster(Semester $semester)
  {
    $this->semester = $semester;
  }

  public function setSectionNum(int $section)
  {
    $this->section = $section;
  }

  public function setCrn(string $crn)
  {
    $this->crn = $crn;
  }

  private function insertDB()
  {
    global $section_tbl;
    $pdo = connectDB();

    $course_id = $this->course->getId();
    $semester_id = $this->semester->getId();
    $smt = $pdo->prepare("INSERT INTO $section_tbl (course_id, semester_id, section, crn) VALUES (:course_id, :semester_id, :section, :crn)");
    $smt->bindParam(":course_id", $course_id, PDO::PARAM_INT);
    $smt->bindParam(":semester_id", $semester_id, PDO::PARAM_INT);
    $smt->bindParam(":section", $this->section, PDO::PARAM_INT);
    $smt->bindParam(":crn", $this->crn, PDO::PARAM_STR);
    $smt->execute();

    $smt = $pdo->prepare("SELECT id FROM $section_tbl WHERE course_id=:course_id AND semester_id=:semester_id AND section=:section LIMIT 1");
    $smt->bindParam(":course_id", $course_id, PDO::PARAM_INT);
    $smt->bindParam(":semester_id", $semester_id, PDO::PARAM_INT);
    $smt->bindParam(":section", $section, PDO::PARAM_INT);
    $smt->execute();
    $this->id = $smt->fetch(PDO::FETCH_ASSOC)['id'];
  }

  private function updateDB()
  {
    global $section_tbl;
    $pdo = connectDB();

    $course_id = $this->course->getId();
    $semester_id = $this->semester->getId();
    $smt = $pdo->prepare("UPDATE $section_tbl SET course_id=:course_id, semester_id=:semester_id, section=:section WHERE id=:id");
    $smt->bindParam(":id", $this->id, PDO::PARAM_INT);
    $smt->bindParam(":course_id", $course_id, PDO::PARAM_INT);
    $smt->bindParam(":semester_id", $semester_id, PDO::PARAM_INT);
    $smt->bindParam(":section", $section, PDO::PARAM_INT);
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

  public static function buildSection(Course $course, Semester $semester, int $section, string $crn)
  {
    return new Section($course, $semester, $section, $crn);
  }
  
  public static function getSection(Course $course, Semester $semester, int $section): ?Section
  {
    global $section_tbl;
    $pdo = connectDB();
    $course_id = $this->course->getId();
    $semester_id = $this->semester->getId();
    $smt = $pdo->prepare("SELECT * FROM $section_tbl WHERE course_id=:course_id AND semester_id=:semester_id AND section=:section LIMIT 1");
    $smt->bindParam(":course_id", $course_id, PDO::PARAM_INT);
    $smt->bindParam(":semester_id", $semester_id, PDO::PARAM_INT);
    $smt->bindParam(":section", $section, PDO::PARAM_INT);
    $smt->execute();

    $data = $smt->fetch(PDO::FETCH_ASSOC);

    if(!$data) return null;

    return new Section($course, $semester, $data['section'], $data['crn']);
  }

  public static function getSectionByCrn(Semester $semester, string $crn): ?Section
  {
    global $section_tbl;
    $pdo = connectDB();
    $semester_id = $this->semester->getId();
    $smt = $pdo->prepare("SELECT * FROM $section_tbl WHERE semester_id=:semester_id AND crn=:crn LIMIT 1");
    $smt->bindParam(":semester_id", $semester_id, PDO::PARAM_INT);
    $smt->bindParam(":crn", $crn, PDO::PARAM_INT);
    $smt->execute();

    $data = $smt->fetch(PDO::FETCH_ASSOC);

    if(!$data) return null;

    return new Section(Course::getCourseById($data['course_id']), $semester, $data['section'], $crn);
  }

  public static function getSectionById(int $id): ?Section
  {
    global $section_tbl;
    $pdo = connectDB();
    $smt = $pdo->prepare("SELECT * FROM $section_tbl WHERE id=:id LIMIT 1");
    $smt->bindParam(":id", $id, PDO::PARAM_INT);
    $smt->execute();

    $data = $smt->fetch(PDO::FETCH_ASSOC);

    if(!$data) return null;

    return new Section(Course::getCourseById($data['course_id']), Semester::getSemesterById($data['semester_id']), $data['section'], $data['crn']);
  }
}
?>

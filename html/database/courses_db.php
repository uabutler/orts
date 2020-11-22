<?php
include_once 'common_db.php';

/**
 * Represents a department object from the database. Essentially acts as a wrapper for the department's prefix string
 */
class Department
{
  private $id;
  private $department;
  private $active;

  /**
   * The database id. Null if it hasn't been stored
   * @return int|null
   */
  public function getId(): ?int
  {
    return $this->id;
  }

  /**
   * The abridged string representing the department. E.g., "CS", "MATH", "STAT"
   * @return string
   */
  public function getDept(): string
  {
    return $this->department;
  }

  /**
   * Are sections in this department accepting override requests?
   * @return bool
   */
  public function isActive(): bool
  {
    return $this->active;
  }

  /**
   * @param string $department
   */
  public function setDept(string $department)
  {
    $this->department = $department;
  }

  /**
   * Activate the department
   */
  public function setActive()
  {
    $this->active = true;
  }

  /**
   * Deactivate the department
   */
  public function setInactive()
  {
    $this->active = true;
  }

  private function __construct(string $department, int $id=null)
  {
    $this->department = $department;
    $this->id = $id;
    $this->active = true;
  }

  /**
   * An array of strings representing all departments
   * @return array
   */
  public static function listDepartments(): array
  {
    global $department_tbl;
    $pdo = connectDB();
    $smt = $pdo->query("SELECT department FROM $department_tbl");
    return flattenResult($smt->fetchAll(PDO::FETCH_NUM));
  }

  /**
   * An array of strings representing all active departments
   * @return array
   */
  public static function listActiveDepartments(): array
  {
    global $department_tbl;
    $pdo = connectDB();
    $smt = $pdo->query("SELECT department FROM $department_tbl WHERE active=true");
    return flattenResult($smt->fetchAll(PDO::FETCH_NUM));
  }

  private function insertDB()
  {
    global $department_tbl;
    $pdo = connectDB();

    $smt = $pdo->prepare("INSERT INTO $department_tbl (department, active) VALUES (:department, :active)");
    $smt->bindParam(":department", $this->department, PDO::PARAM_STR);
    $smt->bindParam(":active", $this->active, PDO::PARAM_BOOL);
    $smt->execute();

    $this->id = $pdo->lastInsertId();
  }

  private function updateDB()
  {
    global $department_tbl, $course_tbl, $section_tbl;
    $pdo = connectDB();

    $smt = $pdo->prepare("UPDATE $department_tbl SET department=:department active=:active WHERE id=:id");
    $smt->bindParam(":id", $this->id, PDO::PARAM_INT);
    $smt->bindParam(":department", $this->department, PDO::PARAM_STR);
    $smt->bindParam(":active", $this->active, PDO::PARAM_BOOL);
    $smt->execute();

    // If this department is no longer active, we need to deactivate all of the courses of that department and
    // sections of those courses
    if(!$this->active)
    {
      $smt = $pdo->prepare("UPDATE $course_tbl SET active=false WHERE department_id=:department_id");
      $smt->bindParam(":department_id", $this->id, PDO::PARAM_INT);
      $smt->execute();

      $smt = $pdo->prepare("UPDATE $section_tbl JOIN $course_tbl SET $section_tbl.active=false WHERE $course_tbl.id=course_id AND department_id=:department_id");
      $smt->bindParam(":department_id", $this->id, PDO::PARAM_INT);
      $smt->execute();
    }
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

  /**
   * Given the abridged string representing a department, this method builds a local department object
   * @param string $department The abridged string representing a department
   * @return Department An object that only exists locally, isn't stored in DB
   */
  public static function buildDepartment(string $department): Department
  {
    return new Department($department);
  }

  /**
   * Retrieves a department from the database, or null if it doesn't exists
   * @param string $department
   * @return Department|null
   */
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

  /**
   * Retrieves a department from the database, or null if it doesn't exists
   * @param int $id The local id of the department in the database
   * @return Department|null
   */
  public static function getDepartmentById(int $id): ?Department
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

/**
 * Represents a course from the database. This holds a {@link Department}, a course number, and a course title. E.g., "CS 370 Software Engineering"
 */
class Course
{
  private $id;
  private $department;
  private $course_num;
  private $title;
  private $active;

  private function __construct(Department $department, int $course_num, string $title, int $id=null)
  {
    $this->id = $id;
    $this->department = $department;
    $this->course_num = $course_num;
    $this->title = $title;
    $this->active = true;
  }

  /**
   * The database id. Null if it hasn't been stored
   * @return int|null
   */
  public function getId(): ?int
  {
    return $this->id;
  }

  /**
   * The department of this course
   * @return Department
   */
  public function getDepartment(): Department
  {
    return $this->department;
  }

  /**
   * The course number, E.g., 370 in "CS 370"
   * @return int
   */
  public function getCourseNum(): int
  {
    return $this->course_num;
  }

  /**
   * The title of the course. E.g., Software Engineering
   * @return string
   */
  public function getTitle()
  {
    return $this->title;
  }

  /**
   * Is this course currently available to submit new requests for?
   * @return bool
   */
  public function isActive(): bool
  {
    return $this->active;
  }

  /**
   * @param Department $department
   */
  public function setDepartment(Department $department)
  {
    $this->department = $department;
  }

  /**
   * @param int $course_num
   */
  public function setCourseNum(int $course_num)
  {
    $this->course_num = $course_num;
  }

  /**
   * @param title $title
   */
  public function setTitle(title $title)
  {
    $this->title = $title;
  }

  /**
   * Activated the course if it's inactive.
   */
  public function setActive()
  {
    $this->active = true;
  }

  /**
   * Sets the course to inactive
   */
  public function setInactive()
  {
    $this->active = false;
  }

  private function insertDB()
  {
    global $course_tbl;
    $pdo = connectDB();

    $department_id = $this->department->getId();
    $smt = $pdo->prepare("INSERT INTO $course_tbl (department_id, course_num, title, active) VALUES (:department_id, :course_num, :title, :active)");
    $smt->bindParam(":department_id", $department_id, PDO::PARAM_INT);
    $smt->bindParam(":course_num", $this->course_num, PDO::PARAM_INT);
    $smt->bindParam(":title", $this->title, PDO::PARAM_STR);
    $smt->bindParam(":active", $this->active, PDO::PARAM_BOOL);
    $smt->execute();

    $this->id = $pdo->lastInsertId();
  }

  private function updateDB()
  {
    global $course_tbl, $section_tbl;
    $pdo = connectDB();

    $department_id = $this->department->getId();
    $smt = $pdo->prepare("UPDATE $course_tbl SET department_id:department_id, course_num=:course_num, title=:title active=:active WHERE id=:id");
    $smt->bindParam(":id", $this->id, PDO::PARAM_INT);
    $smt->bindParam(":department_id", $department_id, PDO::PARAM_INT);
    $smt->bindParam(":course_num", $this->course_num, PDO::PARAM_INT);
    $smt->bindParam(":title", $this->title, PDO::PARAM_STR);
    $smt->bindParam(":active", $this->active, PDO::PARAM_BOOL);
    $smt->execute();

    if(!$this->active)
    {
      $smt = $pdo->prepare("UPDATE $section_tbl SET active=false WHERE course_id=:course_id");
      $smt->bindParam(":course_id", $this->id, PDO::PARAM_INT);
      $smt->execute();
    }
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

  /**
   * Creates a local course object given a {@link Department} that's already stored in the DB, course number, and title
   * @param Department $department Must be stored in the DB
   * @param int $course_num
   * @param string $title
   * @return Course An object that only exists locally, isn't stored in DB
   */
  public static function buildCourse(Department $department, int $course_num, string $title): Course
  {
    return new Course($department, $course_num, $title);
  }

  /**
   * Retrieves a course from the database and creates an object. Returns null if it can't be found
   * @param Department $department
   * @param int $course_num
   * @return Course|null
   */
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

  /**
   * Given a database id, returns the course object, or null if it can't be found
   * @param int $id
   * @return Course|null
   */
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

/**
 * Represents a semester. This is used to relate the semester code (e.g., "202160") to its human readable form (e.g., Fall 2021)
 */
class Semester
{
  private $id;
  private $semester;
  private $description;
  private $active;

  /**
   * The database id. Null if it hasn't been stored
   * @return int|null
   */
  public function getId(): ?int
  {
    return $this->id;
  }

  /**
   * The semester code. E.g., "202160"
   * @return string
   */
  public function getSemesterCode()
  {
    return $this->semester;
  }

  /**
   * The human readable form. E.g., "Fall 2021"
   * @return string
   */
  public function getDescription(): string
  {
    return $this->description;
  }

  /**
   * Is the system currently accepting request for this semester
   * @return bool
   */
  public function isActive()
  {
    return $this->active;
  }

  /**
   * @param string $semester
   */
  public function setSemesterCode(string $semester)
  {
    $this->semester = $semester;
  }

  /**
   * @param string $description
   */
  public function setDescription(string $description)
  {
    $this->description = $description;
  }

  /**
   * Set this semester to active
   */
  public function setActive()
  {
    $this->active = true;
  }

  /**
   * Set this semester to inactive
   */
  public function setInactive()
  {
    $this->active = false;
  }

  /**
   * List all active semesters
   * @return array
   */
  public static function listActiveSemesters(): array
  {
    global $semester_tbl;
    $pdo = connectDB();

    $smt = $pdo->query("SELECT * FROM $semester_tbl WHERE active=true");

    $data = $smt->fetchAll(PDO::FETCH_ASSOC);

    // TODO: Ensure this is the convention for all list functions
    if(!$data) return [];

    $out = [];

    foreach($data as $row)
      array_push($out, new Semester($row['semester'], $row['description'], $row['id']));

    return $out;
  }

  private function __construct(string $semester, string $description, int $id=null)
  {
    $this->id = $id;
    $this->semester = $semester;
    $this->description = $description;
    $this->active = true;
  }

  private function insertDB()
  {
    global $semester_tbl;
    $pdo = connectDB();

    $smt = $pdo->prepare("INSERT INTO $semester_tbl (semester, description, active) VALUES (:semester, :description, :active)");
    $smt->bindParam(":semester", $this->semester, PDO::PARAM_STR);
    $smt->bindParam(":description", $this->description, PDO::PARAM_STR);
    $smt->bindParam(":active", $this->active, PDO::PARAM_BOOL);
    $smt->execute();

    $this->id = $pdo->lastInsertId();
  }

  private function updateDB()
  {
    global $semester_tbl, $section_tbl;
    $pdo = connectDB();

    $smt = $pdo->prepare("UPDATE $semester_tbl SET semester=:semester, description=:description, active=:active WHERE id=:id");
    $smt->bindParam(":id", $this->id, PDO::PARAM_INT);
    $smt->bindParam(":semester", $this->semester, PDO::PARAM_STR);
    $smt->bindParam(":description", $this->description, PDO::PARAM_STR);
    $smt->bindParam(":active", $this->active, PDO::PARAM_BOOL);
    $smt->execute();

    // Deactivate all sections in that semester
    if(!$this->active)
    {
      $smt = $pdo->prepare("UPDATE $section_tbl SET active=false WHERE semester_id=:semester_id");
      $smt->bindParam(":semester_id", $this->id, PDO::PARAM_INT);
      $smt->execute();
    }
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

  /**
   * Build a semester object locally
   * @param string $semester The code
   * @param string $description The human-readable version
   * @return Semster An object that only exists locally, isn't stored in DB
   */
  public static function buildSemester(string $semester, string $description)
  {
    return new Semster($semester, $description);
  }

  /**
   * Fetch a semester from the database given a human readable description, or null it it's not found
   * @param string $description
   * @return Semester|null
   */
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

  /**
   * Fetch a semester from the database given a code, or null it it's not found
   * @param string $semester
   * @return Semester|null
   */
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

  /**
   * Fetch a semester from the database given its id in the DB, or null it it's not found
   * @param int $id
   * @return Semester|null
   */
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

/**
 * Represents a section, a specific course offering in a given semester. This object relates the {@link Semester} object
 * to a {@link Course} object and stores a section number and CRN
 */
class Section
{
  private $id;
  private $course;
  private $semester;
  private $section;
  private $crn;

  /**
   * The database id. Null if it hasn't been stored
   * @return int|null
   */
  public function getId(): ?int
  {
    return $this->id;
  }

  /**
   * An object representing the course offered in this section
   * @return Course
   */
  public function getCourse(): Course
  {
    return $this->course;
  }

  /**
   * An object representing the semester this course is offered in
   * @return Semester
   */
  public function getSemester(): Semester
  {
    return $this->semester;
  }

  /**
   * Returns the section number. E.g., 2 in "CS 270: Section 02"
   * @return int
   */
  public function getSectionNum(): int
  {
    return $this->section;
  }

  /**
   * A string representing the four-digit CRN
   * @return string
   */
  public function getCrn(): string
  {
    return $this->crn;
  }

  /**
   * @param Course $course
   */
  public function setCourse(Course $course)
  {
    $this->course = $course;
  }

  /**
   * @param Semester $semester
   */
  public function setSemster(Semester $semester)
  {
    $this->semester = $semester;
  }

  /**
   * @param int $section
   */
  public function setSectionNum(int $section)
  {
    $this->section = $section;
  }

  /**
   * @param string $crn
   */
  public function setCrn(string $crn)
  {
    $this->crn = $crn;
  }

  function __construct(Course $course, Semester $semester, int $section, string $crn, int $id=null)
  {
    $this->id = $id;
    $this->course = $course;
    $this->semester = $semester;
    $this->section = $section;
    $this->crn = $crn;
  }

  private function insertDB()
  {
    global $section_tbl;
    $pdo = connectDB();

    $course_id = $this->course->getId();
    $semester_id = $this->semester->getId();
    $smt = $pdo->prepare("INSERT INTO $section_tbl (course_id, semester_id, section, crn, active) VALUES (:course_id, :semester_id, :section, :crn, :active)");
    $smt->bindParam(":course_id", $course_id, PDO::PARAM_INT);
    $smt->bindParam(":semester_id", $semester_id, PDO::PARAM_INT);
    $smt->bindParam(":section", $this->section, PDO::PARAM_INT);
    $smt->bindParam(":crn", $this->crn, PDO::PARAM_STR);
    $smt->bindParam(":active", $this->active, PDO::PARAM_BOOL);
    $smt->execute();

    $this->id = $pdo->lastInsertId();
  }

  private function updateDB()
  {
    global $section_tbl;
    $pdo = connectDB();

    $course_id = $this->course->getId();
    $semester_id = $this->semester->getId();
    $smt = $pdo->prepare("UPDATE $section_tbl SET course_id=:course_id, semester_id=:semester_id, section=:section, active=:active WHERE id=:id");
    $smt->bindParam(":id", $this->id, PDO::PARAM_INT);
    $smt->bindParam(":course_id", $course_id, PDO::PARAM_INT);
    $smt->bindParam(":semester_id", $semester_id, PDO::PARAM_INT);
    $smt->bindParam(":section", $section, PDO::PARAM_INT);
    $smt->bindParam(":active", $this->active, PDO::PARAM_BOOL);
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

  /**
   * Builds a section object locally
   * @param Course $course Must already be stored in the database
   * @param Semester $semester Must already be stored in the databse
   * @param int $section
   * @param string $crn
   * @return Section An object that only exists locally, isn't stored in DB
   */
  public static function buildSection(Course $course, Semester $semester, int $section, string $crn)
  {
    return new Section($course, $semester, $section, $crn);
  }

  /**
   * Retrieve a section given the course, semester, and section number from the DB, or null if it can't be found.
   * @param Course $course Must be stored in the database
   * @param Semester $semester Must be stored in the database
   * @param int $section
   * @return Section|null
   */
  public static function getSection(Course $course, Semester $semester, int $section): ?Section
  {
    global $section_tbl;
    $pdo = connectDB();
    $course_id = $course->getId();
    $semester_id = $semester->getId();
    $smt = $pdo->prepare("SELECT * FROM $section_tbl WHERE course_id=:course_id AND semester_id=:semester_id AND section=:section LIMIT 1");
    $smt->bindParam(":course_id", $course_id, PDO::PARAM_INT);
    $smt->bindParam(":semester_id", $semester_id, PDO::PARAM_INT);
    $smt->bindParam(":section", $section, PDO::PARAM_INT);
    $smt->execute();

    $data = $smt->fetch(PDO::FETCH_ASSOC);

    if(!$data) return null;

    return new Section($course, $semester, $data['section'], $data['crn'], $data['id']);
  }

  /**
   * Retrieves a section given the semester and CRN. Null if it can't be found
   * @param Semester $semester Must be stored in the database
   * @param string $crn
   * @return Section|null
   */
  public static function getSectionByCrn(Semester $semester, string $crn): ?Section
  {
    global $section_tbl;
    $pdo = connectDB();
    $semester_id = $semester->getId();
    $smt = $pdo->prepare("SELECT * FROM $section_tbl WHERE semester_id=:semester_id AND crn=:crn LIMIT 1");
    $smt->bindParam(":semester_id", $semester_id, PDO::PARAM_INT);
    $smt->bindParam(":crn", $crn, PDO::PARAM_STR);
    $smt->execute();

    $data = $smt->fetch(PDO::FETCH_ASSOC);

    if(!$data) return null;

    return new Section(Course::getCourseById($data['course_id']), $semester, $data['section'], $crn, $data['id']);
  }

  /**
   * Retrieves a section given its database id. Null if it can't be found
   * @param int $id
   * @return Section|null
   */
  public static function getSectionById(int $id): ?Section
  {
    global $section_tbl;
    $pdo = connectDB();
    $smt = $pdo->prepare("SELECT * FROM $section_tbl WHERE id=:id LIMIT 1");
    $smt->bindParam(":id", $id, PDO::PARAM_INT);
    $smt->execute();

    $data = $smt->fetch(PDO::FETCH_ASSOC);

    if(!$data) return null;

    return new Section(Course::getCourseById($data['course_id']), Semester::getSemesterById($data['semester_id']), $data['section'], $data['crn'], $data['id']);
  }
}

?>

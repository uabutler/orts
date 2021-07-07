<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/programs.php';
require_once __DIR__ . '/requests.php';

/**
 * Represents a department object from the database. Essentially acts as a wrapper for the department's prefix string
 */
class Department implements JsonSerializable
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
     * Deactivate the department
     */
    public function setInactive()
    {
        $this->active = true;
    }

    private function __construct(string $department, bool $active = true, int $id = null)
    {
        $this->department = $department;
        $this->id = $id;
        $this->active = $active;
    }

    /**
     * An array of strings representing all active departments
     * @return array
     */
    public static function listActive(): array
    {
        global $department_tbl;
        $pdo = connectDB();
        $smt = $pdo->query("SELECT department FROM $department_tbl WHERE active=true");
        return flattenResult($smt->fetchAll(PDO::FETCH_NUM));
    }

    /**
     * An array of strings representing all inactive departments
     * @return array
     */
    public static function list(): array
    {
        global $department_tbl;
        $pdo = connectDB();

        $smt = $pdo->query("SELECT * FROM $department_tbl");

        $data = $smt->fetchAll(PDO::FETCH_ASSOC);

        if (!$data) return [];

        $out = [];

        foreach ($data as $row)
            $out[] = new Department($row['department'], $row['active'], $row['id']);

        return $out;
    }

    private function insertDB(): bool
    {
        global $department_tbl;
        $pdo = connectDB();

        $smt = $pdo->prepare("INSERT INTO $department_tbl (department, active) VALUES (:department, :active)");
        $smt->bindParam(":department", $this->department, PDO::PARAM_STR);
        $smt->bindParam(":active", $this->active, PDO::PARAM_BOOL);

        if (!$smt->execute()) return false;

        $this->id = $pdo->lastInsertId();

        return true;
    }

    private function updateDB(): bool
    {
        global $department_tbl;
        $pdo = connectDB();

        $smt = $pdo->prepare("UPDATE $department_tbl SET department=:department WHERE id=:id");
        $smt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $smt->bindParam(":department", $this->department, PDO::PARAM_STR);

        if (!$smt->execute()) return false;

        if ($this->active)
            return true;
        else
            return self::inactiveById($this->id);
    }

    /**
     * Stores the current object in the database. If the object is newly created,
     * a new entry into the DB is made. If the student has been stored in the DB,
     * we update the existing entry
     */
    public function storeInDB(): bool
    {
        // The id is set only when the student is already in the databse
        if (is_null($this->id))
            return $this->insertDB();
        else
            return $this->updateDB();
    }

    /**
     * Delete the current element from the database. This is NOT reversible (unlike setting to inactive)
     * @return bool Did the deletion succeed?
     */
    public function deleteFromDB(): bool
    {
        return self::deleteById($this->id);
    }

    /**
     * @param int $id The id of the element to be deleted
     * @param PDO|null $pdo A connection. We can pass one if one hasn't been created, otherwise, we'll create a new one
     * @return bool Did the deletion succeed?
     */
    public static function deleteById(int $id, PDO $pdo = null): bool
    {
        global $department_tbl, $course_tbl;
        if (is_null($pdo)) $pdo = connectDB();

        // Delete all attachments
        $smt = $pdo->prepare("select id from $course_tbl where department_id=:id");
        $smt->bindParam(":id", $id, PDO::PARAM_INT);
        $smt->execute();
        $ids = flattenResult($smt->fetchAll(PDO::FETCH_NUM));
        foreach ($ids as $i) Course::deleteById($i, $pdo);

        // Delete the request
        return deleteByIdFrom($department_tbl, $id, $pdo);
    }

    public static function inactiveById(int $id, PDO $pdo = null): bool
    {
        global $department_tbl, $course_tbl;
        if (is_null($pdo)) $pdo = connectDB();

        $smt = $pdo->prepare("select id from $course_tbl where department_id=:id");
        $smt->bindParam(":id", $id, PDO::PARAM_INT);
        $smt->execute();
        $ids = flattenResult($smt->fetchAll(PDO::FETCH_NUM));
        foreach ($ids as $i) Course::inactiveById($i, $pdo);

        return inactiveByIdFrom($department_tbl, $id, $pdo);
    }

    /**
     * Given the abridged string representing a department, this method builds a local department object
     * @param string $department The abridged string representing a department
     * @return Department An object that only exists locally, isn't stored in DB
     */
    public static function build(string $department): Department
    {
        return new Department($department);
    }

    /**
     * Retrieves a department from the database, or null if it doesn't exists
     * @param string $department
     * @return Department|null
     */
    public static function get(string $department): ?Department
    {
        global $department_tbl;
        $pdo = connectDB();

        $smt = $pdo->prepare("SELECT * FROM $department_tbl WHERE department=:department LIMIT 1");
        $smt->bindParam(":department", $department, PDO::PARAM_STR);
        $smt->execute();

        $data = $smt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Department($data['department'], $data['active'], $data['id']);
    }

    /**
     * Retrieves a department from the database, or null if it doesn't exists
     * @param int $id The local id of the department in the database
     * @return Department|null
     */
    public static function getById(int $id): ?Department
    {
        global $department_tbl;
        $pdo = connectDB();

        $smt = $pdo->prepare("SELECT * FROM $department_tbl WHERE id=:id LIMIT 1");
        $smt->bindParam(":id", $id, PDO::PARAM_INT);
        $smt->execute();

        $data = $smt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Department($data['department'], $data['active'], $data['id']);
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}

/**
 * Represents a course from the database. This holds a {@link Department}, a course number, and a course title. E.g., "CS 370 Software Engineering"
 */
class Course implements JsonSerializable
{
    private $id;
    private $department;
    private $course_num;
    private $title;
    private $active;

    private function __construct(Department $department, int $course_num, string $title,
                                 bool $active = true, int $id = null)
    {
        $this->id = $id;
        $this->department = $department;
        $this->course_num = $course_num;
        $this->title = $title;
        $this->active = $active;
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
    public function getTitle(): string
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
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * Sets the course to inactive
     */
    public function setInactive()
    {
        $this->active = false;
    }

    private function insertDB(): bool
    {
        global $course_tbl;
        $pdo = connectDB();

        $department_id = $this->department->getId();
        $smt = $pdo->prepare("INSERT INTO $course_tbl (department_id, course_num, title, active) VALUES (:department_id, :course_num, :title, :active)");
        $smt->bindParam(":department_id", $department_id, PDO::PARAM_INT);
        $smt->bindParam(":course_num", $this->course_num, PDO::PARAM_INT);
        $smt->bindParam(":title", $this->title, PDO::PARAM_STR);
        $smt->bindParam(":active", $this->active, PDO::PARAM_BOOL);

        if (!$smt->execute()) return false;

        $this->id = $pdo->lastInsertId();

        return true;
    }

    private function updateDB(): bool
    {
        global $course_tbl;
        $pdo = connectDB();

        $department_id = $this->department->getId();
        $smt = $pdo->prepare("UPDATE $course_tbl SET department_id=:department_id, course_num=:course_num, title=:title WHERE id=:id");
        $smt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $smt->bindParam(":department_id", $department_id, PDO::PARAM_INT);
        $smt->bindParam(":course_num", $this->course_num, PDO::PARAM_INT);
        $smt->bindParam(":title", $this->title, PDO::PARAM_STR);

        if (!$smt->execute()) return false;

        if ($this->active)
            return true;
        else
            return self::inactiveById($this->id);
    }

    /**
     * Stores the current object in the database. If the object is newly created,
     * a new entry into the DB is made. If the student has been stored in the DB,
     * we update the existing entry
     */
    public function storeInDB(): bool
    {
        // The id is set only when the student is already in the databse
        if (is_null($this->id))
            return $this->insertDB();
        else
            return $this->updateDB();
    }

    /**
     * Delete the current element from the database. This is NOT reversible (unlike setting to inactive)
     * @return bool Did the deletion succeed?
     */
    public function deleteFromDB(): bool
    {
        return self::deleteById($this->id);
    }

    /**
     * @param int $id The id of the element to be deleted
     * @param PDO|null $pdo A connection. We can pass one if one hasn't been created, otherwise, we'll create a new one
     * @return bool Did the deletion succeed?
     */
    public static function deleteById(int $id, PDO $pdo = null): bool
    {
        global $course_tbl, $section_tbl;
        if (is_null($pdo)) $pdo = connectDB();

        // Delete all attachments
        $smt = $pdo->prepare("select id from $section_tbl where course_id=:id");
        $smt->bindParam(":id", $id, PDO::PARAM_INT);
        $smt->execute();
        $ids = flattenResult($smt->fetchAll(PDO::FETCH_NUM));
        foreach ($ids as $i) Section::deleteById($i, $pdo);

        // Delete the request
        return deleteByIdFrom($course_tbl, $id, $pdo);
    }

    public static function inactiveById(int $id, PDO $pdo = null): bool
    {
        global $course_tbl, $section_tbl;
        if (is_null($pdo)) $pdo = connectDB();

        $smt = $pdo->prepare("select id from $section_tbl where course_id=:id");
        $smt->bindParam(":id", $id, PDO::PARAM_INT);
        $smt->execute();
        $ids = flattenResult($smt->fetchAll(PDO::FETCH_NUM));
        foreach ($ids as $i) Section::inactiveById($i, $pdo);

        return inactiveByIdFrom($course_tbl, $id, $pdo);
    }


    /**
     * Creates a local course object given a {@link Department} that's already stored in the DB, course number, and title
     * @param Department $department Must be stored in the DB
     * @param int $course_num
     * @param string $title
     * @return Course An object that only exists locally, isn't stored in DB
     */
    public static function build(Department $department, int $course_num, string $title): Course
    {
        return new Course($department, $course_num, $title);
    }

    private static function listHelper(bool $active): array
    {
        global $course_tbl;
        $pdo = connectDB();

        $query = "SELECT * FROM $course_tbl" . ($active ? " WHERE active=true" : "");

        $smt = $pdo->query($query);

        $data = $smt->fetchAll(PDO::FETCH_ASSOC);

        if (!$data) return [];

        $returnList = array();

        foreach ($data as $row)
        {
            $department = Department::getById($row['department_id']);
            $request = new Course($department, $row['course_num'], $row['title'], $row['active'], $row['id']);
            $returnList[] = $request;
        }

        return $returnList;
    }

    /**
     * An array of strings representing all active courses
     * @return array
     */
    public static function listActive(): array
    {
        return self::listHelper(true);
    }

    /**
     * An array of strings representing all inactive courses
     * @return array
     */
    public static function list(): array
    {
        return self::listHelper(false);
    }

    /**
     * Retrieves a course from the database and creates an object. Returns null if it can't be found
     * @param Department $department
     * @param int $course_num
     * @return Course|null
     */
    public static function get(Department $department, int $course_num): ?Course
    {
        global $course_tbl;
        $pdo = connectDB();
        $department_id = $department->getId();
        $smt = $pdo->prepare("SELECT * FROM $course_tbl WHERE department_id=:department_id AND course_num=:course_num");
        $smt->bindParam(":department_id", $department_id, PDO::PARAM_INT);
        $smt->bindParam(":course_num", $course_num, PDO::PARAM_INT);
        $smt->execute();

        $data = $smt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Course($department, $data['course_num'], $data['title'], $data['active'], $data['id']);
    }

    /**
     * Given a database id, returns the course object, or null if it can't be found
     * @param int $id
     * @return Course|null
     */
    public static function getById(int $id): ?Course
    {
        global $course_tbl;
        $pdo = connectDB();
        $smt = $pdo->prepare("SELECT * FROM $course_tbl WHERE id=:id");
        $smt->bindParam(":id", $id, PDO::PARAM_INT);
        $smt->execute();

        $data = $smt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Course(Department::getById($data['department_id']), $data['course_num'], $data['title'],
            $data['active'], $data['id']);
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}

/**
 * Represents a semester. This is used to relate the semester code (e.g., "202160") to its human readable form (e.g., Fall 2021)
 */
class Semester implements JsonSerializable
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
    public function getCode(): string
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
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param string $semester
     */
    public function setCode(string $semester): void
    {
        $this->semester = $semester;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * Set this semester to inactive
     */
    public function setInactive(): void
    {
        $this->active = false;
    }

    private function __construct(string $semester, string $description, bool $active = true, int $id = null)
    {
        $this->id = $id;
        $this->semester = $semester;
        $this->description = $description;
        $this->active = $active;
    }

    private function insertDB(): bool
    {
        global $semester_tbl;
        $pdo = connectDB();

        $smt = $pdo->prepare("INSERT INTO $semester_tbl (semester, description, active) VALUES (:semester, :description, :active)");
        $smt->bindParam(":semester", $this->semester, PDO::PARAM_STR);
        $smt->bindParam(":description", $this->description, PDO::PARAM_STR);
        $smt->bindParam(":active", $this->active, PDO::PARAM_BOOL);
        if (!$smt->execute()) return false;

        $this->id = $pdo->lastInsertId();

        return true;
    }

    private function updateDB(): bool
    {
        global $semester_tbl;
        $pdo = connectDB();

        $smt = $pdo->prepare("UPDATE $semester_tbl SET semester=:semester, description=:description WHERE id=:id");
        $smt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $smt->bindParam(":semester", $this->semester, PDO::PARAM_STR);
        $smt->bindParam(":description", $this->description, PDO::PARAM_STR);

        if (!$smt->execute()) return false;

        if ($this->active)
            return true;
        else
            return self::inactiveById($this->id);
    }

    /**
     * Stores the current object in the database. If the object is newly created,
     * a new entry into the DB is made. If the student has been stored in the DB,
     * we update the existing entry
     */
    public function storeInDB(): bool
    {
        // The id is set only when the student is already in the databse
        if (is_null($this->id))
            return $this->insertDB();
        else
            return $this->updateDB();
    }

    /**
     * Delete the current element from the database. This is NOT reversible (unlike setting to inactive)
     * @return bool Did the deletion succeed?
     */
    public function deleteFromDB(): bool
    {
        return self::deleteById($this->id);
    }

    /**
     * @param int $id The id of the element to be deleted
     * @param PDO|null $pdo A connection. We can pass one if one hasn't been created, otherwise, we'll create a new one
     * @return bool Did the deletion succeed?
     */
    public static function deleteById(int $id, PDO $pdo = null): bool
    {
        global $semester_tbl, $section_tbl;
        if (is_null($pdo)) $pdo = connectDB();

        // Delete all attachments
        $smt = $pdo->prepare("SELECT id FROM $section_tbl WHERE semester_id=:id");
        $smt->bindParam(":id", $id, PDO::PARAM_INT);
        $smt->execute();
        $ids = flattenResult($smt->fetchAll(PDO::FETCH_NUM));
        foreach ($ids as $i) Section::deleteById($i, $pdo);

        return deleteByIdFrom($semester_tbl, $id, $pdo);
    }

    public static function inactiveById(int $id, PDO $pdo = null): bool
    {
        global $semester_tbl, $section_tbl;
        if (is_null($pdo)) $pdo = connectDB();

        $smt = $pdo->prepare("SELECT id FROM $section_tbl WHERE semester_id=:id");
        $smt->bindParam(":id", $id, PDO::PARAM_INT);
        $smt->execute();
        $ids = flattenResult($smt->fetchAll(PDO::FETCH_NUM));
        foreach ($ids as $i) Section::inactiveById($i, $pdo);

        return inactiveByIdFrom($semester_tbl, $id, $pdo);
    }


    /**
     * Build a semester object locally
     * @param string $semester The code
     * @param string $description The human-readable version
     * @return Semester An object that only exists locally, isn't stored in DB
     */
    public static function build(string $semester, string $description)
    {
        return new Semester($semester, $description);
    }

    private static function listHelper(bool $active): array
    {
        global $semester_tbl;
        $pdo = connectDB();

        $smt = $pdo->prepare("SELECT * FROM $semester_tbl WHERE active=:active");
        $smt->bindParam(":active", $active, PDO::PARAM_BOOL);
        $smt->execute();

        $data = $smt->fetchAll(PDO::FETCH_ASSOC);

        if (!$data) return [];

        $out = [];

        foreach ($data as $row)
            $out[] = new Semester($row['semester'], $row['description'], $row['active'], $row['id']);

        return $out;
    }

    public static function listActive(): array
    {
        return self::listHelper(true);
    }

    public static function listInactive(): array
    {
        return self::listHelper(false);
    }

    /**
     * Fetch a semester from the database given a human readable description, or null it it's not found
     * @param string $description
     * @return Semester|null
     */
    public static function get(string $description): ?Semester
    {
        global $semester_tbl;
        $pdo = connectDB();

        $smt = $pdo->prepare("SELECT * FROM $semester_tbl WHERE description=:description LIMIT 1");
        $smt->bindParam(":description", $description, PDO::PARAM_STR);
        $smt->execute();

        $data = $smt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Semester($data['semester'], $description, $data['active'], $data['id']);
    }

    /**
     * Fetch a semester from the database given a code, or null it it's not found
     * @param string $semester
     * @return Semester|null
     */
    public static function getByCode(string $semester): ?Semester
    {
        global $semester_tbl;
        $pdo = connectDB();

        $smt = $pdo->prepare("SELECT * FROM $semester_tbl WHERE semester=:semester LIMIT 1");
        $smt->bindParam(":semester", $semester, PDO::PARAM_STR);
        $smt->execute();

        $data = $smt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Semester($semester, $data['description'], $data['active'], $data['id']);
    }

    /**
     * Fetch a semester from the database given its id in the DB, or null it it's not found
     * @param int $id
     * @return Semester|null
     */
    public static function getById(int $id): ?Semester
    {
        global $semester_tbl;
        $pdo = connectDB();

        $smt = $pdo->prepare("SELECT * FROM $semester_tbl WHERE id=:id LIMIT 1");
        $smt->bindParam(":id", $id, PDO::PARAM_INT);
        $smt->execute();

        $data = $smt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Semester($data['semester'], $data['description'], $data['active'], $id);
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}

/**
 * Represents a section, a specific course offering in a given semester. This object relates the {@link Semester} object
 * to a {@link Course} object and stores a section number and CRN
 */
class Section implements JsonSerializable
{
    private $id;
    private $course;
    private $semester;
    private $section;
    private $crn;
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
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
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
    public function setSemester(Semester $semester)
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

    /**
     * Inactive this section
     */
    public function setInactive(): void
    {
        $this->active = false;
    }


    private function __construct(Course $course, Semester $semester, int $section, string $crn, bool $active = true,
                                 int $id = null)
    {
        $this->id = $id;
        $this->course = $course;
        $this->semester = $semester;
        $this->section = $section;
        $this->crn = $crn;
        $this->active = $active;
    }

    private function insertDB(): bool
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

        if (!$smt->execute()) return false;

        $this->id = $pdo->lastInsertId();

        return true;
    }

    private function updateDB(): bool
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

        if (!$smt->execute()) return false;

        if ($this->active)
            return true;
        else
            return self::inactiveById($this->id);
    }

    /**
     * Stores the current object in the database. If the object is newly created,
     * a new entry into the DB is made. If the student has been stored in the DB,
     * we update the existing entry
     */
    public function storeInDB(): bool
    {
        // The id is set only when the student is already in the databse
        if (is_null($this->id))
            return $this->insertDB();
        else
            return $this->updateDB();
    }

    /**
     * Delete the current element from the database. This is NOT reversible (unlike setting to inactive)
     * @return bool Did the deletion succeed?
     */
    public function deleteFromDB(): bool
    {
        return self::deleteById($this->id);
    }

    /**
     * @param int $id The id of the element to be deleted
     * @param PDO|null $pdo A connection. We can pass one if one hasn't been created, otherwise, we'll create a new one
     * @return bool Did the deletion succeed?
     */
    public static function deleteById(int $id, PDO $pdo = null): bool
    {
        global $section_tbl, $request_tbl;
        if (is_null($pdo)) $pdo = connectDB();

        // Delete all attachments
        $smt = $pdo->prepare("select id from $request_tbl where section_id=:id");
        $smt->bindParam(":id", $id, PDO::PARAM_INT);
        $smt->execute();
        $ids = flattenResult($smt->fetchAll(PDO::FETCH_NUM));
        foreach ($ids as $i) Request::deleteById($i, $pdo);

        // Delete the request
        return deleteByIdFrom($section_tbl, $id, $pdo);
    }

    public static function inactiveById(int $id, PDO $pdo = null): bool
    {
        global $section_tbl, $request_tbl;
        if (is_null($pdo)) $pdo = connectDB();

        $smt = $pdo->prepare("select id from $request_tbl where section_id=:id");
        $smt->bindParam(":id", $id, PDO::PARAM_INT);
        $smt->execute();
        $ids = flattenResult($smt->fetchAll(PDO::FETCH_NUM));
        foreach ($ids as $i) Request::inactiveById($i, $pdo);

        return inactiveByIdFrom($section_tbl, $id, $pdo);
    }

    /**
     * Builds a section object locally
     * @param Course $course Must already be stored in the database
     * @param Semester $semester Must already be stored in the databse
     * @param int $section
     * @param string $crn
     * @return Section An object that only exists locally, isn't stored in DB
     */
    public static function build(Course $course, Semester $semester, int $section, string $crn): Section
    {
        return new Section($course, $semester, $section, $crn);
    }

    private static function listHelper(): array
    {

    }

    /**
     * Retrieve a section given the course, semester, and section number from the DB, or null if it can't be found.
     * @param Course $course Must be stored in the database
     * @param Semester $semester Must be stored in the database
     * @param int $section
     * @return Section|null
     */
    public static function get(Course $course, Semester $semester, int $section): ?Section
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

        if (!$data) return null;

        return new Section($course, $semester, $data['section'], $data['crn'], $data['active'], $data['id']);
    }

    /**
     * Retrieves a section given the semester and CRN. Null if it can't be found
     * @param Semester $semester Must be stored in the database
     * @param string $crn
     * @return Section|null
     */
    public static function getByCrn(Semester $semester, string $crn): ?Section
    {
        global $section_tbl;
        $pdo = connectDB();
        $semester_id = $semester->getId();
        $smt = $pdo->prepare("SELECT * FROM $section_tbl WHERE semester_id=:semester_id AND crn=:crn LIMIT 1");
        $smt->bindParam(":semester_id", $semester_id, PDO::PARAM_INT);
        $smt->bindParam(":crn", $crn, PDO::PARAM_STR);
        $smt->execute();

        $data = $smt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Section(Course::getById($data['course_id']), $semester, $data['section'], $crn, $data['active'],
            $data['id']);
    }

    /**
     * Retrieves a section given its database id. Null if it can't be found
     * @param int $id
     * @return Section|null
     */
    public static function getById(int $id): ?Section
    {
        global $section_tbl;
        $pdo = connectDB();
        $smt = $pdo->prepare("SELECT * FROM $section_tbl WHERE id=:id LIMIT 1");
        $smt->bindParam(":id", $id, PDO::PARAM_INT);
        $smt->execute();

        $data = $smt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Section(Course::getById($data['course_id']), Semester::getById($data['semester_id']),
            $data['section'], $data['crn'], $data['active'], $data['id']);
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}

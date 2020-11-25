<?php
include_once 'common_db.php';
include_once 'courses_db.php';
include_once 'students_db.php';

class Request
{
    private $id;
    private $student;
    private $section;
    private $last_modified;
    private $status;
    private $reason;
    private $explanation;
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
     * @return Student
     */
    public function getStudent(): Student
    {
        return $this->student;
    }

    /**
     * @return Section
     */
    public function getSection(): Section
    {
        return $this->section;
    }

    /**
     * A timestamp representing the last time the request was modified
     * @return string
     */
    public function getLastModified(): string
    {
        return $this->last_modified;
    }

    /**
     * The status of the override request: Denied, Approved, etc. Must equal a value from {@link
     * OverrideRequest::listStatuses()}.
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * The reason the student submitted the override request
     * @return string
     */
    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * The text the student submitted for their explaination
     * @return string
     */
    public function getExplanation(): string
    {
        return $this->explanation;
    }

    /**
     * Is the request active or in the archive
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param Student $student
     */
    public function setStudent(Student $student)
    {
        $this->student = $student;
    }

    /**
     * @param Section $section
     */
    public function setSection(Section $section)
    {
        $this->section = $section;
    }

    /**
     * @param string $last_modified
     */
    public function setLastModified(string $last_modified)
    {
        $this->last_modified = $last_modified;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status)
    {
        $this->status = $status;
    }

    /**
     * @param string $reason
     */
    public function setReason(string $reason)
    {
        $this->reason = $reason;
    }

    /**
     * @param string $explanation
     */
    public function setExplanation(string $explanation)
    {
        $this->explanation = $explanation;
    }

    /**
     * Activate the request, this moves it out of the archive
     */
    public function setActive(): void
    {
        $this->active = true;
    }

    /**
     * Deactivate the request, this effectively archives it
     */
    public function setInactive(): void
    {
        $this->active = false;
    }

    private function __construct(Student $student, Section $section, string $last_modified, string $status,
                                 string $reason, string $explanation, bool $active = true, int $id = null)
    {
        $this->id = $id;
        $this->student = $student;
        $this->section = $section;
        $this->last_modified = $last_modified;
        $this->status = $status;
        $this->reason = $reason;
        $this->explanation = $explanation;
        $this->active = $active;
    }

    private function insertDB()
    {
        global $request_tbl;

        $pdo = connectDB();

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //Shows SQL errors
        $smt = $pdo->prepare("INSERT INTO $request_tbl (student_id, last_modified, section_id, status, reason, explanation) VALUES (:student_id, :last_modified, :section_id, :status, :reason, :explanation)");

        $studentid = Request::getStudent()->getId();
        $sectionid = Request::getSection()->getId();
        $smt->bindParam(":student_id", $studentid, PDO::PARAM_INT);
        $smt->bindParam(":last_modified", $this->last_modified, PDO::PARAM_STR);
        $smt->bindParam(":section_id", $sectionid, PDO::PARAM_INT);
        $smt->bindParam(":status", $this->status, PDO::PARAM_STR);
        $smt->bindParam(":reason", $this->reason, PDO::PARAM_STR);
        $smt->bindParam(":explanation", $this->explanation, PDO::PARAM_STR);

        $smt->execute();

        $this->id = $pdo->lastInsertId();
    }

    private function updateDB()
    {
        global $request_tbl;

        $pdo = connectDB();

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //Shows SQL errors
        $smt = $pdo->prepare("UPDATE $request_tbl SET student_id=:student_id, last_modified=:last_modified, section_id=:section_id, status=:status, reason=:reason, explanation=:explanation WHERE id=:id");

        $studentid = Request::getStudent()->getId();
        $sectionid = Request::getSection()->getId();
        $smt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $smt->bindParam(":student_id", $studentid, PDO::PARAM_INT);
        $smt->bindParam(":last_modified", $this->last_modified, PDO::PARAM_STR);
        $smt->bindParam(":section_id", $sectionid, PDO::PARAM_INT);
        $smt->bindParam(":status", $this->status, PDO::PARAM_STR);
        $smt->bindParam(":reason", $this->reason, PDO::PARAM_STR);
        $smt->bindParam(":explanation", $this->explanation, PDO::PARAM_STR);
        $smt->execute();
    }

    /**
     * Stores the current object in the database. If the object is newly created,
     * a new entry into the DB is made. If the request has been stored in the DB,
     * we update the existing entry
     */
    public function storeInDB()
    {
        // The id is set only when the student is already in the database
        if (is_null($this->id))
            $this->insertDB();
        else
            $this->updateDB();
    }

    /**
     * A list of strings representing all the options for statuses
     * @return array
     */
    public static function listStatuses()
    {
        global $request_tbl;
        return getEnums($request_tbl, "status");
    }

    /**
     * A list of strings representing all of the options for request reasons
     * @return array
     */
    public static function listReasons()
    {
        global $request_tbl;
        return getEnums($request_tbl, "reason");
    }

    /**
     * Constructs a new request locally
     * @param Student $student
     * @param Section $section
     * @param string $status Must match a value from {@link Request::listStatuses()}
     * @param string $reason Must match a value from {@link Request::listOverrideReasons()}
     * @param string $explanation
     * @return Request|null
     */
    public static function build(Student $student, Section $section, string $status, string $reason,
                                 string $explanation) //Need?: OverrideRequest
    {
        $time = gmmktime();
        $now = date("Y-m-d H:i:s", $time);
        if (in_array($status, Request::listStatuses()) && in_array($reason, Request::listReasons()))
            return new Request($student, $section, $now, $status, $reason, $explanation);
        else
            return null; //null? error message?
    }

    /**
     * Retrieve a students requests from the database
     * @param Student $student
     */
    public static function get(Student $student): array
    {
        global $request_tbl;
        $pdo = connectDB();

        $smt = $pdo->prepare("SELECT * FROM $request_tbl");
        $smt->execute();

        $requestsList = $smt->fetchAll();
        $returnList = array();

        foreach ($requestsList as $row)
        {
            $section = Section::getById($row['section_id']);
            $request = new Request($student, $section, $row['last_modified'], $row['status'], $row['reason'],
                $row['explanation'], $row['id']);
            array_push($returnList, $request);
        }

        return $returnList;
    }

    /**
     * Retrieve a request from the database by ID
     * @param int $id
     * @return Request
     */
    public static function getById(int $id): Request
    {
        global $request_tbl;
        $pdo = connectDB();

        $smt = $pdo->prepare("SELECT * FROM $request_tbl WHERE id=:request_id LIMIT 1");
        $smt->bindParam(":request_id", $id, PDO::PARAM_INT);
        $smt->execute();

        $data = $smt->fetch(PDO::FETCH_ASSOC);

        $student = Student::getById($data['student_id']);
        $section = Section::getById($data['section_id']);
        return new Request($student, $section, $data['last_modified'], $data['status'], $data['reason'],
            $data['explanation'], $data['id']);
    }

    /**
     * Retrieve all requests from the database
     */
    public static function getOverrideRequests(): array
    {
        global $request_tbl;
        $pdo = connectDB();

        $smt = $pdo->prepare("SELECT * FROM $request_tbl");
        $smt->execute();

        $requestsList = $smt->fetchAll();
        $returnList = array();
        foreach ($requestsList as $row)
        {
            $student = Student::getById($row['student_id']);
            $section = Section::getById($row['section_id']);
            $request = new Request($student, $section, $row['last_modified'], $row['status'], $row['reason'],
                $row['explanation'], $row['id']);
            array_push($returnList, $request);
        }

        return $returnList;
    }
}

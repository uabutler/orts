<?php
include_once 'common_db.php';
include_once 'courses_db.php';
include_once 'students_db.php';
include_once 'faculty_db.php';

class Request implements JsonSerializable
{
    private $id;
    private $student;
    private $section;
    private $last_modified;
    private $faculty;
    private $status;
    private $justification;
    private $banner;
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
     * @return Faculty
     */
    public function getFaculty(): Faculty
    {
        return $this->faculty;
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
     * @return string|null
     */
    public function getJustification(): ?string
    {
        return htmlspecialchars($this->justification, ENT_QUOTES);
    }

    /**
     * @return bool
     */
    public function isInBanner(): bool
    {
        return $this->banner;
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
        return htmlspecialchars($this->explanation, ENT_QUOTES);
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
     * @param Faculty $faculty
     */
    public function setFaculty(Faculty $faculty): void
    {
        $this->faculty = $faculty;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status)
    {
        $this->status = $status;
    }

    /**
     * @param string|null $justification
     */
    public function setJustification(?string $justification): void
    {
        $this->justification = $justification;
    }

    /**
     * The request has been put in banner
     */
    public function setInBanner(): void
    {
        $this->banner = true;
    }

    /**
     * The request is not yet in banner
     */
    public function setNotInBanner(): void
    {
        $this->banner = false;
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

    private function __construct(Student $student, Section $section, string $last_modified, Faculty $faculty, string
    $status, ?string $justification, bool $banner, string $reason, string $explanation, bool $active = true, int $id
    = null)
    {
        $this->id = $id;
        $this->student = $student;
        $this->section = $section;
        $this->last_modified = $last_modified;
        $this->faculty = $faculty;
        $this->status = $status;
        $this->justification = $justification;
        $this->banner = $banner;
        $this->reason = $reason;
        $this->explanation = $explanation;
        $this->active = $active;
    }

    private function insertDB()
    {
        global $request_tbl;

        $pdo = connectDB();

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //Shows SQL errors
        $smt = $pdo->prepare("INSERT INTO $request_tbl (student_id, last_modified, section_id, faculty_id, status, justification, banner, reason, explanation, active) VALUES (:student_id, :last_modified, :section_id, :faculty_id, :status, :justification, :banner, :reason, :explanation, :active)");

        $studentid = $this->student->getId();
        $facultyid = $this->faculty->getId();
        $sectionid = $this->section->getId();
        $smt->bindParam(":student_id", $studentid, PDO::PARAM_INT);
        $smt->bindParam(":last_modified", $this->last_modified, PDO::PARAM_STR);
        $smt->bindParam(":section_id", $sectionid, PDO::PARAM_INT);
        $smt->bindParam(":faculty_id", $facultyid, PDO::PARAM_INT);
        $smt->bindParam(":status", $this->status, PDO::PARAM_STR);
        $smt->bindParam(":justification", $this->justification, PDO::PARAM_STR);
        $smt->bindParam(":banner", $this->banner, PDO::PARAM_BOOL);
        $smt->bindParam(":reason", $this->reason, PDO::PARAM_STR);
        $smt->bindParam(":explanation", $this->explanation, PDO::PARAM_STR);
        $smt->bindParam(":active", $this->active, PDO::PARAM_BOOL);

        if(!$smt->execute()) return false;

        $this->id = $pdo->lastInsertId();

        return true;
    }

    private function updateDB()
    {
        global $request_tbl;

        $pdo = connectDB();

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //Shows SQL errors
        $smt = $pdo->prepare("UPDATE $request_tbl SET student_id=:student_id, last_modified=:last_modified, section_id=:section_id, faculty_id=:faculty_id, status=:status, justification=:justification, banner=:banner, reason=:reason, explanation=:explanation, active=:active WHERE id=:id");

        $studentid = $this->student->getId();
        $facultyid = $this->faculty->getId();
        $sectionid = $this->section->getId();
        $smt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $smt->bindParam(":student_id", $studentid, PDO::PARAM_INT);
        $smt->bindParam(":last_modified", $this->last_modified, PDO::PARAM_STR);
        $smt->bindParam(":section_id", $sectionid, PDO::PARAM_INT);
        $smt->bindParam(":faculty_id", $facultyid, PDO::PARAM_INT);
        $smt->bindParam(":status", $this->status, PDO::PARAM_STR);
        $smt->bindParam(":justification", $this->justification, PDO::PARAM_STR);
        $smt->bindParam(":banner", $this->banner, PDO::PARAM_BOOL);
        $smt->bindParam(":reason", $this->reason, PDO::PARAM_STR);
        $smt->bindParam(":explanation", $this->explanation, PDO::PARAM_STR);
        $smt->bindParam(":active", $this->active, PDO::PARAM_BOOL);

        if(!$smt->execute()) return false;

        return true;
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
            return $this->insertDB();
        else
            return $this->updateDB();
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
     * @param Student $student Must exist in DB
     * @param Section $section Must exist in DB
     * @param Faculty $faculty Must exist in DB
     * @param string $status Must match a value from {@link Request::listStatuses()}
     * @param string $reason Must match a value from {@link Request::listOverrideReasons()}
     * @param string $explanation
     * @return Request|null
     */
    public static function build(Student $student, Section $section, Faculty $faculty, string $status, string $reason,
                                 string $explanation) //Need?: OverrideRequest
    {
        $time = gmmktime();
        $now = date("Y-m-d H:i:s", $time);
        if (in_array($status, Request::listStatuses()) && in_array($reason, Request::listReasons()))
            return new Request($student, $section, $now, $faculty, $status, null, false, $reason, $explanation);
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

        $studentid = $student->getId();
        $smt = $pdo->prepare("SELECT * FROM $request_tbl WHERE student_id=:student_id");
        $smt->bindParam(":student_id", $studentid, PDO::PARAM_INT);
        $smt->execute();

        $requestsList = $smt->fetchAll();

        if(!$requestsList) return [];

        $returnList = array();

        foreach ($requestsList as $row)
        {
            $section = Section::getById($row['section_id']);
            $request = new Request($student, $section, $row['last_modified'], Faculty::getById($row['faculty_id']),
            $row['status'], $row['justification'], $row['banner'], $row['reason'], $row['explanation'], $row['active'], $row['id']);
            array_push($returnList, $request);
        }

        return $returnList;
    }

    /**
     * Retrieve a faculty's requests from the database
     * @param Faculty $faculty
     */
    public static function getByFaculty(Faculty $faculty): array
    {
        global $request_tbl;
        $pdo = connectDB();

        $facultyid = $faculty->getId();
        $smt = $pdo->prepare("SELECT * FROM $request_tbl WHERE faculty_id=:faculty_id");
        $smt->bindParam(":faculty_id", $facultyid, PDO::PARAM_INT);
        $smt->execute();

        $requestsList = $smt->fetchAll();

        if(!$requestsList) return [];

        $returnList = array();

        foreach ($requestsList as $row)
        {
            $section = Section::getById($row['section_id']);
            $request = new Request(Student::getById($row['student_id']), $section, $row['last_modified'],
            $faculty, $row['status'], $row['justification'], $row['banner'], $row['reason'], $row['explanation'], $row['active'], $row['id']);
            array_push($returnList, $request);
        }

        return $returnList;
    }

    /**
     * Retrieve a faculty's requests from the database
     * @param Semester $semester
     */
    public static function getInactive(Semester $semester): array
    {
        global $request_tbl, $section_tbl;
        $pdo = connectDB();

        $semesterid = $semester->getId();
        $smt = $pdo->prepare("SELECT * FROM $request_tbl WHERE section_id IN (SELECT id FROM $section_tbl WHERE semester_id=:semester_id) AND active=false");
        $smt->bindParam(":semester_id", $semesterid, PDO::PARAM_INT);
        $smt->execute();

        $requestsList = $smt->fetchAll();

        if(!$requestsList) return [];

        $returnList = array();

        foreach ($requestsList as $row)
        {
            $section = Section::getById($row['section_id']);
            $request = new Request(Student::getById($row['student_id']), $section, $row['last_modified'],
                Faculty::getById($row['faculty_id']), $row['status'], $row['justification'], $row['banner'], $row['reason'],
                $row['explanation'], $row['active'], $row['id']);
            array_push($returnList, $request);
        }

        return $returnList;
    }

    /**
     * Retrieve a request from the database by ID
     * @param int $id
     * @return Request
     */
    public static function getById(int $id): ?Request
    {
        global $request_tbl;
        $pdo = connectDB();

        $smt = $pdo->prepare("SELECT * FROM $request_tbl WHERE id=:request_id LIMIT 1");
        $smt->bindParam(":request_id", $id, PDO::PARAM_INT);
        $smt->execute();

        $data = $smt->fetch(PDO::FETCH_ASSOC);

        if(!$data) return null;

        $student = Student::getById($data['student_id']);
        $section = Section::getById($data['section_id']);
        return new Request($student, $section, $data['last_modified'], Faculty::getById($data['faculty_id']),
        $data['status'], $data['justification'], $data['banner'], $data['reason'], $data['explanation'], $data['active'], $data['id']);
    }

    /**
     * Retrieve all requests from the database
     */
    public static function listActive(): array
    {
        global $request_tbl;
        $pdo = connectDB();

        $smt = $pdo->prepare("SELECT * FROM $request_tbl WHERE active=true");
        $smt->execute();

        $requestsList = $smt->fetchAll();

        if(!$requestsList) return [];

        $returnList = array();
        foreach ($requestsList as $row)
        {
            $student = Student::getById($row['student_id']);
            $section = Section::getById($row['section_id']);
            $request = new Request($student, $section, $row['last_modified'], Faculty::getById($row['faculty_id']),
            $row['status'], $row['justification'], $row['banner'], $row['reason'], $row['explanation'], $row['active'], $row['id']);
            array_push($returnList, $request);
        }

        return $returnList;
    }

    public function getStatusHtml()
    {
        switch ($this->status)
        {
            case 'Received':
                return '<i class="material-icons" style="color:orange">warning</i> Received';
            case 'Approved':
                if($this->banner)
                    return '<i class="material-icons" style="color:green">done_all</i> Approved: In Banner';
                else
                    return '<i class="material-icons" style="color:green">done</i> Approved';
            case 'Provisionally Approved':
                if($this->banner)
                    return '<i class="material-icons" style="color:yellowgreen">done_all</i> Provisionally Approved: In Banner';
                else
                    return '<i class="material-icons" style="color:yellowgreen">done</i> Provisionally Approved';
            case 'Denied':
                return '<i class="material-icons" style="color:red">cancel</i> Denied';
            case 'Requires Faculty Approval':
                return '<i class="material-icons" style="color:orange">warning</i> Requires Faculty Approval';
        }
    }

    public function jsonSerialize()
    {
        $out = get_object_vars($this);

        $out['justification'] = $this->getJustification();
        $out['explanation'] = $this->getExplanation();

        return $out;
    }
}

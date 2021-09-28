<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/helper/PDOWrapper.php';
require_once __DIR__ . '/helper/DAO.php';
require_once __DIR__ . '/helper/DAODeactivatable.php';
require_once __DIR__ . '/helper/DAODeletable.php';
require_once __DIR__ . '/attachments.php';
require_once __DIR__ . '/courses.php';
require_once __DIR__ . '/students.php';
require_once __DIR__ . '/faculty.php';

// Regardless of what the user requests, requests will not be large than this.
const MAX_REQUEST_PAGE_SIZE = 100;

class Request extends DAO implements JsonSerializable, DAODeactivatable, DAODeletable
{
    private $student;
    private $section;
    private $creation_time;
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
     * A timestamp representing the last time the request was created
     * @return string
     */
    public function getCreationTime(): ?string
    {
        return $this->creation_time;
    }

    /**
     * A timestamp representing the last time the request was modified
     * @return string
     */
    public function getLastModified(): ?string
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
        return addslashes(htmlspecialchars($this->justification, ENT_QUOTES));
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
        return addslashes(htmlspecialchars($this->explanation, ENT_QUOTES));
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
     * Deactivate the request, this effectively archives it
     */
    public function setInactive(): void
    {
        $this->active = false;
    }

    private function __construct(Student $student, ?string $creation_time, ?string $last_modified, Section $section,
                                 Faculty $faculty, string $status, ?string $justification, bool $banner, string $reason,
                                 string $explanation, bool $active = true, int $id
                                 = null)
    {
        $this->id = $id;
        $this->student = $student;
        $this->last_modified = $last_modified;
        $this->creation_time = $creation_time;
        $this->section = $section;
        $this->faculty = $faculty;
        $this->status = $status;
        $this->justification = $justification;
        $this->banner = $banner;
        $this->reason = $reason;
        $this->explanation = $explanation;
        $this->active = $active;
    }

    /**
     * @throws DatabaseException
     */
    protected function insert(): void
    {
        global $request_tbl;

        $timestamp = getTimeStamp();

        Logger::info("Request creation time: $timestamp");

        $pdo = PDOWrapper::getConnection();

        $query = "INSERT INTO $request_tbl
        (
            student_id,
            creation_time,
            last_modified,
            section_id,
            faculty_id,
            status,
            justification,
            banner,
            reason,
            explanation,
            active
        )
        VALUES
        (
            :student_id,
            :creation_time,
            :last_modified,
            :section_id,
            :faculty_id,
            :status,
            :justification,
            :banner,
            :reason,
            :explanation,
            :active
        )";

        $smt = $pdo->prepare($query);

        $studentid = $this->student->getId();
        $facultyid = $this->faculty->getId();
        $sectionid = $this->section->getId();
        $smt->bindParam(":student_id", $studentid, PDO::PARAM_INT);
        $smt->bindParam(":creation_time", $timestamp, PDO::PARAM_STR);
        $smt->bindParam(":last_modified", $timestamp, PDO::PARAM_STR);
        $smt->bindParam(":section_id", $sectionid, PDO::PARAM_INT);
        $smt->bindParam(":faculty_id", $facultyid, PDO::PARAM_INT);
        $smt->bindParam(":status", $this->status, PDO::PARAM_STR);
        $smt->bindParam(":justification", $this->justification, PDO::PARAM_STR);
        $smt->bindParam(":banner", $this->banner, PDO::PARAM_BOOL);
        $smt->bindParam(":reason", $this->reason, PDO::PARAM_STR);
        $smt->bindParam(":explanation", $this->explanation, PDO::PARAM_STR);
        $smt->bindParam(":active", $this->active, PDO::PARAM_BOOL);

        $this->id = PDOWrapper::insert($request_tbl, $smt, Logger::obj($this));

        $this->id = $pdo->lastInsertId();
        $this->creation_time = $timestamp;
        $this->last_modified = $timestamp;
    }

    /**
     * @throws DatabaseException
     */
    protected function update(): void
    {
        global $request_tbl;

        $timestamp = getTimeStamp();

        Logger::info("Request modification time: $timestamp");

        $pdo = PDOWrapper::getConnection();
        $query = "UPDATE $request_tbl SET
            student_id=:student_id,
            last_modified=:last_modified,
            section_id=:section_id,
            faculty_id=:faculty_id,
            status=:status,
            justification=:justification,
            banner=:banner,
            reason=:reason,
            explanation=:explanation
        WHERE id=:id";

        $smt = $pdo->prepare($query);

        $studentid = $this->student->getId();
        $facultyid = $this->faculty->getId();
        $sectionid = $this->section->getId();
        $smt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $smt->bindParam(":student_id", $studentid, PDO::PARAM_INT);
        $smt->bindParam(":last_modified", $timestamp, PDO::PARAM_STR);
        $smt->bindParam(":section_id", $sectionid, PDO::PARAM_INT);
        $smt->bindParam(":faculty_id", $facultyid, PDO::PARAM_INT);
        $smt->bindParam(":status", $this->status, PDO::PARAM_STR);
        $smt->bindParam(":justification", $this->justification, PDO::PARAM_STR);
        $smt->bindParam(":banner", $this->banner, PDO::PARAM_BOOL);
        $smt->bindParam(":reason", $this->reason, PDO::PARAM_STR);
        $smt->bindParam(":explanation", $this->explanation, PDO::PARAM_STR);

        PDOWrapper::update($request_tbl, $smt, $this->id, Logger::obj($this));

        $this->last_modified = $timestamp;

        if (!$this->active)
            self::deactivate();
    }

    public function delete(): void
    {
        self::deleteById($this->id);
    }

    public static function deleteByID(int $id): void
    {
        global $request_tbl, $attachment_tbl;
        PDOWrapper::deleteWithChildren($request_tbl, $id, Attachment::class, $attachment_tbl, "request_id");
    }

    public function deactivate(): void
    {
        self::deactivateByID($this->id);
    }

    public static function deactivateByID(int $id): void
    {
        global $request_tbl;
        PDOWrapper::deactivateLeaf($request_tbl, $id);
    }

    /**
     * A list of strings representing all the options for statuses
     * @return array
     */
    public static function listStatuses(): array
    {
        global $request_tbl;
        return getEnums($request_tbl, "status");
    }

    /**
     * A list of strings representing all of the options for request reasons
     * @return array
     */
    public static function listReasons(): array
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
                                 string $explanation): ?Request
    {
        if (in_array($status, Request::listStatuses()) && in_array($reason, Request::listReasons()))
            return new Request($student, null, null, $section, $faculty, $status, null, false, $reason, $explanation);
        else
            return null; //null? error message?
    }

    /**
     * @param bool $active Should the results be active or inactive?
     * @param Student|null $student Filter the results to only those from a specific student. Null means don't care.
     * @param Semester|null $semester Filter the results to only those for a specific semester. Null means don't care.
     * @param Faculty|null $faculty Filter the results to only those assigned to a specific faculty member. Null means don't care.
     * @return array An array containing a list of the requests
     */
    public static function get(bool $active, Student $student = null, Semester $semester = null,
                               Faculty $faculty = null): ?array
    {
        global $request_tbl, $section_tbl;

        // Log all of the arguments to this function
        Logger::info("Retrieving requests from database");
        Logger::info("Adding parameter: active=" . ($active ? "true" : "false"));

        $pdo = PDOWrapper::getConnection();

        $query = "SELECT * FROM $request_tbl WHERE active=:active";

        if (!is_null($semester))
        {
            Logger::info("Adding parameter: semester=" . $semester->getId());
            $query .= " AND section_id IN (SELECT id FROM $section_tbl WHERE semester_id=:semester_id)";
            $semester_id = $semester->getId();
        }

        if (!is_null($student))
        {
            Logger::info("Adding parameter: student=" . $student->getId());
            $query .= " AND student_id=:student_id";
            $student_id = $student->getId();
        }

        if (!is_null($faculty))
        {
            Logger::info("Adding parameter: faculty=" . $faculty->getId());
            $query .= " AND faculty_id=:faculty_id";
            $faculty_id = $faculty->getId();
        }

        $smt = $pdo->prepare($query);
        $smt->bindParam(":active", $active, PDO::PARAM_BOOL);

        if (!is_null($semester))
            $smt->bindParam(":semester_id", $semester_id, PDO::PARAM_INT);

        if (!is_null($student))
            $smt->bindParam(":student_id", $student_id, PDO::PARAM_INT);

        if (!is_null($faculty))
            $smt->bindParam(":faculty_id", $faculty_id, PDO::PARAM_INT);

        if (!$smt->execute())
        {
            Logger::error("Request retrieval failed. Error info: " . $smt->errorInfo());
            Logger::error("Used filter active=" . ($active) ? "true" : "false");

            if (!is_null($semester)) Logger::error("Used filter semester=" . $semester->getId());
            if (!is_null($student)) Logger::error("Used filter student=" . $student->getId());
            if (!is_null($faculty)) Logger::error("Used filter faculty=" . $faculty->getId());

            return null;
        }

        $requestsList = $smt->fetchAll();

        Logger::info("Retrieved request list: " . Logger::obj($requestsList), Verbosity::HIGH);
        Logger::info("Building request objects");

        $returnList = [];

        foreach ($requestsList as $row)
        {
            $student = $student ?? Student::getById($row['student_id']);
            $faculty = $faculty ?? Faculty::getById($row['faculty_id']);
            $request = new Request($student, $row['creation_time'], $row['last_modified'],
                Section::getById($row['section_id']), $faculty,
                $row['status'], $row['justification'], $row['banner'], $row['reason'], $row['explanation'],
                $row['active'], $row['id']);
            $returnList[] = $request;
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
        Logger::info("Retrieving student from database. ID: $id");

        $pdo = PDOWrapper::getConnection();

        $query = "SELECT * FROM $request_tbl WHERE id=:request_id LIMIT 1";
        $smt = $pdo->prepare($query);
        $smt->bindParam(":request_id", $id, PDO::PARAM_INT);

        if (!$smt->execute())
        {
            Logger::error("Could not retrieve request from the database. Error info: " . Logger::obj($smt->errorInfo()));
            Logger::error("Request ID: $id");
            return null;
        }

        $data = $smt->fetch(PDO::FETCH_ASSOC);

        if (!$data)
        {
            Logger::warning("No request with ID $id found");
            return null;
        }

        Logger::info("Retrieved request: " . Logger::obj($data), Verbosity::HIGH);

        $student = Student::getById($data['student_id']);
        $section = Section::getById($data['section_id']);
        return new Request($student, $data['creation_time'], $data['last_modified'], $section,
            Faculty::getById($data['faculty_id']),
            $data['status'], $data['justification'], $data['banner'], $data['reason'], $data['explanation'],
            $data['active'], $data['id']);
    }

    public function getStatusHtml(): ?string
    {
        switch ($this->status)
        {
            case 'Received':
                return '<i class="material-icons" style="color:orange">warning</i> Received';
            case 'Approved':
                if ($this->banner)
                    return '<i class="material-icons" style="color:green">done_all</i> Approved: In Banner';
                else
                    return '<i class="material-icons" style="color:green">done</i> Approved';
            case 'Provisionally Approved':
                if ($this->banner)
                    return '<i class="material-icons" style="color:yellowgreen">done_all</i> Provisionally Approved: In Banner';
                else
                    return '<i class="material-icons" style="color:yellowgreen">done</i> Provisionally Approved';
            case 'Denied':
                return '<i class="material-icons" style="color:red">cancel</i> Denied';
            case 'Requires Faculty Approval':
                return '<i class="material-icons" style="color:orange">warning</i> Requires Faculty Approval';
            default:
                return null;
        }
    }

    public function jsonSerialize()
    {
        $out = get_object_vars($this);

        unset($out['justification']);
        unset($out['explanation']);

        return $out;
    }
}

class RequestPaginator
{
    // Required
    private $semester_filter;
    private $semester;

    // A string representing student information. Name or banner id. Possibly last, first?
    private $student;

    // Misc filters
    private $faculty;
    private $status;
    private $start_range;
    private $end_range;

    // The last request id of the previous page.
    private $last_id;
    private $last_id_filter;

    // The requested maximum number of items to return
    private $size;
    private $sort;

    private $result;

    /**
     * RequestPaginator constructor. These are the only two parameters that are required.
     * @param string $semester_code The six digit code
     */
    public function __construct(string $semester_code)
    {
        $this->size = "LIMIT 50";
        $this->sort = "ASC";
        $this->result = null;

        $this->semester = Semester::getByCode($semester_code);

        if (is_null($this->semester))
        {
            $result = [];
            return;
        }

        global $section_tbl;
        $semester_id = $this->semester->getId();
        $this->semester_filter = "section_id IN (SELECT id FROM $section_tbl WHERE semester_id=$semester_id)";

        $this->student =
        $this->faculty =
        $this->status =
        $this->start_range =
        $this->end_range =
        $this->last_id = null;
    }

    /**
     * Filter the results based on a student search. This string will be "intelligently" matched to the student name or
     * banner id.
     *
     * In the case of a banner id, the banner ID must exactly match the banner id of one of the students. It's assumed
     * that any search that starts with a numerical value will be for a banner id.
     *
     * @param string $student_search
     * @return $this
     */
    public function studentSearch(string $student_search): RequestPaginator
    {
        $this->student = $student_search;
        return $this;
    }

    /**
     * Filter by the given course. Cannot be used when searching for CRN
     * @param string $department
     * @param int $course_num
     * @return $this
     */
    public function course(string $department, int $course_num): RequestPaginator
    {
        $sections = Section::getByCourse($this->semester, Course::get(Department::get($department), $course_num));
        $ids = [];

        foreach ($sections as $section)
            $ids[] = $section->getId();

        if (!sizeof($sections))
            $this->result = [];
        else
            $filter[] = "section_id IN (" . implode(',', $ids) . ")";

        return $this;
    }

    /**
     * Filter by the given course. Cannot be used when searching by dept and course number
     * @param string $crn
     * @return $this
     */
    public function crn(string $crn): RequestPaginator
    {
        $section = Section::getByCrn($this->semester, $crn);

        if (is_null($section))
            $this->result = [];
        else
            $semester = "section_id=" . $section->getId();

        return $this;
    }

    /**
     * @throws DatabaseException
     */
    public function faculty(int $faculty_id): RequestPaginator
    {
        $faculty = Faculty::getById($faculty_id);
        if (is_null($faculty))
            $this->result = [];
        else
            $this->faculty = "faculty_id=" . $faculty->getId();

        return $this;
    }

    public function status(string $status): RequestPaginator
    {
        $this->status = "status=$status";
        return $this;
    }

    public function start(string $start): RequestPaginator
    {
        $this->start_range = "creation_time>$start";
        return $this;
    }

    public function end(string $end): RequestPaginator
    {
        $this->start_range = "creation_time<$end";
        return $this;
    }

    private function constructIdConditional(): ?string
    {
        if (is_null($this->last_id))
            return null;

        if ($this->sort == "DESC")
            return "id>" . $this->last_id;
        else
            return "id<" . $this->last_id;
    }

    public function previousId(string $id): RequestPaginator
    {
        $this->last_id = $id;
        $this->last_id_filter = $this->constructIdConditional();
        return $this;
    }

    public function asc(): RequestPaginator
    {
        $this->sort = "ASC";
        $this->last_id_filter = $this->constructIdConditional();
        return $this;
    }

    public function desc(): RequestPaginator
    {
        $this->sort = "DESC";
        $this->last_id_filter = $this->constructIdConditional();
        return $this;
    }

    public function maxSize(int $size): RequestPaginator
    {
        $max = min($size, MAX_REQUEST_PAGE_SIZE);
        $this->size = "LIMIT $max";
        return $this;
    }

    /**
     * @throws DatabaseException
     */
    public function getPage(): array
    {
        if (!is_null($this->result))
            return $this->result;

        return [];
    }
}

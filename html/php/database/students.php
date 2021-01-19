<?php
include_once 'common.php';
include_once 'courses.php';
include_once 'programs.php';
// TODO: Data validation on constructor and setters

/**
 * This class wraps a student entry in the database
 */
class Student implements JsonSerializable
{
    private $id;
    private $email;
    private $first_name;
    private $last_name;
    private $banner_id;
    private $grad_month;
    private $standing;
    private $majors;
    private $minors;
    private $last_active_sem;

    private function __construct(string $email, string $first_name, string $last_name, string $banner_id,
                                 string $grad_month, string $standing, array $majors, array $minors,
                                 Semester $last_active_sem = null, int $id = null)
    {
        $this->email = $email;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->banner_id = $banner_id;
        $this->grad_month = $grad_month;
        $this->standing = $standing;
        $this->majors = $majors;
        $this->minors = $minors;
        $this->last_active_sem = $last_active_sem;
        $this->id = $id;
    }

    /**
     * The database id (not banner id). Null if it hasn't been stored
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->first_name;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->last_name;
    }

    /**
     * A string representing the 9-digit banner id
     * @return string
     */
    public function getBannerId(): string
    {
        return $this->banner_id;
    }

    /**
     * MM/YYYY format
     * @return string
     */
    public function getGradMonth(): string
    {
        return $this->grad_month;
    }

    /**
     * Checks to see if the last request a student submitted was in an active semester
     * @return Semester|null
     */
    public function isActive(): bool
    {
        if (!$this->last_active_sem)
            return false;
        else
            return $this->last_active_sem->isActive();
    }

    /**
     * A list of strings representing the student's majors
     * @return array
     */
    public function getMajors(): array
    {
        return $this->majors;
    }

    /**
     * A list of strings representing the student's minors
     * @return array
     */
    public function getMinors(): array
    {
        return $this->minors;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    /**
     * @param string $first_name
     */
    public function setFirstName(string $first_name)
    {
        $this->first_name = $first_name;
    }

    /**
     * @param string $last_name
     */
    public function setLastName(string $last_name)
    {
        $this->last_name = $last_name;
    }

    /**
     * @param string $banner_id
     */
    public function setBannerId(string $banner_id)
    {
        $this->banner_id = $banner_id;
    }

    /**
     * @param string $grad_month
     */
    public function setGradMonth(string $grad_month)
    {
        $this->grad_month = $grad_month;
    }

    /**
     * @param string $standing
     */
    public function setStanding(string $standing)
    {
        $this->standing = $standing;
    }

    /**
     * Academic standing. "Freshman", etc.
     * @return string
     */
    public function getStanding(): string
    {
        return $this->standing;
    }

    /**
     * @param Semester|null $last_active_sem
     */
    public function setLastActiveSem(?Semester $last_active_sem): void
    {
        $this->last_active_sem = $last_active_sem;
    }

    /**
     * Each value must match one from {@link Student::listMajors()} and be unique
     * @param array $majors
     */
    public function setMajors(array $majors)
    {
        $this->majors = Major::buildArray($majors);
    }

    /**
     * Each value must match one from {@link Student::listMinors()} and be unique
     * @param array $minors
     */
    public function setMinors(array $minors)
    {
        $this->minors = Minor::buildArray($minors);
    }

    /**
     * An array of strings representing all possible academic standings, "Freshman", etc.
     * @return array
     */
    public static function listStandings(): array
    {
        global $student_tbl;
        return getEnums($student_tbl, "standing");
    }

    // Adds the list of majors to the database
    private function add_majors(array $majors, $pdo)
    {
        global $student_major_tbl, $student_tbl, $major_tbl;
        $major_str = Major::buildListString($majors);
        $smt = $pdo->prepare("INSERT INTO $student_major_tbl (student_id, major_id) SELECT $student_tbl.id, $major_tbl.id FROM $student_tbl INNER JOIN $major_tbl WHERE $student_tbl.email=:email AND $major_tbl.major IN ($major_str)");
        $smt->bindParam(":email", $this->email, PDO::PARAM_STR);
        $smt->execute();
    }

    // Adds the list of minors to the database
    private function add_minors(array $minors, $pdo)
    {
        global $student_minor_tbl, $student_tbl, $minor_tbl;
        $minor_str = Minor::buildListString($minors);
        $smt = $pdo->prepare("INSERT INTO $student_minor_tbl (student_id, minor_id) SELECT $student_tbl.id, $minor_tbl.id FROM $student_tbl INNER JOIN $minor_tbl WHERE $student_tbl.email=:email AND $minor_tbl.minor IN ($minor_str)");
        $smt->bindParam(":email", $this->email, PDO::PARAM_STR);
        $smt->execute();
    }

    // Removes one major from the database
    private function remove_major(string $major, $pdo)
    {
        global $student_major_tbl, $student_tbl, $major_tbl;
        $smt = $pdo->prepare("DELETE $student_major_tbl FROM $student_major_tbl INNER JOIN $student_tbl ON $student_tbl.id=student_id INNER JOIN $major_tbl ON $major_tbl.id=major_id WHERE email=:email AND major=:major");
        $smt->bindParam(":email", $this->email, PDO::PARAM_STR);
        $smt->bindParam(":major", $major, PDO::PARAM_STR);
        $smt->execute();
    }

    // Removes one minor from the database
    private function remove_minor(string $minor, $pdo)
    {
        global $student_minor_tbl, $student_tbl, $minor_tbl;
        $smt = $pdo->prepare("DELETE $student_minor_tbl FROM $student_minor_tbl INNER JOIN $student_tbl ON $student_tbl.id=student_id INNER JOIN $minor_tbl ON $minor_tbl.id=minor_id WHERE email=:email AND minor=:minor");
        $smt->bindParam(":email", $this->email, PDO::PARAM_STR);
        $smt->bindParam(":minor", $minor, PDO::PARAM_STR);
        $smt->execute();
    }

    // If the student is newly created, this will create a new entry in the database
    private function insertDB()
    {
        global $student_tbl, $major_tbl, $minor_tbl, $student_major_tbl, $student_minor_tbl;

        $pdo = connectDB();

        // Insert basic student info
        $smt = $pdo->prepare("INSERT INTO $student_tbl (email, first_name, last_name, banner_id, grad_month, standing, last_active_sem) VALUES (:email, :first_name, :last_name, :banner_id, :grad_month, :standing, :last_active_sem)");
        $last_active_sem_id = $this->last_active_sem ? $this->last_active_sem->getId() : null;
        $smt->bindParam(":email", $this->email, PDO::PARAM_STR);
        $smt->bindParam(":first_name", $this->first_name, PDO::PARAM_STR);
        $smt->bindParam(":last_name", $this->last_name, PDO::PARAM_STR);
        $smt->bindParam(":banner_id", $this->banner_id, PDO::PARAM_STR);
        $smt->bindParam(":grad_month", $this->grad_month, PDO::PARAM_STR);
        $smt->bindParam(":standing", $this->standing, PDO::PARAM_STR);
        $smt->bindParam(":last_active_sem", $last_active_sem_id, PDO::PARAM_INT);

        if(!$smt->execute()) return false;

        // get the newly created ID
        $this->id = $pdo->lastInsertId();

        // Insert information about majors and minors
        $this->add_majors(Major::buildStringList($this->majors), $pdo);
        $this->add_minors(Major::buildStringList($this->minors), $pdo);

        return true;
    }

    // If the student already exists in the database, this will update their entry with the information from this object
    private function updateDB()
    {
        global $student_tbl, $major_tbl, $minor_tbl, $student_major_tbl, $student_minor_tbl;

        $pdo = connectDB();

        // First, update the basic student info
        $last_active_sem_id = $this->last_active_sem ? $this->last_active_sem->getId() : null;
        $smt = $pdo->prepare("UPDATE $student_tbl SET email=:email, first_name=:first_name, last_name=:last_name, banner_id=:banner_id, grad_month=:grad_month, standing=:standing, last_active_sem=:last_active_sem WHERE id=:id");
        $smt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $smt->bindParam(":email", $this->email, PDO::PARAM_STR);
        $smt->bindParam(":first_name", $this->first_name, PDO::PARAM_STR);
        $smt->bindParam(":last_name", $this->last_name, PDO::PARAM_STR);
        $smt->bindParam(":banner_id", $this->banner_id, PDO::PARAM_STR);
        $smt->bindParam(":grad_month", $this->grad_month, PDO::PARAM_STR);
        $smt->bindParam(":standing", $this->standing, PDO::PARAM_STR);
        $smt->bindParam(":last_active_sem", $last_active_sem_id, PDO::PARAM_INT);

        if(!$smt->execute()) return false;

        // Next, get the majors currently stored in the database
        $smt = $pdo->prepare("SELECT major FROM $student_tbl INNER JOIN $student_major_tbl ON $student_tbl.id = $student_major_tbl.student_id  INNER JOIN $major_tbl ON $student_major_tbl.major_id = $major_tbl.id WHERE $student_tbl.email = :email");
        $smt->bindParam(":email", $this->email, PDO::PARAM_STR);
        $smt->execute();
        $current_majors = flattenResult($smt->fetchAll(PDO::FETCH_NUM));
        $old_majors = Major::buildStringList($this->majors);

        $smt = $pdo->prepare("SELECT minor FROM $student_tbl INNER JOIN $student_minor_tbl ON $student_tbl.id = $student_minor_tbl.student_id  INNER JOIN $minor_tbl ON $student_minor_tbl.minor_id = $minor_tbl.id WHERE $student_tbl.email = :email");
        $smt->bindParam(":email", $this->email, PDO::PARAM_STR);
        $smt->execute();
        $current_minors = flattenResult($smt->fetchAll(PDO::FETCH_NUM));
        $old_minors = Minor::buildStringList($this->minors);

        // Add all the majors that aren't in the database to the database
        $majors_to_add = [];
        foreach ($old_majors as $major)
            if (!in_array($major, $current_majors)) array_push($majors_to_add, $major);
        $this->add_majors($majors_to_add, $pdo);

        $minors_to_add = [];
        foreach ($old_minors as $minor)
            if (!in_array($minor, $current_minors)) array_push($minors_to_add, $minor);
        $this->add_minors($minors_to_add, $pdo);

        // If a major is in the database, but is no longer a major, remove it
        foreach ($current_majors as $major)
            if (!in_array($major, $old_majors)) $this->remove_major($major, $pdo);

        foreach ($current_minors as $minor)
            if (!in_array($minor, $old_minors)) $this->remove_minor($minor, $pdo);

        return true;
    }

    /**
     * Stores the current object in the database. If the object is newly created,
     * a new entry into the DB is made. If the student has been stored in the DB,
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
     * Constructs a new student locally
     * @param string $email
     * @param string $first_name
     * @param string $last_name
     * @param string $banner_id
     * @param string $grad_month
     * @param string $standing
     * @param array $majors
     * @param array $minors
     * @return Student An object that only exists locally, isn't stored in DB
     */
    public static function build(string $email, string $first_name, string $last_name, string $banner_id,
                                 string $grad_month, string $standing, array $majors, array $minors)
    {
        $major_arr = Major::buildArray($majors);
        $minor_arr = Minor::buildArray($minors);

        if(is_null($major_arr) or is_null($minor_arr)) return null;

        return new Student($email, $first_name, $last_name, $banner_id,
            $grad_month, $standing, $major_arr, $minor_arr);
    }

    // Given the student information row from the DB, this function completes the student object
    private static function loadStudent(array $data, $pdo): Student
    {
        global $student_tbl, $major_tbl, $minor_tbl, $student_major_tbl, $student_minor_tbl;

        // First, query for the majors
        $smt = $pdo->prepare("SELECT major FROM $student_tbl INNER JOIN $student_major_tbl ON $student_tbl.id = $student_major_tbl.student_id  INNER JOIN $major_tbl ON $student_major_tbl.major_id = $major_tbl.id WHERE $student_tbl.id = :id");
        $smt->bindParam(":id", $data['id'], PDO::PARAM_INT);
        $smt->execute();

        // Place off the the rows in a single array
        $majors = flattenResult($smt->fetchAll(PDO::FETCH_NUM));

        // Then, query for the minors
        $smt = $pdo->prepare("SELECT minor FROM $student_tbl INNER JOIN $student_minor_tbl ON $student_tbl.id = $student_minor_tbl.student_id  INNER JOIN $minor_tbl ON $student_minor_tbl.minor_id = $minor_tbl.id WHERE $student_tbl.id = :id");
        $smt->bindParam(":id", $data['id'], PDO::PARAM_INT);
        $smt->execute();

        $minors = flattenResult($smt->fetchAll(PDO::FETCH_NUM));

        // Build the student and return the object
        return new Student($data['email'], $data['first_name'], $data['last_name'], $data['banner_id'],
            $data['grad_month'], $data['standing'], Major::buildArray($majors), Major::buildArray($minors),
            $data['last_active_sem'] ? Semester::getById($data['last_active_sem']) : null, $data['id']);
    }

    /**
     * Retrieve a student from the database given an email, null if it can't be found
     * @param string $email
     * @return Student|null
     */
    public static function get(string $email): ?Student
    {
        global $student_tbl, $major_tbl, $minor_tbl, $student_major_tbl, $student_minor_tbl;
        $pdo = connectDB();

        $smt = $pdo->prepare("SELECT * FROM $student_tbl WHERE email=:email LIMIT 1");
        $smt->bindParam(":email", $email, PDO::PARAM_STR);
        $smt->execute();
        $data = $smt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return Student::loadStudent($data, $pdo);
    }

    /**
     * Retrieves a student form the database given the database id (not banner id), null if it can't be found
     * @param int $id
     * @return Student|null
     */
    public static function getById(int $id): ?Student
    {
        global $student_tbl, $major_tbl, $minor_tbl, $student_major_tbl, $student_minor_tbl;
        $pdo = connectDB();

        $smt = $pdo->prepare("SELECT * FROM $student_tbl WHERE id=:id LIMIT 1");
        $smt->bindParam(":id", $id, PDO::PARAM_INT);
        $smt->execute();
        $data = $smt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return Student::loadStudent($data, $pdo);
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}

<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/helper/PDOWrapper.php';
require_once __DIR__ . '/helper/DatabaseException.php';
require_once __DIR__ . '/helper/DAO.php';
require_once __DIR__ . '/helper/DAODeletable.php';

/**
 * A class that stores a faculty member, keeps track of name and email
 */
class Faculty extends DAO implements JsonSerializable, DAODeletable
{
    private $email;
    private $first_name;
    private $last_name;
    private $default;

    /**
     * The database id. Null if it hasn't been stored
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
     * @return bool Is this the default faculty member? New requests are assigned to them
     */
    public function isDefault(): bool
    {
        return $this->default;
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
     * Make this faculty the default
     */
    public function setDefault()
    {
        $this->default = true;
    }

    private function __construct(string $email, string $first_name, string $last_name, bool $default = null, int $id = null)
    {
        $this->email = $email;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->default = $default;
        $this->id = $id;
    }

    public static function list(): array
    {
        global $faculty_tbl;
        $pdo = PDOWrapper::getConnection();

        $smt = $pdo->query("SELECT * FROM $faculty_tbl");

        $data = $smt->fetchAll(PDO::FETCH_ASSOC);

        if (!$data) return [];

        $out = [];

        foreach ($data as $row)
            $out[] = new Faculty($row['email'], $row['first_name'], $row['last_name'], $row['is_default'], $row['id']);

        return $out;
    }

    // Ensures exactly one default
    private function makeDefault(): bool
    {
        global $faculty_tbl, $request_tbl;

        $currentDefaultFacultyId = self::getDefault()->getId();

        $pdo = PDOWrapper::getConnection();

        Logger::info("Unsetting current default.");
        if ($pdo->exec("UPDATE $faculty_tbl SET is_default=false WHERE is_default=true") === false)
            throw new DatabaseException("Could not unset current default faculty.", 500, $pdo->errorInfo());

        Logger::info("Setting new default.");
        $smt = $pdo->prepare("UPDATE $faculty_tbl SET is_default=true WHERE id=:id");
        $smt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if(!$smt->execute())
            throw new DatabaseException("Could not set faculty as default.", 500, $smt->errorInfo());

        Logger::info("Moving requests");
        $smt = $pdo->prepare("UPDATE $request_tbl SET faculty_id=:id WHERE faculty_id=$currentDefaultFacultyId AND active=true");
        $smt->bindParam(":id", $this->id, PDO::PARAM_INT);
        if(!$smt->execute())
            throw new DatabaseException("Could not reassign requests.", 500, $pdo->errorInfo());

        return true;
    }

    protected function insert(): void
    {
        global $faculty_tbl;

        $pdo = PDOWrapper::getConnection();
        $query = "INSERT INTO $faculty_tbl
        (
            email,
            first_name,
            last_name
        )
        VALUES
        (
            :email,
            :first_name,
            :last_name
        )";

        $smt = $pdo->prepare($query);
        $smt->bindParam(":email", $this->email, PDO::PARAM_STR);
        $smt->bindParam(":first_name", $this->first_name, PDO::PARAM_STR);
        $smt->bindParam(":last_name", $this->last_name, PDO::PARAM_STR);

        $this->id = PDOWrapper::insert($faculty_tbl, $smt, Logger::obj($this));

        Logger::info("Finding default faculty...");
        $smt = $pdo->prepare("SELECT id FROM $faculty_tbl WHERE is_default=true");

        if (!$smt->execute())
        {
            Logger::error("Could not retrieve faculty to determine default. Error info: " . Logger::obj($smt->errorInfo()), Verbosity::LOW, true);
            throw new DatabaseException("A database error occurred while creating the faculty", 500, $smt->errorInfo());
        }

        // If there is no default, or the user indicated this should be the new default
        if ($smt->rowCount() === 0 || $this->default)
        {
            Logger::info("No default faculty found, setting new faculty to default.");
            $this->makeDefault();
        }
        else
        {
            Logger::info("Default faculty already exists. Skipping.");
        }
    }

    protected function update(): void
    {
        global $faculty_tbl;
        Logger::info("Writing updated faculty to database: " . Logger::obj($this));

        $pdo = PDOWrapper::getConnection();
        $query = "UPDATE $faculty_tbl SET
            email=:email,
            first_name=:first_name,
            last_name=:last_name
        WHERE id=:id";

        $smt = $pdo->prepare($query);
        $smt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $smt->bindParam(":email", $this->email, PDO::PARAM_STR);
        $smt->bindParam(":first_name", $this->first_name, PDO::PARAM_STR);
        $smt->bindParam(":last_name", $this->last_name, PDO::PARAM_STR);

        PDOWrapper::update($faculty_tbl, $smt, $this->id, Logger::obj($this));

        if ($this->default)
            $this->makeDefault();
    }

    /**
     * Delete the current element from the database. This is NOT reversible (unlike setting to inactive)
     * @throws DatabaseException
     */
    public function delete(): void
    {
        self::deleteByID($this->id);
    }

    /**
     * Faculty deletion is a unique case. We have to first ensure the faculty isn't default, then move all their request
     * to the default faculty.
     * @param int $id The id of the element to be deleted
     * @throws DatabaseException
     */
    public static function deleteByID(int $id): void
    {
        global $faculty_tbl, $request_tbl;

        $pdo = PDOWrapper::getConnection();

        $smt = $pdo->query("SELECT is_default FROM $faculty_tbl WHERE id=$id");

        // If they're the default faculty
        if ($smt->fetchColumn())
            throw new DatabaseException("Cannot delete default faculty", 400, $smt->errorInfo());

        $defaultFacultyId = Faculty::getDefault()->getId();

        // Move their requests to the default faculty
        if ($pdo->exec("UPDATE $request_tbl SET faculty_id=$defaultFacultyId WHERE faculty_id=$id") === false)
            throw new DatabaseException("Could not reassign requests.", 500, $pdo->errorInfo());

        PDOWrapper::deleteLeaf($faculty_tbl, $id);
    }


    /**
     * Create a local object representing a faculty member
     * @param string $email
     * @param string $first_name
     * @param string $last_name
     * @param bool $default Note: If this is the first faculty member added, they will be default regardless of this setting
     * @return Faculty An object that only exists locally, isn't stored in DB
     */
    public static function build(string $email, string $first_name, string $last_name, bool $default = false): Faculty
    {
        return new Faculty($email, $first_name, $last_name, $default);
    }

    /**
     * Retrieve a faculty member given their email, null if not found
     * @param string $email
     * @return Faculty|null
     */
    public static function get(string $email): ?Faculty
    {
        global $faculty_tbl;
        Logger::info("Retrieving faculty from database.");

        $pdo = PDOWrapper::getConnection();
        $query = "SELECT * FROM $faculty_tbl WHERE email=:email LIMIT 1";
        $smt = $pdo->prepare($query);
        $smt->bindParam(":email", $email, PDO::PARAM_STR);

        if (!$smt->execute())
        {
            Logger::error("Faculty retrieval failed. Error info: " . Logger::obj($smt->errorInfo()));
            Logger::error("Faculty email: $email");
            return null;
        }

        $data = $smt->fetch(PDO::FETCH_ASSOC);

        if (!$data)
        {
            Logger::warning("No email found with email $email@truman.edu");
            return null;
        }

        return new Faculty($email, $data['first_name'], $data['last_name'], $data['is_default'], $data['id']);
    }

    /**
     * Retrieve a faculty member by their database id, null if not found
     * @param int $id
     * @return Faculty|null
     * @throws DatabaseException
     */
    public static function getById(int $id): ?Faculty
    {
        global $faculty_tbl;

        $pdo = PDOWrapper::getConnection();
        $smt = $pdo->prepare("SELECT * FROM $faculty_tbl WHERE id=:id LIMIT 1");
        $smt->bindParam(":id", $id, PDO::PARAM_INT);

        if (!$smt->execute())
        {
            Logger::error("Faculty retrieval failed. Error info: " . Logger::obj($smt->errorInfo()));
            Logger::error("Faulty ID: $id");
            throw new DatabaseException("Could not retrieve faculty.", 500, $smt->errorInfo());
        }

        $data = $smt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Faculty($data['email'], $data['first_name'], $data['last_name'], $data['is_default'], $id);
    }

    public static function getDefault(): ?Faculty
    {
        global $faculty_tbl;
        $pdo = PDOWrapper::getConnection();

        $smt = $pdo->query("SELECT * FROM $faculty_tbl WHERE is_default=true LIMIT 1");

        $data = $smt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Faculty($data['email'], $data['first_name'], $data['last_name'], $data['is_default'], $data['id']);
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}

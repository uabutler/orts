<?php
require_once __DIR__ . '/common.php';

/**
 * A class that stores a faculty member, keeps track of name and email
 */
class Faculty implements JsonSerializable
{
    private $id;
    private $email;
    private $first_name;
    private $last_name;
    private $default;
    private $error_info;

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

    public function errorInfo(): ?array
    {
        return $this->error_info;
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
        $pdo = connectDB();

        $smt = $pdo->query("SELECT * FROM $faculty_tbl");

        $data = $smt->fetchAll(PDO::FETCH_ASSOC);

        if (!$data) return [];

        $out = [];

        foreach ($data as $row)
            $out[] = new Faculty($row['email'], $row['first_name'], $row['last_name'], $row['is_default'], $row['id']);

        return $out;
    }

    // Ensures exactly one default
    private function makeDefault($pdo): bool
    {
        global $faculty_tbl;

        $smt = $pdo->exec("UPDATE $faculty_tbl SET is_default=false WHERE is_default=true");

        $smt = $pdo->prepare("UPDATE $faculty_tbl SET is_default=true WHERE id=:id");
        $smt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if(!$smt->execute())
        {
            $this->error_info = $smt->errorInfo();
            return false;
        }

        return true;
    }

    private function insertDB(): bool
    {
        global $faculty_tbl;
        Logger::info("Writing new faculty to database: " . Logger::obj($this));

        $pdo = connectDB();
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

        if (!$smt->execute())
        {
            $this->error_info = $smt->errorInfo();

            if ($this->error_info[0] === "23000")
            {
                Logger::warning("Faculty already exists. Error info: " . Logger::obj($this->error_info));
                Logger::warning("Faculty details: " . Logger::obj($this));
            }
            else
            {
                Logger::error("Faculty insertion failed. Error info: " . Logger::obj($this->error_info));
                Logger::error("Faculty details: " . Logger::obj($this));
            }

            return false;
        }

        Logger::info("Faculty insertion complete.");
        $this->id = $pdo->lastInsertId();
        Logger::info("Created faculty with id " . $this->getId(), Verbosity::LOW);

        Logger::info("Finding default faculty...");
        $smt = $pdo->prepare("SELECT id FROM $faculty_tbl WHERE is_default=true");

        if (!$smt->execute())
        {
            $this->error_info = $smt->errorInfo();
            Logger::error("Could not retrieve faculty to determine default. Error info: " . Logger::obj($this->error_info), Verbosity::LOW, true);
            return false;
        }

        // If there is no default, or the user indicated this should be the new default
        if ($smt->rowCount() === 0 || $this->default)
        {
            Logger::info("No default faculty found, setting new faculty to default.");
            if (!$this->makeDefault($pdo)) return false;
        }
        else
        {
            Logger::info("Default faculty already exists. Skipping.");
        }

        return true;
    }

    private function updateDB(): bool
    {
        global $faculty_tbl;
        Logger::info("Writing updated faculty to database: " . Logger::obj($this));

        $pdo = connectDB();
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

        if (!$smt->execute())
        {
            $this->error_info = $smt->errorInfo();

            if ($this->error_info[0] === "23000")
            {
                Logger::warning("Faculty already exists. Error info: " . Logger::obj($this->error_info));
                Logger::warning("Faculty details: " . Logger::obj($this));
            }
            else
            {
                Logger::error("Faculty update failed. Error info: " . Logger::obj($this->error_info));
                Logger::error("Faculty details: " . Logger::obj($this));
            }

            return false;
        }

        if ($this->default)
            if (!$this->makeDefault($pdo)) return false;

        return true;
    }

    /**
     * Stores the current object in the database. If the object is newly created,
     * a new entry into the DB is made. If the student has been stored in the DB,
     * we update the existing entry
     */
    public function storeInDB(): bool
    {
        // The id is set only when the student is already in the database
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
        global $faculty_tbl;
        Logger::info("Deleting faculty from database. ID: $id");

        if (is_null($pdo)) $pdo = connectDB();

        return deleteByIdFrom($faculty_tbl, $id, $pdo);
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

        $pdo = connectDB();
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
     */
    public static function getById(int $id): ?Faculty
    {
        global $faculty_tbl;

        $pdo = connectDB();
        $smt = $pdo->prepare("SELECT * FROM $faculty_tbl WHERE id=:id LIMIT 1");
        $smt->bindParam(":id", $id, PDO::PARAM_INT);

        if (!$smt->execute())
        {
            Logger::error("Faculty retrieval failed. Error info: " . Logger::obj($smt->errorInfo()));
            Logger::error("Faulty ID: $id");
            return null;
        }

        $data = $smt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Faculty($data['email'], $data['first_name'], $data['last_name'], $data['is_default'], $id);
    }

    public static function getDefault(): ?Faculty
    {
        global $faculty_tbl;
        $pdo = connectDB();

        $smt = $pdo->query("SELECT * FROM $faculty_tbl WHERE is_default=true LIMIT 1");

        $data = $smt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Faculty($data['email'], $data['first_name'], $data['last_name'], $data['is_default'], $data['id']);
    }

    public function jsonSerialize()
    {
        $out = get_object_vars($this);
        unset($out['error_info']);
        return $out;
    }
}

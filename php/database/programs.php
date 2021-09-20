<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/helper/DAO.php';
require_once __DIR__ . '/helper/DAODeletable.php';
require_once __DIR__ . '/helper/DAODeactivatable.php';

abstract class Program extends DAO implements DAODeletable, DAODeactivatable
{
    protected $id;
    protected $name;
    protected $active;

    protected function __construct(string $name, bool $active = true, int $id = null)
    {
        $this->name = $name;
        $this->id = $id;
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
     * The name of the major. E.g., "Computer Science"
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Can students use this as one of their majors?
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * The name of the major. E.g., "Computer Science"
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setInactive(): void
    {
        $this->active = false;
    }

    protected static function listActiveHelper(string $table, string $col): array
    {
        $pdo = PDOWrapper::getConnection();
        $smt = $pdo->prepare("SELECT $col FROM $table WHERE active=true");
        $smt->execute();
        return flattenResult($smt->fetchAll(PDO::FETCH_NUM));
    }

    protected static function listAllHelper(string $table, string $col): array
    {
        $pdo = PDOWrapper::getConnection();

        $smt = $pdo->query("SELECT * FROM $table");

        $data = $smt->fetchAll(PDO::FETCH_ASSOC);

        if (!$data) return [];

        $out = [];

        foreach ($data as $row)
            $out[] = new Major($row[$col], $row['active'], $row['id']);

        return $out;
    }

    public static function buildStringList(array $arr): array
    {
        $get_name = function (Program $program) { return $program->getName(); };
        return array_map($get_name, $arr);
    }

    public static function buildListString(array $arr): string
    {
        if (empty($arr)) return "";
        return implode(', ', preg_filter('/^/', "'", preg_filter('/$/', "'", $arr)));
    }
}

class Major extends Program implements JsonSerializable
{
    // Create a new entry in the database
    protected function insert(): void
    {
        global $major_tbl;

        $pdo = PDOWrapper::getConnection();

        $query = "INSERT INTO $major_tbl (major, active) VALUES (:major, :active)";
        $smt = $pdo->prepare($query);
        $smt->bindParam(":major", $this->name, PDO::PARAM_STR);
        $smt->bindParam(":active", $this->active, PDO::PARAM_BOOL);

        $this->id = PDOWrapper::insert($major_tbl, $smt, Logger::obj($this));
    }

    // If the student already exists in the database, this will update their entry with the information from this object
    protected function update(): void
    {
        global $major_tbl;

        $pdo = PDOWrapper::getConnection();

        $smt = $pdo->prepare("UPDATE $major_tbl SET major=:major WHERE id=:id");
        $smt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $smt->bindParam(":major", $this->name, PDO::PARAM_STR);

        PDOWrapper::update($major_tbl, $smt, $this->id, Logger::obj($this));

        if (!$this->active)
            self::deactivate();
    }

    /**
     * Delete the current element from the database. This is NOT reversible (unlike setting to inactive)
     * @throws DatabaseException
     */
    public function delete(): void
    {
        self::deleteByID($this->id);
    }

    public static function deleteByID(int $id): void
    {
        global $major_tbl, $student_major_tbl;
        $pdo = PDOWrapper::getConnection();

        // We can't use the PDO wrapper here since this table doesn't have a class representing it
        $smt = $pdo->prepare("DELETE FROM $student_major_tbl WHERE major_id=:id");
        $smt->bindParam(":id", $id, PDO::PARAM_INT);

        if (!$smt->execute())
            throw new DatabaseException("Unable to dissociate major from some students. Major could not be deleted.", 500, $smt->errorInfo());

        PDOWrapper::deleteLeaf($major_tbl, $id);
    }

    public function deactivate(): void
    {
        self::deactivateByID($this->id);
    }

    public static function deactivateByID(int $id): void
    {
        global $major_tbl;
        PDOWrapper::deleteLeaf($major_tbl, $id);
    }

    public static function listActive(): array
    {
        global $major_tbl;
        return self::listActiveHelper($major_tbl, "major");
    }

    public static function list(): array
    {
        global $major_tbl;
        return self::listAllHelper($major_tbl, "major");
    }

    /**
     * Create a local object representing a major member
     * @param string $name
     * @return Major An object that only exists locally, isn't stored in DB
     */
    public static function build(string $name): Major
    {
        return new Major($name);
    }

    /**
     * Return an array of Majors given an array of the major names. Returns null if one of the majors can't be found.
     * @param array $names
     * @return array|null
     */
    public static function buildArray(array $names): ?array
    {
        $out = [];

        foreach ($names as $name)
        {
            $major = Major::get($name);
            if (is_null($major)) return null;
            $out[] = $major;
        }

        return $out;
    }

    /**
     * Retrieve a major given its name, null if not found
     * @param string $name
     * @return Major|null
     */
    public static function get(string $name): ?Major
    {
        global $major_tbl;
        $pdo = PDOWrapper::getConnection();

        $smt = $pdo->prepare("SELECT * FROM $major_tbl WHERE major=:name LIMIT 1");
        $smt->bindParam(":name", $name, PDO::PARAM_STR);
        $smt->execute();

        $data = $smt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Major($name, $data['active'], $data['id']);
    }

    /**
     * Retrieve a faculty member by their database id, null if not found
     * @param int $id
     * @return Faculty|null
     */
    public static function getById(int $id): ?Major
    {
        global $major_tbl;
        $pdo = PDOWrapper::getConnection();

        $smt = $pdo->prepare("SELECT * FROM $major_tbl WHERE id=:id LIMIT 1");
        $smt->bindParam(":id", $id, PDO::PARAM_INT);
        $smt->execute();

        $data = $smt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Major($data['major'], $data['active'], $id);
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}

class Minor extends Program implements JsonSerializable
{
    // Create a new entry in the database
    protected function insert(): void
    {
        global $minor_tbl;

        $pdo = PDOWrapper::getConnection();

        // Insert basic student info
        $smt = $pdo->prepare("INSERT INTO $minor_tbl (minor, active) VALUES (:minor, :active)");
        $smt->bindParam(":minor", $this->name, PDO::PARAM_STR);
        $smt->bindParam(":active", $this->active, PDO::PARAM_BOOL);
        $smt->execute();

        $this->id = PDOWrapper::insert($minor_tbl, $smt, Logger::obj($this));
    }

    protected function update(): void
    {
        global $minor_tbl;

        $pdo = PDOWrapper::getConnection();

        // First, update the basic student info
        $smt = $pdo->prepare("UPDATE $minor_tbl SET minor=:minor WHERE id=:id");
        $smt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $smt->bindParam(":minor", $this->name, PDO::PARAM_STR);

        PDOWrapper::update($minor_tbl, $smt, $this->id, Logger::obj($this));

        if (!$this->active)
            self::deactivate();
    }

    public function delete(): void
    {
        self::deleteByID($this->id);
    }

    public static function deleteByID(int $id): void
    {
        global $minor_tbl, $student_minor_tbl;
        $pdo = PDOWrapper::getConnection();

        // We can't use the PDO wrapper here since this table doesn't have a class representing it from which we can call delete
        $smt = $pdo->prepare("DELETE FROM $student_minor_tbl WHERE minor_id=:id");
        $smt->bindParam(":id", $id, PDO::PARAM_INT);

        if (!$smt->execute())
            throw new DatabaseException("Unable to dissociate minor from some students. Minor could not be deleted.", 500, $smt->errorInfo());

        PDOWrapper::deleteLeaf($minor_tbl, $id);
    }

    public function deactivate(): void
    {
        self::deactivateByID($this->id);
    }

    public static function deactivateByID(int $id): void
    {
        global $minor_tbl;
        PDOWrapper::deactivateLeaf($minor_tbl, $id);
    }

    public static function listActive(): array
    {
        global $minor_tbl;
        return self::listActiveHelper($minor_tbl, "minor");
    }

    public static function list(): array
    {
        global $minor_tbl;
        return self::listAllHelper($minor_tbl, "minor");
    }

    /**
     * Create a local object representing a minor member
     * @param string $name
     * @return Minor An object that only exists locally, isn't stored in DB
     */
    public static function build(string $name): Minor
    {
        return new Minor($name);
    }

    /**
     * Return an array of Minors given an array of the minor names. Returns null if one of the minors can't be found.
     * @param array $names
     * @return array|null
     */
    public static function buildArray(array $names): ?array
    {
        $out = [];

        foreach ($names as $name)
        {
            $minor = Minor::get($name);
            if (is_null($minor)) return null;
            $out[] = $minor;
        }

        return $out;
    }

    /**
     * Retrieve a minor given its name, null if not found
     * @param string $name
     * @return Minor|null
     */
    public static function get(string $name): ?Minor
    {
        global $minor_tbl;
        $pdo = PDOWrapper::getConnection();

        $smt = $pdo->prepare("SELECT * FROM $minor_tbl WHERE minor=:name LIMIT 1");
        $smt->bindParam(":name", $name, PDO::PARAM_STR);
        $smt->execute();

        $data = $smt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Minor($name, $data['active'], $data['id']);
    }

    /**
     * Retrieve a faculty member by their database id, null if not found
     * @param int $id
     * @return Faculty|null
     */
    public static function getById(int $id): ?Minor
    {
        global $minor_tbl;
        $pdo = PDOWrapper::getConnection();

        $smt = $pdo->prepare("SELECT * FROM $minor_tbl WHERE id=:id LIMIT 1");
        $smt->bindParam(":id", $id, PDO::PARAM_INT);
        $smt->execute();

        $data = $smt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Minor($data['minor'], $data['active'], $id);
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}

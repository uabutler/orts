<?php

class Program
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

    /**
     * Sets the current major to active
     */
    public function setActive(): void
    {
        $this->active = true;
    }

    public function setInactive(): void
    {
        $this->active = false;
    }

    public static function buildStringList(array $arr): array
    {
        $get_name = function (Program $program) { return $program->getName(); };
        return array_map($get_name, $arr);
    }

    public static function buildListString(array $arr): string
    {
        return implode(', ', preg_filter('/^/', "'", preg_filter('/$/', "'", $arr)));
    }
}

class Major extends Program
{
    // Create a new entry in the database
    private function insertDB()
    {
        global $major_tbl;

        $pdo = connectDB();

        // Insert basic student info
        $smt = $pdo->prepare("INSERT INTO $major_tbl (major, active) VALUES (:major, :active)");
        $smt->bindParam(":major", $this->name, PDO::PARAM_STR);
        $smt->bindParam(":active", $this->active, PDO::PARAM_BOOL);
        $smt->execute();

        // get the newly created ID
        $this->id = $pdo->lastInsertId();
    }

    // If the student already exists in the database, this will update their entry with the information from this object
    private function updateDB()
    {
        global $major_tbl;

        $pdo = connectDB();

        // First, update the basic student info
        $smt = $pdo->prepare("UPDATE $major_tbl SET major=:major active=:active WHERE id=:id");
        $smt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $smt->bindParam(":major", $this->name, PDO::PARAM_STR);
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
        // The id is set only when the student is already in the database
        if (is_null($this->id))
            $this->insertDB();
        else
            $this->updateDB();
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
            array_push($out, $major);
        }

        return $out;
    }

    /**
     * An array of strings representing all possible majors
     * @return array
     */
    public static function list(): array
    {
        global $major_tbl;
        $pdo = connectDB();
        $smt = $pdo->query("SELECT major FROM $major_tbl");
        return flattenResult($smt->fetchAll(PDO::FETCH_NUM));
    }

    /**
     * Retrieve a major given its name, null if not found
     * @param string $name
     * @return Major|null
     */
    public static function get(string $name): ?Major
    {
        global $major_tbl;
        $pdo = connectDB();

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
        $pdo = connectDB();

        $smt = $pdo->prepare("SELECT * FROM $major_tbl WHERE id=:id LIMIT 1");
        $smt->bindParam(":id", $id, PDO::PARAM_INT);
        $smt->execute();

        $data = $smt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Major($data['major'], $data['active'], $id);
    }
}

class Minor extends Program
{
    // Create a new entry in the database
    private function insertDB()
    {
        global $minor_tbl;

        $pdo = connectDB();

        // Insert basic student info
        $smt = $pdo->prepare("INSERT INTO $minor_tbl (minor, active) VALUES (:minor, :active)");
        $smt->bindParam(":minor", $this->name, PDO::PARAM_STR);
        $smt->bindParam(":active", $this->active, PDO::PARAM_BOOL);
        $smt->execute();

        // get the newly created ID
        $this->id = $pdo->lastInsertId();
    }

    // If the student already exists in the database, this will update their entry with the information from this object
    private function updateDB()
    {
        global $minor_tbl;

        $pdo = connectDB();

        // First, update the basic student info
        $smt = $pdo->prepare("UPDATE $minor_tbl SET minor=:minor active=:active WHERE id=:id");
        $smt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $smt->bindParam(":minor", $this->name, PDO::PARAM_STR);
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
        // The id is set only when the student is already in the database
        if (is_null($this->id))
            $this->insertDB();
        else
            $this->updateDB();
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
            array_push($out, $minor);
        }

        return $out;
    }

    /**
     * An array of strings representing all possible minors
     * @return array
     */
    public static function list(): array
    {
        global $minor_tbl;
        $pdo = connectDB();
        $smt = $pdo->query("SELECT minor FROM $minor_tbl");
        return flattenResult($smt->fetchAll(PDO::FETCH_NUM));
    }

    /**
     * Retrieve a minor given its name, null if not found
     * @param string $name
     * @return Minor|null
     */
    public static function get(string $name): ?Minor
    {
        global $minor_tbl;
        $pdo = connectDB();

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
        $pdo = connectDB();

        $smt = $pdo->prepare("SELECT * FROM $minor_tbl WHERE id=:id LIMIT 1");
        $smt->bindParam(":id", $id, PDO::PARAM_INT);
        $smt->execute();

        $data = $smt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Minor($data['minor'], $data['active'], $id);
    }
}

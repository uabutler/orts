<?php
include_once 'common_db.php';

/**
 * A class that stores a faculty member, keeps track of name and email
 */
class Faculty implements JsonSerializable
{
    private $id;
    private $email;
    private $first_name;
    private $last_name;

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

    private function __construct(string $email, string $first_name, string $last_name, int $id = null)
    {
        $this->email = $email;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->id = $id;
    }

    private function insertDB()
    {
        global $faculty_tbl;
        $pdo = connectDB();

        $smt = $pdo->prepare("INSERT INTO $faculty_tbl (email, first_name, last_name) VALUES (:email, :first_name, :last_name)");
        $smt->bindParam(":email", $this->email, PDO::PARAM_STR);
        $smt->bindParam(":first_name", $this->first_name, PDO::PARAM_STR);
        $smt->bindParam(":last_name", $this->last_name, PDO::PARAM_STR);
        $smt->execute();

        $smt = $pdo->prepare("SELECT id FROM $faculty_tbl WHERE email=:email");
        $smt->bindParam(":email", $this->email, PDO::PARAM_STR);
        $smt->execute();
        $this->id = $smt->fetch(PDO::FETCH_ASSOC)['id'];
    }

    private function updateDB()
    {
        global $department_tbl;
        $pdo = connectDB();

        $smt = $pdo->prepare("UPDATE $department_tbl SET email=:email, first_name=:first_name, last_name=:last_name WHERE id=:id");
        $smt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $smt->bindParam(":email", $this->email, PDO::PARAM_STR);
        $smt->bindParam(":first_name", $this->first_name, PDO::PARAM_STR);
        $smt->bindParam(":last_name", $this->last_name, PDO::PARAM_STR);
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
        if (is_null($this->id))
            $this->insertDB();
        else
            $this->updateDB();
    }

    /**
     * Create a local object representing a faculty member
     * @param string $email
     * @param string $first_name
     * @param string $last_name
     * @return Faculty An object that only exists locally, isn't stored in DB
     */
    public static function build(string $email, string $first_name, string $last_name): Faculty
    {
        return new Faculty($email, $first_name, $last_name);
    }

    /**
     * Retrieve a faculty member given their email, null if not found
     * @param string $email
     * @return Faculty|null
     */
    public static function get(string $email): ?Faculty
    {
        global $faculty_tbl;
        $pdo = connectDB();

        $smt = $pdo->prepare("SELECT * FROM $faculty_tbl WHERE email=:email LIMIT 1");
        $smt->bindParam(":email", $email, PDO::PARAM_STR);
        $smt->execute();

        $data = $smt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Faculty($email, $data['first_name'], $data['last_name'], $data['id']);
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
        $smt->execute();

        $data = $smt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Faculty($data['email'], $data['first_name'], $data['last_name'], $id);
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}

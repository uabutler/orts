<?php
include_once 'common_db.php';

class Faculty
{
  private $id;
  private $email;
  private $first_name;
  private $last_name;

  public function getId() { return $this->id; }
  public function getEmail() { return $this->email; }
  public function getFirstName() { return $this->first_name; }
  public function getLastName() { return $this->last_name; }

  /**
   * Setters
   */
  public function setEmail(string $email)
  {
    $this->email = $email;
  }

  public function setFirstName(string $first_name)
  {
    $this->first_name = $first_name;
  }

  public function setLastName(string $last_name)
  {
    $this->last_name = $last_name;
  }

  private function __construct(string $email, string $first_name, string $last_name, int $id=null)
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
    if(is_null($this->id))
      $this->insertDB();
    else
      $this->updateDB();
  }

  public static function buildFaculty(string $email, string $first_name, string $last_name): Facluty
  {
    return new Faculty($email, $first_name, $last_name);
  }

  public static function getFaculty(string $email): ?Faculty
  {
    global $faculty_tbl;
    $pdo = connectDB();

    $smt = $pdo->prepare("SELECT * FROM $faculty_tbl WHERE email=:email LIMIT 1");
    $smt->bindParam(":email", $email, PDO::PARAM_STR);
    $smt->execute();

    $data = $smt->fetch(PDO::FETCH_ASSOC);

    if(!$data) return null;

    return new Faculty($email, $data['first_name'], $data['last_name'], $data['id']);
  }

  public static function getFacultyById(int $id): ?Department
  {
    global $faculty_tbl;
    $pdo = connectDB();

    $smt = $pdo->prepare("SELECT * FROM $faculty_tbl WHERE id=:id LIMIT 1");
    $smt->bindParam(":id", $id, PDO::PARAM_INT);
    $smt->execute();

    $data = $smt->fetch(PDO::FETCH_ASSOC);

    if(!$data) return null;

    return new Faculty($data['email'], $data['first_name'], $data['last_name'], $id);
  }
}

?>

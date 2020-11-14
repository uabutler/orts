<?php
include_once 'common_db.php';
// TODO: Data validation on constructor and setters
// TODO: Error-handling

/**
 * This class wraps a student entry in the database
 */
class Student
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

  private function __construct(string $email, string $first_name, string $last_name, string $banner_id,
    string $grad_month, string $standing, array $majors, array $minors, int $id=null)
  {
    $this->email = $email;
    $this->first_name = $first_name;
    $this->last_name = $last_name;
    $this->banner_id = $banner_id;
    $this->grad_month = $grad_month;
    $this->majors = $majors;
    $this->minors = $minors;
    $this->standing = $standing;
    $this->id = $id;
  }

  /**
   * Getters
   */
  public function getId() { return $this->id; }
  public function getEmail() { return $this->email; }
  public function getFirstName() { return $this->first_name; }
  public function getLastName() { return $this->last_name; }
  public function getBannerId() { return $this->banner_id; }
  public function getGradMonth() { return $this->grad_month; }
  public function getStanding() { return $this->standing; }
  public function getMajors() { return $this->majors; }
  public function getMinors() { return $this->minors; }

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

  public function setBannerId(string $banner_id)
  {
    $this->banner_id = $banner_id;
  }

  public function setGradMonth(string $grad_month)
  {
    $this->grad_month = $grad_month;
  }

  public function setStanding(string $standing)
  {
    $this->standing = $standing;
  }

  public function setMajors(array $majors)
  {
    $this->majors = $majors;
  }

  public function setMinors(array $minors)
  {
    $this->minors = $minors;
  }

  // List all possible majors
  public static function listMajors(): array
  {
    global $major_tbl;
    $pdo = connectDB();
    $smt = $pdo->query("SELECT major FROM $major_tbl");
    return flattenResult($smt->fetchAll(PDO::FETCH_NUM));
  }

  // List all possible minors
  public static function listMinors(): array
  {
    global $minor_tbl;
    $pdo = connectDB();
    $smt = $pdo->query("SELECT minor FROM $minor_tbl");
    return flattenResult($smt->fetchAll(PDO::FETCH_NUM));
  }

  // List all possible standings
  public static function listStandings(): array
  {
    global $student_tbl;
    return getEnums($student_tbl, "standing");
  }

  // Adds the list of majors to the database
  private function add_majors(array $majors, $pdo)
  {
    global $student_major_tbl, $student_tbl, $major_tbl;
    $major_str = arrayToDbList($majors);
    $smt = $pdo->prepare("INSERT INTO $student_major_tbl (student_id, major_id) SELECT $student_tbl.id, $major_tbl.id FROM $student_tbl INNER JOIN $major_tbl WHERE $student_tbl.email=:email AND $major_tbl.major IN ($major_str)");
    $smt->bindParam(":email", $this->email, PDO::PARAM_STR);
    $smt->execute();
  }

  // Adds the list of minors to the database
  private function add_minors(array $minors, $pdo)
  {
    global $student_minor_tbl, $student_tbl, $minor_tbl;
    $minor_str = arrayToDbList($minors);
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
    $smt = $pdo->prepare("INSERT INTO $student_tbl (email, first_name, last_name, banner_id, grad_month, standing) VALUES (:email, :first_name, :last_name, :banner_id, :grad_month, :standing)");

    $smt->bindParam(":email", $this->email, PDO::PARAM_STR);
    $smt->bindParam(":first_name", $this->first_name, PDO::PARAM_STR);
    $smt->bindParam(":last_name", $this->last_name, PDO::PARAM_STR);
    $smt->bindParam(":banner_id", $this->banner_id, PDO::PARAM_STR);
    $smt->bindParam(":grad_month", $this->grad_month, PDO::PARAM_STR);
    $smt->bindParam(":standing", $this->standing, PDO::PARAM_STR); 
    $smt->execute();

    // get the newly created ID
    $smt = $pdo->prepare("SELECT id FROM $student_tbl WHERE email=:email");
    $smt->bindParam(":email", $this->email, PDO::PARAM_STR);
    $smt->execute();
    $this->id = $smt->fetch(PDO::FETCH_ASSOC)['id'];

    // Insert information about majors and minors
    add_majors($this->majors, $pdo);
    add_minors($this->minors, $pdo);
  }

  // If the student already exists in the database, this will update their entry with the information from this object
  private function updateDB()
  {
    global $student_tbl, $major_tbl, $minor_tbl, $student_major_tbl, $student_minor_tbl;

    $pdo = connectDB();

    // First, update the basic student info
    $smt = $pdo->prepare("UPDATE $student_tbl SET email=:email, first_name=:first_name, last_name=:last_name, banner_id=:banner_id, grad_month=:grad_month, standing=:standing WHERE id=:id");

    $smt->bindParam(":email", $this->email, PDO::PARAM_STR);
    $smt->bindParam(":first_name", $this->first_name, PDO::PARAM_STR);
    $smt->bindParam(":last_name", $this->last_name, PDO::PARAM_STR);
    $smt->bindParam(":banner_id", $this->banner_id, PDO::PARAM_STR);
    $smt->bindParam(":grad_month", $this->grad_month, PDO::PARAM_STR);
    $smt->bindParam(":standing", $this->standing, PDO::PARAM_STR);
    $smt->bindParam(":id", $this->id, PDO::PARAM_INT);

    $smt->execute();

    // Next, get the majors currently stored in the database
    $smt = $pdo->prepare("SELECT major FROM $student_tbl INNER JOIN $student_major_tbl ON $student_tbl.id = $student_major_tbl.student_id  INNER JOIN $major_tbl ON $student_major_tbl.major_id = $major_tbl.id WHERE $student_tbl.email = :email");
    $smt->bindParam(":email", $this->email, PDO::PARAM_STR);
    $smt->execute();
    $current_majors = flattenResult($smt->fetchAll(PDO::FETCH_NUM));

    $smt = $pdo->prepare("SELECT minor FROM $student_tbl INNER JOIN $student_minor_tbl ON $student_tbl.id = $student_minor_tbl.student_id  INNER JOIN $minor_tbl ON $student_minor_tbl.minor_id = $minor_tbl.id WHERE $student_tbl.email = :email");
    $smt->bindParam(":email", $this->email, PDO::PARAM_STR);
    $smt->execute();
    $current_minors = flattenResult($smt->fetchAll(PDO::FETCH_NUM));

    // Add all the majors that aren't in the database to the database
    $majors_to_add = [];
    foreach($this->majors as $major)
      if(!in_array($major, $current_majors)) array_push($majors_to_add, $major);
    $this->add_majors($majors_to_add, $pdo);

    $minors_to_add = [];
    foreach($this->minors as $minor)
      if(!in_array($minor, $current_minors)) array_push($minors_to_add, $minor);
    $this->add_minors($minors_to_add, $pdo);

    // If a major is in the database, but is no longer a major, remove it
    foreach($current_majors as $major)
      if(!in_array($major, $this->majors)) $this->remove_major($major, $pdo);

    foreach($current_minors as $minor)
      if(!in_array($minor, $this->minors)) $this->remove_minor($minor, $pdo);
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

  /**
   * Constructs a new student locally
   */
  public static function buildStudent(string $email, string $first_name, string $last_name, string $banner_id,
    string $grad_month, string $standing, array $majors, array $minors)
  {
    return new Student($email, $first_name, $last_name, $banner_id,
      $grad_month, $standing, $majors, $minors);
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
    $out = new Student($data['email'], $data['first_name'], $data['last_name'], $data['banner_id'], $data['grad_month'], $data['standing'], $majors, $minors, $data['id']);

    return $out;
  }

  /**
   * Retrieve a student from the database
   */
  public static function getStudent(string $email): ?Student
  {
    global $student_tbl, $major_tbl, $minor_tbl, $student_major_tbl, $student_minor_tbl;
    $pdo = connectDB();

    $smt = $pdo->prepare("SELECT * FROM $student_tbl WHERE email=:email LIMIT 1");
    $smt->bindParam(":email", $email, PDO::PARAM_STR);
    $smt->execute();
    $data = $smt->fetch(PDO::FETCH_ASSOC);

    if(!$data) return null;

    return Student::loadStudent($data, $pdo);
  }

  public static function getStudentById(int $id): ?Student
  {
    global $student_tbl, $major_tbl, $minor_tbl, $student_major_tbl, $student_minor_tbl;
    $pdo = connectDB();

    $smt = $pdo->prepare("SELECT * FROM $student_tbl WHERE id=:id LIMIT 1");
    $smt->bindParam(":id", $id, PDO::PARAM_INT);
    $smt->execute();
    $data = $smt->fetch(PDO::FETCH_ASSOC);

    if(!$data) return null;

    return Student::loadStudent($data, $pdo);
  }
}

?>

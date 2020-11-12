<?php
include_once 'common_db.php';

/**
 * These are objects to wrap the data stored in the databases
 */
class Student
{
  public $id;
  public $email;
  public $first_name;
  public $last_name;
  public $banner_id;
  public $grad_month;
  public $majors;
  public $minors;

  private function __construct(string $email, string $first_name, string $last_name, string $banner_id,
    string $grad_month, array $majors, array $minors, int $id=null)
  {
    $this->email = $email;
    $this->first_name = $first_name;
    $this->last_name = $last_name;
    $this->banner_id = $banner_id;
    $this->grad_month = $grad_month;
    $this->majors = $majors;
    $this->minors = $minors;
  }

  public function getId() { return $this->id; }
  public function getEmail() { return $this->email; }
  public function getFirstName() { return $this->first_name; }
  public function getLastName() { return $this->last_name; }
  public function getBannerId() { return $this->banner_id; }
  public function getGradMonth() { return $this->grad_month; }
  public function getMajors() { return $this->majors; }
  public function getMinors() { return $this->minors; }

  public function storeInDB()
  {
    global $student_tbl, $major_tbl, $minor_tbl, $student_major_tbl, $student_minor_tbl;

    $pdo = connectDB();

    // Insert basic student info
    $smt = $pdo->prepare("INSERT INTO $student_tbl (email, first_name, last_name, banner_id, grad_month) VALUES (:email, :first_name, :last_name, :banner_id, :grad_month)");

    $smt->bindParam(":email", $this->email, PDO::PARAM_STR);
    $smt->bindParam(":first_name", $this->first_name, PDO::PARAM_STR);
    $smt->bindParam(":last_name", $this->last_name, PDO::PARAM_STR);
    $smt->bindParam(":banner_id", $this->banner_id, PDO::PARAM_STR);
    $smt->bindParam(":grad_month", $this->grad_month, PDO::PARAM_STR);

    $smt->execute();

    // The two arrays need to be converted to strings because PDO doesn't like arrays
    $majors = implode(', ', preg_filter('/^/', "'", preg_filter('/$/', "'", $this->majors)));
    $minors = implode(', ', preg_filter('/^/', "'", preg_filter('/$/', "'", $this->minors)));

    // Insert majors
    $smt = $pdo->prepare("INSERT INTO $student_major_tbl (student_id, major_id) SELECT $student_tbl.id, $major_tbl.id FROM $student_tbl INNER JOIN $major_tbl WHERE $student_tbl.email=:email AND $major_tbl.major IN ($majors)");
    $smt->bindParam(":email", $this->email, PDO::PARAM_STR);
    $smt->execute();

    // And minors
    $smt = $pdo->prepare("INSERT INTO $student_minor_tbl (student_id, minor_id) SELECT $student_tbl.id, $minor_tbl.id FROM $student_tbl INNER JOIN $minor_tbl ON $student_tbl.email=:email AND $minor_tbl.minor IN ($minors)");
    $smt->bindParam(":email", $this->email, PDO::PARAM_STR);
    $smt->execute();
  }

  /**
   * Constructs a new student locally
   */
  public static function buildStudent(string $email, string $first_name, string $last_name, string $banner_id,
    string $grad_month, array $majors, array $minors)
  {
    return new Student($email, $first_name, $last_name, $banner_id,
      $grad_month, $majors, $minors);
  }

  /**
   * Retrieve a student from the database
   */
  public static function getStudent(string $email) : Student
  {
    global $student_tbl, $major_tbl, $minor_tbl, $student_major_tbl, $student_minor_tbl;

    $pdo = connectDB();

    $smt = $pdo->prepare("SELECT * FROM $student_tbl WHERE email=:email LIMIT 1");
    $smt->bindParam(":email", $email, PDO::PARAM_STR);
    $smt->execute();

    $data = $smt->fetch(PDO::FETCH_ASSOC);
    
    $smt = $pdo->prepare("SELECT major FROM $student_tbl INNER JOIN $student_major_tbl ON $student_tbl.id = $student_major_tbl.student_id  INNER JOIN $major_tbl ON $student_major_tbl.major_id = $major_tbl.id WHERE $student_tbl.email = :email");
    $smt->bindParam(":email", $email, PDO::PARAM_STR);
    $smt->execute();

    $majors = flattenResult($smt->fetchAll(PDO::FETCH_NUM));

    $smt = $pdo->prepare("SELECT minor FROM $student_tbl INNER JOIN $student_minor_tbl ON $student_tbl.id = $student_minor_tbl.student_id  INNER JOIN $minor_tbl ON $student_minor_tbl.minor_id = $minor_tbl.id WHERE $student_tbl.email = :email");
    $smt->bindParam(":email", $email, PDO::PARAM_STR);
    $smt->execute();

    $minors = flattenResult($smt->fetchAll(PDO::FETCH_NUM));

    $out = new Student($data['email'], $data['first_name'], $data['last_name'], $data['banner_id'], $data['grad_month'], $majors, $minors, $data['id']);

    return $out;
  }
}

?>

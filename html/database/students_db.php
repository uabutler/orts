<?php
include 'common_db.php';

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

  function __construct(string $email, string $first_name, string $last_name, string $banner_id,
    string $grad_month, array $majors, array $minors)
  {
    $this->email = $email;
    $this->first_name = $first_name;
    $this->last_name = $last_name;
    $this->banner_id = $banner_id;
    $this->grad_month = $grad_month;
    $this->majors = $majors;
    $this->minors = $minors;
  }
}

/**
 * Add a student to the database.
 */
function addStudent(Student $student)
{
  global $student_tbl, $major_tbl, $minor_tbl, $student_major_tbl, $student_minor_tbl;

  $pdo = connectDB();

  // Insert basic student info
  $smt = $pdo->prepare("INSERT INTO $student_tbl (email, first_name, last_name, banner_id, grad_month) VALUES (:email, :first_name, :last_name, :banner_id, :grad_month)");

  $smt->bindParam(":email", $student->email, PDO::PARAM_STR);
  $smt->bindParam(":first_name", $student->first_name, PDO::PARAM_STR);
  $smt->bindParam(":last_name", $student->last_name, PDO::PARAM_STR);
  $smt->bindParam(":banner_id", $student->banner_id, PDO::PARAM_STR);
  $smt->bindParam(":grad_month", $student->grad_month, PDO::PARAM_STR);

  $smt->execute();

  // The two arrays need to be converted to strings because PDO doesn't like arrays
  $majors = implode(', ', preg_filter('/^/', "'", preg_filter('/$/', "'", $student->majors)));
  $minors = implode(', ', preg_filter('/^/', "'", preg_filter('/$/', "'", $student->minors)));

  // Insert majors
  $smt = $pdo->prepare("INSERT INTO $student_major_tbl (student_id, major_id) SELECT $student_tbl.id, $major_tbl.id FROM $student_tbl INNER JOIN $major_tbl WHERE $student_tbl.email=:email AND $major_tbl.major IN ($majors)");
  $smt->bindParam(":email", $student->email, PDO::PARAM_STR);
  $smt->execute();

  // And minors
  $smt = $pdo->prepare("INSERT INTO $student_minor_tbl (student_id, minor_id) SELECT $student_tbl.id, $minor_tbl.id FROM $student_tbl INNER JOIN $minor_tbl ON $student_tbl.email=:email AND $minor_tbl.minor IN ($minors)");
  $smt->bindParam(":email", $student->email, PDO::PARAM_STR);
  $smt->execute();
}

function getStudent(string $email) : Student
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

  $out = new Student($data['email'], $data['first_name'], $data['last_name'], $data['banner_id'], $data['grad_month'], $majors, $minors);
  $out->id = $data['id'];

  return $out;
}

?>

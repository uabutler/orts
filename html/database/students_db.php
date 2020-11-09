<?php
include 'common_db.php';

class Student
{
  public $email;
  public $first_name;
  public $last_name;
  public $banner_id;
  public $grad_month;

  function __construct($email, $first_name, $last_name, $banner_id, $grad_month)
  {
    $this->email = $email;
    $this->first_name = $first_name;
    $this->last_name = $last_name;
    $this->banner_id = $banner_id;
    $this->grad_month = $grad_month;
  }
}

/**
 * Add a student to the database
 */
function addStudent(Student $student)
{
  global $student_tbl;

  $pdo = connectDB();

  $smt = $pdo->prepare("INSERT INTO $student_tbl (email, first_name, last_name, banner_id, grad_month) VALUES (:email, :first_name, :last_name, :banner_id, :grad_month)");

  $smt->bindParam(":email", $student->email, PDO::PARAM_STR);
  $smt->bindParam(":first_name", $student->first_name, PDO::PARAM_STR);
  $smt->bindParam(":last_name", $student->last_name, PDO::PARAM_STR);
  $smt->bindParam(":banner_id", $student->banner_id, PDO::PARAM_STR);
  $smt->bindParam(":grad_month", $student->grad_month, PDO::PARAM_STR);

  $smt->execute();
}

function printTbl()
{
  $pdo = connectDB();

  print_r($pdo->query("SELECT * FROM test_tbl")->fetch());
}

?>

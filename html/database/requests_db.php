<?php
include_once 'common_db.php';
include_once 'courses_db.php';
include_once 'students_db.php';

class OverrideRequest
{
  public $student;
  public $section;
  public $last_modified;
  public $status;
  public $reason;
  public $explanation;
  
  function __construct(Student $student, Section $section, string $last_modified, string $status, string $reason, string $explanation)
  {
    $this->student = $student;
    $this->section = $section;
    $this->last_modified = $last_modified;
    $this->status = $status;
    $this->reason = $reason;
    $this->explanation = $explanation;
  }
  
  /*
  function __construct(array $data)
  {
   $this->student = new Student($data);
   $this->section = new Section($data);
   $this->last_modified = $last_modified;
   $this->status = $status;
   $this->reason = $reason;
   $this->explanation = $explanation;
  } 
  */
  
  public function getStudent()      { return $this->student; }
  public function getSection()      { return $this->section; }
  public function getLastModified() { return $this->last_modified; }
  public function getStatus()       { return $this->status; }
  public function getReason()         { return $this->reason; }
  public function getExplanation()  { return $this->explanation; }

  public function storeInDB()
  {
    global $request_tbl;
    
    $pdo = connectDB();
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //Shows SQL errors
    $smt = $pdo->prepare("INSERT INTO $request_tbl (student_id, last_modified, section_id, status, reason, explanation) VALUES (:student_id, :last_modified, :section_id, :status, :reason, :explanation)");
    
    $qwe = OverrideRequest::getStudent()->getId();
    $smt->bindParam(":student_id", $qwe, PDO::PARAM_INT); //Does -> -> work?
    $smt->bindParam(":last_modified", $this->last_modified, PDO::PARAM_STR);
    $smt->bindParam(":section_id", $qwe, PDO::PARAM_INT);
    $smt->bindParam(":status", $this->status, PDO::PARAM_STR);
    $smt->bindParam(":reason", $this->reason, PDO::PARAM_STR);
    $smt->bindParam(":explanation", $this->explaination, PDO::PARAM_STR);
  
    $smt->execute();
  }
  
  public static function listStatuses()
  {
    global $request_tbl;
    return getEnums($request_tbl, "status");
  }

  public static function listOverrideReasons()
  {
    global $request_tbl;
    return getEnums($request_tbl, "reason");
  }
  
  /**
   * Constructs a new request locally
   */
  public static function buildRequest(Student $student, Section $section, string $status, string $reason, string $explanation) //Need?: OverrideRequest
  {
    $time = gmmktime();
    $now = date("Y-m-d H:i:s", $time);
    if(in_array($status, OverrideRequest::listStatuses()) && in_array($reason, OverrideRequest::listOverrideReasons()))
      return new OverrideRequest($student, $section, $now, $status, $reason, $explanation);
    else
      return null; //null? error message?
  }
  
  /**
   * Retrieve a students requests from the database
   */ 
  public static function getStudentRequests(Student $student) : array
  {
    global $request_tbl;
  	$pdo = connectDB();
   
	  $smt = $pdo->prepare("SELECT * FROM $request_tbl");   
	  $smt->execute();   
   
    $requestsList = $smt->fetchAll();
    $returnList = array();

  	foreach ($requestsList as $row)
    {
      $section = getSectionById($row['section_id']);
      $request = new OverrideRequest($student, $section, $row['last_modified'], $row['status'], $row['reason'], $row['explanation']);
      array_push($returnList, $request);
    }
    
    return $returnList;
  }
  
  /**
   * Retrieve a request from the database by ID
   */ 
  public static function getOverrideRequest(int $id) : OverrideRequest
  {
  	global $request_tbl;
  	$pdo = connectDB();
    
    $smt = $pdo->prepare("SELECT * FROM $request_tbl WHERE id=:request_id LIMIT 1"); 
    $smt->bindParam(":request_id", $id, PDO::PARAM_INT);  
	  $smt->execute();  
	    
    $data = $smt->fetch(PDO::FETCH_ASSOC);
   
    $student = getStudentById($data['student_id']);
    $section = getSectionById($data['section_id']);
	  return new OverrideRequest($student, $section, $row['last_modified'], $row['status'], $row['reason'], $row['explanation']);
  }
  
  /**
   * Retrieve all requests from the database
   */ 
  public static function getOverrideRequests() : OverrideRequest
  {
  	global $request_tbl;
    $pdo = connectDB();
   
    $smt = $pdo->prepare("SELECT * FROM $request_tbl");   
    $smt->execute();   
   
    $requestsList = $smt->fetchAll();
    $returnList = array();
    foreach ($requestsList as $row)
    {
      $student = getStudentById($row['student_id']);
      $section = getSectionById($row['section_id']);
      $request = new OverrideRequest($student, $section, $row['last_modified'], $row['status'], $row['reason'], $row['explanation']);
      array_push($returnList, $request);
    }
    
    return $returnList;
  }
  
}

//TESTING
$me = Student::getStudent('mmk9999');
$sec = Section::getSectionById(1);
$ob = OverrideRequest::buildRequest($me, $sec, OverrideRequest::listStatuses()[1], OverrideRequest::listOverrideReasons()[0], 'testexplanation');
$ob->storeInDB();
?>

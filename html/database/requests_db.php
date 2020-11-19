<?php
include_once 'common_db.php';
include_once 'courses_db.php';
include_once 'students_db.php';

class OverrideRequest
{
  private $id;
  private $student;
  private $section;
  private $last_modified;
  private $status;
  private $reason;
  private $explanation;
  
  public function getId()           { return $this->id; }
  public function getStudent()      { return $this->student; }
  public function getSection()      { return $this->section; }
  public function getLastModified() { return $this->last_modified; }
  public function getStatus()       { return $this->status; }
  public function getReason()       { return $this->reason; }
  public function getExplanation()  { return $this->explanation; }
  
  /**
   * Setters
   */
  public function setStudent(Student $student)
  {
    $this->student = $student;
  }

  public function setSection(Section $section)
  {
    $this->section = $section;
  }

  public function setLastModified(string $last_modified)
  {
	$this->last_modified = $last_modified;
  }

  public function setStatus(string $status)
  {
	$this->status = $status;
  }

  public function setReason(string $reason)
  {
	$this->reason = $reason;
  }

  public function setExplanation(string $explanation)
  {
	$this->explanation = $explanation;
  }

  
  private function __construct(Student $student, Section $section, string $last_modified, string $status, string $reason, string $explanation, int $id=null)
  {
    $this->id = $id;
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

  private function insertDB()
  {
    global $request_tbl;
    
    $pdo = connectDB();
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //Shows SQL errors
    $smt = $pdo->prepare("INSERT INTO $request_tbl (student_id, last_modified, section_id, status, reason, explanation) VALUES (:student_id, :last_modified, :section_id, :status, :reason, :explanation)");
    
    $studentid = OverrideRequest::getStudent()->getId();
    $sectionid = OverrideRequest::getSection()->getId();
    $smt->bindParam(":student_id", $studentid, PDO::PARAM_INT);
    $smt->bindParam(":last_modified", $this->last_modified, PDO::PARAM_STR);
    $smt->bindParam(":section_id", $sectionid, PDO::PARAM_INT);
    $smt->bindParam(":status", $this->status, PDO::PARAM_STR);
    $smt->bindParam(":reason", $this->reason, PDO::PARAM_STR);
    $smt->bindParam(":explanation", $this->explanation, PDO::PARAM_STR);
     
    $smt->execute();

    $this->id = $pdo->lastInsertId();
  }
  
  private function updateDB()
  {
    global $request_tbl;
    
    $pdo = connectDB();
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //Shows SQL errors
    $smt = $pdo->prepare("UPDATE $request_tbl SET student_id=:student_id, last_modified=:last_modified, section_id=:section_id, status=:status, reason=:reason, explanation=:explanation WHERE id=:id");
    
    $studentid = OverrideRequest::getStudent()->getId();
    $sectionid = OverrideRequest::getSection()->getId();
    $smt->bindParam(":id", $this->id, PDO::PARAM_INT);
    $smt->bindParam(":student_id", $studentid, PDO::PARAM_INT);
    $smt->bindParam(":last_modified", $this->last_modified, PDO::PARAM_STR);
    $smt->bindParam(":section_id", $sectionid, PDO::PARAM_INT);
    $smt->bindParam(":status", $this->status, PDO::PARAM_STR);
    $smt->bindParam(":reason", $this->reason, PDO::PARAM_STR);
    $smt->bindParam(":explanation", $this->explanation, PDO::PARAM_STR); 
    $smt->execute();
  }
  
  /**
   * Stores the current object in the database. If the object is newly created,
   * a new entry into the DB is made. If the request has been stored in the DB,
   * we update the existing entry
   */
  public function storeInDB()
  {
    // The id is set only when the student is already in the database
    if(is_null($this->id))
      $this->insertDB();
    else
      $this->updateDB();
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
      $section = Section::getSectionById($row['section_id']);
      $request = new OverrideRequest($student, $section, $row['last_modified'], $row['status'], $row['reason'], $row['explanation'], $row['id']);
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
   
    $student = Student::getStudentById($data['student_id']);
    $section = Section::getSectionById($data['section_id']);
    return new OverrideRequest($student, $section, $data['last_modified'], $data['status'], $data['reason'], $data['explanation'], $data['id']);
  }
  
  /**
   * Retrieve all requests from the database
   */ 
  public static function getOverrideRequests() : array
  {
  	global $request_tbl;
    $pdo = connectDB();
   
    $smt = $pdo->prepare("SELECT * FROM $request_tbl");   
    $smt->execute();   
   
    $requestsList = $smt->fetchAll();
    $returnList = array();
    foreach ($requestsList as $row)
    {
      $student = Student::getStudentById($row['student_id']);
      $section = Section::getSectionById($row['section_id']);
      $request = new OverrideRequest($student, $section, $row['last_modified'], $row['status'], $row['reason'], $row['explanation'], $row['id']);
      array_push($returnList, $request);
    }
    
    return $returnList;
  }
  
}

?>

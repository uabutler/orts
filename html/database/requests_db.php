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
  public $type;
  public $explanation;
  
  function __construct(Student $student, Section $section, string $last_modified, string $status, string $type, string $explanation)
  {
    $this->student = $student;
    $this->section = $section;
    $this->last_modified = $last_modified;
    $this->status = $status;
    $this->type = $type;
    $this->explanation = $explanation;
  }
  
  function __construct(array $data)
  {
  	$this->student = new Student($data);
  	$this->section = new Section($data);
  	$this->last_modified = $last_modified;
   $this->status = $status;
   $this->type = $type;
   $this->explanation = $explanation;
  } 
  
  public function getStudent()      { return $this->student; }
  public function getSection()      { return $this->section; }
  public function getLastModified() { return $this->last_modified; }
  public function getStatus()       { return $this->status; }
  public function getType()         { return $this->type; }
  public function getExplanation()  { return $this->explanation; }

  public function storeInDB()
  {
    global $request_tbl;
    
    $pdo = connectDB();
    
    //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //Shows SQL errors
    $smt = $pdo->prepare("INSERT INTO $request_tbl (student_id, last_modified, section_id, status, type, explanation) VALUES (:student_id, :last_modified, :section_id, :status, :type, :explanation)");
    
    $smt->bindParam(":student_id", $this->student->id, PDO::PARAM_INT); //Does -> -> work?
    $smt->bindParam(":last_modified", $this->last_modified, PDO::PARAM_STR);
    $smt->bindParam(":section_id", $this->section->id, PDO::PARAM_INT);
    $smt->bindParam(":status", $this->status, PDO::PARAM_STR);
    $smt->bindParam(":type", $this->type, PDO::PARAM_STR);
    $smt->bindParam(":explanation", $this->explanation, PDO::PARAM_STR);
  
    $smt->execute();
  }
  
  public static function listStatuses()
  {
    global $request_tbl;
    return getEnums($request_tbl, "status");
  }

  public static function listOverrideTypes()
  {
    global $request_tbl;
    return getEnums($request_tbl, "type");
  }
  
  /**
   * Constructs a new request locally
   */
  public static function buildRequest(Student $student, Section $section, string $status, string type, string $explanation) //Need?: OverrideRequest
  {
	$time = gmmktime();
    $now = date("Y-m-d H:i:s", $time);
    //VALIDATE ENUMS HERE?
    return new OverrideRequest($student, $section, $now, $status, $type, $explanation);
  }
  
  /**
   * Retrieve a students requests from the database
   */ 
  public static function getStudentRequests(Student $student) : array
  {
	 global $request_tbl;
    $pdo = connectDB();

    $smt = $pdo->prepare("SELECT * FROM $student_table INNER JOIN $request_tbl ON $request_tbl.student_id = $student_tbl.id INNER JOIN $section_tbl ON $request_tbl.section_id = $request_tbl.id WHERE student_id=:student_id");
    $smt->bindParam(":student_id", $student->$id, PDO::PARAM_INT);
    $smt->execute();
    
    $requestsList = $smt->fetchAll();
    $returnList = array();
    foreach ($requestsList as $row){
		$request = new OverrideRequest($row);
		array_push($returnList, $request);
    }
    
    //$out = new OverrideRequest($data['email'], $data['first_name'], $data['last_name'], $data['banner_id'], $data['grad_month'], $majors, $minors, $data['id']);
    return $returnList;
  }
  
  /**
   * Retrieve a request from the database by ID
   */ 
  public static function getOverrideRequest(int $id) : OverrideRequest
  {
  	global $request_tbl;
  	$pdo = connectDB();
  	
  	 $smt = $pdo->prepare("SELECT * FROM $student_table INNER JOIN $request_tbl ON $request_tbl.student_id = $student_tbl.id INNER JOIN $section_tbl ON $request_tbl.section_id = $request_tbl.id WHERE section_id=:section_id");
    $smt->bindParam(":section_id", $section->$id, PDO::PARAM_INT);
    $smt->execute();
	    
   $data = $smt->fetch(PDO::FETCH_ASSOC);
   return new OverrideRequest($data); 
  }
  
  /**
   * Retrieve all requests from the database
   */ 
  public static function getOverrideRequests() : OverrideRequest
  {
  	global $request_tbl;
  	$pdo = connectDB();
  	
  	$smt = $pdo->prepare("SELECT * FROM $student_table INNER JOIN $request_tbl ON $request_tbl.student_id = $student_tbl.id INNER JOIN $section_tbl ON $request_tbl.section_id = $request_tbl.id");
   $smt->execute();
   
   $requestsList = $smt->fetchAll();
   $returnList = array();
   foreach ($requestsList as $row){
		$request = new OverrideRequest($row);
		array_push($returnList, $request);
   }
    
   //$out = new OverrideRequest($data['email'], $data['first_name'], $data['last_name'], $data['banner_id'], $data['grad_month'], $majors, $minors, $data['id']);
   return $returnList;
  }
  
}

//TESTING
//$me = new Student('mmk5213', 'Micah', 'Kuan', '123456789', '2021', array(1), array(2));
//$me->id = 1;
//$sec = new Section();
//$sec->id = 2;
//addOverrideRequest($me, $sec, listStatuses()[1], listOverrideTypes()[0], 'testexplanation');
?>

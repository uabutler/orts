<?php
include_once 'common_db.php';
include_once 'requests_db.php';

class Attachment
{
  private $id;
  private $request;
  private $name;
  private $path;

  public function getId() { return $this->id; }
  public function getRequest() { return $this->request; }
  public function getName() { return $this->name; }
  public function getPath() { return $this->path; }

  /**
   * Setters
   */
  public function setName(string $email)
  {
    $this->name = $name;
  }

  public function setPath(string $first_name)
  {
    $this->first_name = $first_name;
  }

  private function __construct(Request $request, string $name, string $path, int $id=null)
  {
    $this->id = $id;
    $this->request = $request;
    $this->name = $name;
    $this->path = $path;
  }

  private function insertDB()
  {
    global $attachment_tbl;
    $pdo = connectDB();

    $request_id = $this->request->getId();
    $smt = $pdo->prepare("INSERT INTO $attachment_tbl (request_id, name, path) VALUES (:request_id, :name, :path)");
    $smt->bindParam(":request_id", $request_id, PDO::PARAM_INT);
    $smt->bindParam(":name", $this->name, PDO::PARAM_STR);
    $smt->bindParam(":path", $this->path, PDO::PARAM_STR);
    $smt->execute();

    $this->id = $pdo->lastInsertId();
  }

  private function updateDB()
  {
    global $attachment_tbl;
    $pdo = connectDB();

    $request_id = $this->request->getId();
    $smt = $pdo->prepare("UPDATE $attachment_tbl SET request_id=:request_id, name=:name, path=:path WHERE id=:id");
    $smt->bindParam(":id", $this->id, PDO::PARAM_INT);
    $smt->bindParam(":request_id", $request_id, PDO::PARAM_INT);
    $smt->bindParam(":name", $this->name, PDO::PARAM_STR);
    $smt->bindParam(":path", $this->path, PDO::PARAM_STR);
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

  public static function buildAttachment(Request $request, string $name, string $path): Attachment
  {
    return new Attachment($request, $name, $path);
  }

  public static function listAttachments(Request $request): array
  {
    global $attachment_tbl;
    $pdo = connectDB();

    $request_id = $this->request->getId();
    $smt = $pdo->prepare("SELECT * FROM $attachment_tbl WHERE request_id=:request_id");
    $smt->bindParam(":request_id", $request_id, PDO::PARAM_INT);
    $smt->execute();

    $data = $smt->fetchAll(PDO::FETCH_ASSOC);

    if(!$data) return [];

    $out = [];

    foreach($data as $row)
      array_push(new Attachment(new Request($row['request_id'], $row['name'], $row['path'], $row['id'])));

    return $out;
  }


  public static function getAttachment(string $path): ?Attachment
  {
    global $attachment_tbl;
    $pdo = connectDB();

    $smt = $pdo->prepare("SELECT * FROM $attachment_tbl WHERE path=:path LIMIT 1");
    $smt->bindParam(":path", $path, PDO::PARAM_STR);
    $smt->execute();

    $data = $smt->fetch(PDO::FETCH_ASSOC);

    if(!$data) return null;

    return new Attachment($request, $data['name'], $data['path'], $data['id']);
  }
}

?>

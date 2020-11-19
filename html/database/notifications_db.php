<?php
include_once 'common_db.php';

class Notification
{
  private $id;
  private $request;
  private $sender_email;
  private $receiver_email;
  private $body;

  public function getId() { return $this->id; }
  public function getRequest() { return $this->request; }
  public function getSenderEmail() { return $this->sender_email; }
  public function getReceiverEmail() { return $this->receiver_email; }
  public function getBody() { return $this->body; }

  /**
   * Setters
   */
  public function setRequest(Request $request)
  {
    $this->request = $request;
  }

  public function setSenderEmail(string $sender_email)
  {
    $this->sender_email = $sender_email;
  }

  public function setReceiverEmail(string $receiver_email)
  {
    $this->last_name = $last_name;
  }

  public function setBody(string $body)
  {
    $this->body = $body;
  }

  private function __construct(Request $request, string $sender_email, string $receiver_email, string $body, int $id=null)
  {
    $this->id = $id;
    $this->email = $email;
    $this->sender_email = $sender_email;
    $this->receiver_email = $receiver_email;
    $this->body = $body;
  }

  private function insertDB()
  {
    global $notification_tbl;
    $pdo = connectDB();

    $request_id = $this->request->getId();
    $smt = $pdo->prepare("INSERT INTO $notification_tbl (request_id, sender_email, receiver_email, body) VALUES (:request_id, :sender_email, :receiver_email, :body)");
    $smt->bindParam(":request_id", $request_id, PDO::PARAM_INT);
    $smt->bindParam(":sender_email", $this->sender_email, PDO::PARAM_STR);
    $smt->bindParam(":receiver_email", $this->receiver_email, PDO::PARAM_STR);
    $smt->bindParam(":body", $this->body, PDO::PARAM_LOB);
    $smt->execute();

    $this->id = $pdo->lastInsertId();
  }

  private function updateDB()
  {
    global $notification_tbl;
    $pdo = connectDB();

    $smt = $pdo->prepare("UPDATE $notification_tbl SET request_id=:request_id, sender_email=:sender_email, receiver_email=:receiver_email WHERE id=:id");
    $smt->bindParam(":id", $this->id, PDO::PARAM_INT);
    $smt->bindParam(":request_id", $request_id, PDO::PARAM_INT);
    $smt->bindParam(":sender_email", $this->sender_email, PDO::PARAM_STR);
    $smt->bindParam(":receiver_email", $this->receiver_email, PDO::PARAM_STR);
    $smt->bindParam(":body", $this->body, PDO::PARAM_LOB);
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

  public static function buildNotification(Request $request, string $sender_email, string $receiver_email, string $body): Notification
  {
    return new Notification($request, $sender_email, $receiver_email, $body);
  }

  private static function listNotificationsHelper($data, string $var_name, int $pdo_param): array
  {
    global $notification_tbl;
    $pdo = connectDB();

    $smt = $pdo->prepare("SELECT * FROM $notification_tbl WHERE $var_name=:data");
    $smt->bindParam(":data", $data, $pdo_param);
    $smt->execute();

    $data = $smt->fetchAll(PDO::FETCH_ASSOC);

    if(!$data) return [];

    $out = [];

    foreach($data as $row)
      array_push(new Notification(new Request($row['request_id'], $row['sender_email'], $row['receiver_email'], $row['body'], $row['id'])));

    return $out;
  }

  public static function listNotifications(Request $request): array
  {
    return listNotificationsHelper($request->getId(), "request_id", PDO::PARAM_INT);
  }

  public static function listSentNotifications(string $sender_email): array
  {
    return listNotificationsHelper($sender_email, "sender_email", PDO::PARAM_STR);
  }

  public static function listReceivedNotifications(string $receiver_email): array
  {
    return listNotificationsHelper($receiver_email, "receiver_email", PDO::PARAM_STR);
  }

  public static function getNotificationId(int $id): ?Notification
  {
    global $notification_tbl;
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

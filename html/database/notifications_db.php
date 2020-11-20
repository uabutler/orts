<?php
include_once 'common_db.php';

class Notification
{
  private $id;
  private $request;
  private $sender_email;
  private $receiver_email;
  private $body;

  /**
   * The database id. Null if it hasn't been stored
   * @return int|null
   */
  public function getId(): ?int
  {
    return $this->id;
  }

  /**
   * The request this notification is associated with
   * @return OverrideRequest
   */
  public function getRequest(): OverrideRequest
  {
    return $this->request;
  }

  /**
   * @return string
   */
  public function getSenderEmail(): string
  {
    return $this->sender_email;
  }

  /**
   * @return string
   */
  public function getReceiverEmail(): string
  {
    return $this->receiver_email;
  }

  /**
   * The text to be sent with the notification
   * @return string
   */
  public function getBody(): string
  {
    return $this->body;
  }

  /**
   * @param OverrideRequest $request
   */
  public function setRequest(OverrideRequest $request)
  {
    $this->request = $request;
  }

  /**
   * @param string $sender_email
   */
  public function setSenderEmail(string $sender_email)
  {
    $this->sender_email = $sender_email;
  }

  /**
   * @param string $receiver_email
   */
  public function setReceiverEmail(string $receiver_email)
  {
    $this->receiver_email = $receiver_email;
  }

  /**
   * @param string $body
   */
  public function setBody(string $body)
  {
    $this->body = $body;
  }

  private function __construct(OverrideRequest $request, string $sender_email, string $receiver_email, string $body, int $id=null)
  {
    $this->id = $id;
    $this->request = $request;
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

  /**
   * This method build a local notification object
   * @param OverrideRequest $request
   * @param string $sender_email
   * @param string $receiver_email
   * @param string $body
   * @return Notification An object that only exists locally, isn't stored in DB
   */
  public static function buildNotification(OverrideRequest $request, string $sender_email, string $receiver_email, string $body): Notification
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
      array_push($out, new Notification(OverrideRequest::getOverrideRequestById($row['request_id']), $row['sender_email'], $row['receiver_email'], $row['body'], $row['id']));

    return $out;
  }

    /**
     * List all of the notification associated with a given request
     * @param OverrideRequest $request Must be stored in the DB
     * @return array
     */
  public static function listNotifications(OverrideRequest $request): array
  {
    return Notification::listNotificationsHelper($request->getId(), "request_id", PDO::PARAM_INT);
  }

  /**
   * List all of the notification sent by a given user
   * @param string $sender_email Should represent a user that exists in the database
   * @return array
   */
  public static function listSentNotifications(string $sender_email): array
  {
    return Notification::listNotificationsHelper($sender_email, "sender_email", PDO::PARAM_STR);
  }

  /**
   * List all of the notification received by a given user
   * @param string $receiver_email Should represent a user that exists in the databse
   * @return array
   */
  public static function listReceivedNotifications(string $receiver_email): array
  {
    return Notification::listNotificationsHelper($receiver_email, "receiver_email", PDO::PARAM_STR);
  }

  /**
   * Retrieves a notification by its database id if it exists, null otherwise.
   * @param int $id
   * @return Notification|null
   */
  public static function getNotificationById(int $id): ?Notification
  {
    global $notification_tbl;
    $pdo = connectDB();

    $smt = $pdo->prepare("SELECT * FROM $notification_tbl WHERE id=:id LIMIT 1");
    $smt->bindParam(":id", $id, PDO::PARAM_INT);
    $smt->execute();

    $data = $smt->fetch(PDO::FETCH_ASSOC);

    if(!$data) return null;

    return new Notification(OverrideRequest::getOverrideRequestById($data['request_id']), $data['sender_email'], $data['receiver_email'], $data['body']);
  }
}

?>

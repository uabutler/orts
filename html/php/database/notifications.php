<?php
require_once __DIR__.'common.php';

/**
 * Represents a notification send in the system. This class relates the notification to a request, and to the sender and
 * receiver's email
 */
class Notification implements JsonSerializable
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
     * @return Request
     */
    public function getRequest(): Request
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
        return htmlspecialchars($this->body, ENT_QUOTES);
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request)
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

    private function __construct(Request $request, string $sender_email, string $receiver_email, string $body,
                                 int $id = null)
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

        if(!$smt->execute()) return false;

        $this->id = $pdo->lastInsertId();

        return true;
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

        if(!$smt->execute()) return false;

        return true;
    }

    /**
     * Stores the current object in the database. If the object is newly created,
     * a new entry into the DB is made. If the student has been stored in the DB,
     * we update the existing entry
     */
    public function storeInDB()
    {
        // The id is set only when the student is already in the databse
        if (is_null($this->id))
            return $this->insertDB();
        else
            return $this->updateDB();
    }

    /**
     * This method build a local notification object
     * @param Request $request
     * @param string $sender_email
     * @param string $receiver_email
     * @param string $body
     * @return Notification An object that only exists locally, isn't stored in DB
     */
    public static function build(Request $request, string $sender_email, string $receiver_email,
                                 string $body): Notification
    {
        return new Notification($request, $sender_email, $receiver_email, $body);
    }

    private static function listHelper($data, string $var_name, int $pdo_param): array
    {
        global $notification_tbl;
        $pdo = connectDB();

        $smt = $pdo->prepare("SELECT * FROM $notification_tbl WHERE $var_name=:data");
        $smt->bindParam(":data", $data, $pdo_param);
        $smt->execute();

        $data = $smt->fetchAll(PDO::FETCH_ASSOC);

        if (!$data) return [];

        $out = [];

        foreach ($data as $row)
            array_push($out, new Notification(Request::getById($row['request_id']), $row['sender_email'],
                $row['receiver_email'], $row['body'], $row['id']));

        return $out;
    }

    /**
     * List all of the notification associated with a given request
     * @param Request $request Must be stored in the DB
     * @return array
     */
    public static function list(Request $request): array
    {
        return Notification::listHelper($request->getId(), "request_id", PDO::PARAM_INT);
    }

    /**
     * List all of the notification sent by a given user
     * @param string $sender_email Should represent a user that exists in the database
     * @return array
     */
    public static function listSent(string $sender_email): array
    {
        return Notification::listHelper($sender_email, "sender_email", PDO::PARAM_STR);
    }

    /**
     * List all of the notification received by a given user
     * @param string $receiver_email Should represent a user that exists in the databse
     * @return array
     */
    public static function listReceivedNotifications(string $receiver_email): array
    {
        return Notification::listHelper($receiver_email, "receiver_email", PDO::PARAM_STR);
    }

    /**
     * Retrieves a notification by its database id if it exists, null otherwise.
     * @param int $id
     * @return Notification|null
     */
    public static function getById(int $id): ?Notification
    {
        global $notification_tbl;
        $pdo = connectDB();

        $smt = $pdo->prepare("SELECT * FROM $notification_tbl WHERE id=:id LIMIT 1");
        $smt->bindParam(":id", $id, PDO::PARAM_INT);
        $smt->execute();

        $data = $smt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Notification(Request::getById($data['request_id']), $data['sender_email'],
            $data['receiver_email'], $data['body']);
    }

    public function jsonSerialize()
    {
        $out = get_object_vars($this);

        $out['body'] = $this->getBody();

        return $out;
    }
}

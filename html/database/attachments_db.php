<?php
include_once 'common_db.php';
include_once 'requests_db.php';

/**
 * Helpful for tracking what files on an OS are attachments for requests and what the
 * original name of the file was. The databse does not store the file itself, that task
 * is left to the file system of the server.
 */
class Attachment
{
    private $id;
    private $request;
    private $name;
    private $path;

    /**
     * The database id. Null if it hasn't been stored
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * The request this attachment is associated with
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * The original name of the file
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * The path to the local version of the file in the server's filesystem
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Set the original name of the file
     * @param string $name The original name of the file
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * Set the path to the local version of the file in the server's filesystem
     * @param string $path The path to the file in the local operating system
     */
    public function setPath(string $path)
    {
        $this->path = $path;
    }

    private function __construct(Request $request, string $name, string $path, int $id = null)
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
        if (is_null($this->id))
            $this->insertDB();
        else
            $this->updateDB();
    }

    /**
     * Given the attachment info, this method builds a local attachment object
     * @param Request $request The request this attachment is associated with, must have already been stored in DB
     * @param string $name The original name of the file
     * @param string $path The path to the file in the host OS
     * @return Attachment An object that only exists locally, isn't stored in DB
     */
    public static function build(Request $request, string $name, string $path): Attachment
    {
        return new Attachment($request, $name, $path);
    }

    /**
     * List all of the attachments associated with a given request, must have already been stored in the DB
     * @param Request $request
     * @return array
     */
    public static function list(Request $request): array
    {
        global $attachment_tbl;
        $pdo = connectDB();

        $request_id = $request->getId();
        $smt = $pdo->prepare("SELECT * FROM $attachment_tbl WHERE request_id=:request_id");
        $smt->bindParam(":request_id", $request_id, PDO::PARAM_INT);
        $smt->execute();

        $data = $smt->fetchAll(PDO::FETCH_ASSOC);

        if (!$data) return [];

        $out = [];

        foreach ($data as $row)
            array_push($out, new Attachment(Request::getById($row['request_id']),
                $row['name'], $row['path'], $row['id']));

        return $out;
    }

    /**
     * Returns the attachment object given it's location in the OS, null if not found
     * @param string $path
     * @return Attachment|null
     */
    public static function get(string $path): ?Attachment
    {
        global $attachment_tbl;
        $pdo = connectDB();

        $smt = $pdo->prepare("SELECT * FROM $attachment_tbl WHERE path=:path LIMIT 1");
        $smt->bindParam(":path", $path, PDO::PARAM_STR);
        $smt->execute();

        $data = $smt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Attachment(Request::getById($data['request_id']), $data['name'], $data['path'], $data['id']);
    }
}

<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/requests.php';

/**
 * Helpful for tracking what files on an OS are attachments for requests and what the
 * original name of the file was. The databse does not store the file itself, that task
 * is left to the file system of the server.
 */
class Attachment implements JsonSerializable
{
    private $id;
    private $request;
    private $name;
    private $upload_time;
    private $path;
    private $filesize;

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

    public function getUploadTime(): ?string
    {
        return $this->upload_time;
    }

    private static function computeFileSize($path): string
    {
        $filesize = filesize($path);
        // Stolen from a random php.net comment
        $sz = 'BKMGTP';
        $factor = (int) floor((strlen($filesize) - 1) / 3);
        return sprintf("%.2f", $filesize / pow(1024, $factor)) . @$sz[$factor];
    }

    public function getFileSize(): string
    {
        return $this->filesize;
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
     * @param string $path The ABSOLUTE path to the file in the OS.
     */
    public function setPath(string $path)
    {
        $this->path = $path;
    }

    private function __construct(Request $request, ?string $upload_time, string $name, string $path, ?string $filesize, int $id = null)
    {
        $this->id = $id;
        $this->upload_time = $upload_time;
        $this->request = $request;
        $this->name = $name;
        $this->path = $path;
        $this->filesize = $filesize;
    }

    private function insertDB(): bool
    {
        global $attachment_tbl;

        $timestamp = getTimeStamp();

        $pdo = connectDB();

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //Shows SQL errors

        $request_id = $this->request->getId();
        $smt = $pdo->prepare("INSERT INTO $attachment_tbl (request_id, upload_time, name, path) VALUES (:request_id, :upload_time, :name, :path)");
        $smt->bindParam(":request_id", $request_id, PDO::PARAM_INT);
        $smt->bindParam(":upload_time", $timestamp, PDO::PARAM_STR);
        $smt->bindParam(":name", $this->name, PDO::PARAM_STR);
        $smt->bindParam(":path", $this->path, PDO::PARAM_STR);

        if (!$smt->execute()) return false;

        $this->id = $pdo->lastInsertId();
        $this->filesize = $this->computeFileSize($this->path);

        return true;
    }

    private function updateDB(): bool
    {
        global $attachment_tbl;
        $pdo = connectDB();

        $request_id = $this->request->getId();
        $smt = $pdo->prepare("UPDATE $attachment_tbl SET request_id=:request_id, name=:name, path=:path WHERE id=:id");
        $smt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $smt->bindParam(":request_id", $request_id, PDO::PARAM_INT);
        $smt->bindParam(":name", $this->name, PDO::PARAM_STR);
        $smt->bindParam(":path", $this->path, PDO::PARAM_STR);

        if (!$smt->execute()) return false;

        return true;
    }

    /**
     * Stores the current object in the database. If the object is newly created,
     * a new entry into the DB is made. If the student has been stored in the DB,
     * we update the existing entry
     */
    public function storeInDB(): bool
    {
        // The id is set only when the student is already in the database
        if (is_null($this->id))
            return $this->insertDB();
        else
            return $this->updateDB();
    }

    /**
     * Delete the current element from the database. This is NOT reversible (unlike setting to inactive)
     * @return bool Did the deletion succeed?
     */
    public function deleteFromDB(): bool
    {
        return self::deleteById($this->id);
    }

    /**
     * @param int $id The id of the element to be deleted
     * @param PDO|null $pdo A connection. We can pass one if one hasn't been created, otherwise, we'll create a new one
     * @return bool Did the deletion succeed?
     */
    public static function deleteById(int $id, PDO $pdo = null): bool
    {
        // TODO: Delete file
        global $attachment_tbl;
        if (is_null($pdo)) $pdo = connectDB();
        return deleteByIdFrom($attachment_tbl, $id, $pdo);
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
        return new Attachment($request, null, $name, $path, null);
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
            $out[] = new Attachment(Request::getById($row['request_id']), $row['upload_time'], $row['name'], $row['path'], self::computeFileSize($data['path']), $row['id']);

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

        return new Attachment(Request::getById($data['request_id']), $data['upload_time'], $data['name'], $data['path'], self::computeFileSize($data['path']), $data['id']);
    }

    /**
     * Retrieves an attachment given its database id. Null if it can't be found
     * @param int $id
     * @return Section|null
     */
    public static function getById(int $id): ?Attachment
    {
        global $attachment_tbl;
        $pdo = connectDB();

        $smt = $pdo->prepare("SELECT * FROM $attachment_tbl WHERE id=:id LIMIT 1");
        $smt->bindParam(":id", $id, PDO::PARAM_INT);
        $smt->execute();

        $data = $smt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Attachment(Request::getById($data['request_id']), $data['upload_time'], $data['name'], $data['path'], self::computeFileSize($data['path']), $data['id']);
    }

    public function jsonSerialize()
    {
        $out = get_object_vars($this);

        unset($out['path']);
        unset($out['request']);
        $out['request_id'] = $this->request->getId();

        return $out;
    }
}

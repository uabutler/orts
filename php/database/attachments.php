<?php
require_once __DIR__ . '/../logger.php';
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/helper/PDOWrapper.php';
require_once __DIR__ . '/helper/DAO.php';
require_once __DIR__ . '/helper/DAODeletable.php';
require_once __DIR__ . '/requests.php';

/**
 * Helpful for tracking what files on an OS are attachments for requests and what the
 * original name of the file was. The databse does not store the file itself, that task
 * is left to the file system of the server.
 */
class Attachment extends DAO implements JsonSerializable, DAODeletable
{
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

    private static function computeFileSize(string $path): string
    {
        $filesize = filesize($path);
        // Stolen from a random php.net comment
        $sz = 'BKMGTP';
        $factor = (int)floor((strlen($filesize) - 1) / 3);
        $ret = sprintf("%.2f", $filesize / pow(1024, $factor)) . @$sz[$factor];

        Logger::info("Computed size for $path as $ret");

        return $ret;
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

    private function __construct(Request $request, ?string $upload_time, string $name, string $path, ?string $filesize,
                                 int $id = null)
    {
        $this->id = $id;
        $this->upload_time = $upload_time;
        $this->request = $request;
        $this->name = $name;
        $this->path = $path;
        $this->filesize = $filesize;
    }

    /**
     * @throws DatabaseException
     */
    protected function insert(): void
    {
        global $attachment_tbl;

        $timestamp = getTimeStamp();

        Logger::info("attachment creation time: $timestamp");

        $pdo = PDOWrapper::getConnection();
        $query = "INSERT INTO $attachment_tbl
        (
            request_id,
            upload_time,
            name,
            path
        )
        VALUES
        (
            :request_id,
            :upload_time,
            :name,
            :path
        )";

        $smt = $pdo->prepare($query);
        $request_id = $this->request->getId();
        $smt->bindParam(":request_id", $request_id, PDO::PARAM_INT);
        $smt->bindParam(":upload_time", $timestamp, PDO::PARAM_STR);
        $smt->bindParam(":name", $this->name, PDO::PARAM_STR);
        $smt->bindParam(":path", $this->path, PDO::PARAM_STR);

        $this->id = PDOWrapper::insert($attachment_tbl, $smt, Logger::obj($this));
        $this->filesize = $this->computeFileSize($this->path);
    }

    /**
     * @throws DatabaseException
     */
    protected function update(): void
    {
        global $attachment_tbl;

        $pdo = PDOWrapper::getConnection();
        $query = "UPDATE $attachment_tbl SET
            request_id=:request_id,
            name=:name,
            path=:path
        WHERE id=:id";

        $smt = $pdo->prepare($query);
        $request_id = $this->request->getId();
        $smt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $smt->bindParam(":request_id", $request_id, PDO::PARAM_INT);
        $smt->bindParam(":name", $this->name, PDO::PARAM_STR);
        $smt->bindParam(":path", $this->path, PDO::PARAM_STR);

        PDOWrapper::update($attachment_tbl, $smt, $this->id, Logger::obj($this));
    }

    /**
     * Delete the current element from the database. This is NOT reversible (unlike setting to inactive)
     * @throws DatabaseException
     */
    public function delete(): void
    {
        self::deleteById($this->id);
    }

    /**
     * @param int $id The id of the element to be deleted
     * @throws DatabaseException
     */
    public static function deleteByID(int $id): void
    {
        global $attachment_tbl;
        $attachment = self::getById($id);
        PDOWrapper::deleteLeaf($attachment_tbl, $id);
        PDOWrapper::markFileForDeletion($attachment->getPath());
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
    public static function list(Request $request): ?array
    {
        global $attachment_tbl;

        Logger::info("Retrieving attachments from the database for request " . $request->getId());

        $pdo = PDOWrapper::getConnection();

        $request_id = $request->getId();
        $smt = $pdo->prepare("SELECT * FROM $attachment_tbl WHERE request_id=:request_id");
        $smt->bindParam(":request_id", $request_id, PDO::PARAM_INT);

        if (!$smt->execute())
        {
            Logger::error("Attachment retrieval failed. Error info: " . $smt->errorInfo());
            Logger::error("Request ID: $request_id");

            return null;
        }

        $data = $smt->fetchAll(PDO::FETCH_ASSOC);

        Logger::info("Retrieved attachments list for request $request_id: " . Logger::obj($data), Verbosity::HIGH);
        Logger::info("Building attachment list");

        $out = [];

        foreach ($data as $row)
            $out[] = new Attachment(Request::getById($row['request_id']), $row['upload_time'], $row['name'],
                $row['path'], self::computeFileSize($row['path']), $row['id']);

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

        Logger::info("Retrieving attachment from database. Path: $path");

        $pdo = PDOWrapper::getConnection();

        $smt = $pdo->prepare("SELECT * FROM $attachment_tbl WHERE path=:path LIMIT 1");
        $smt->bindParam(":path", $path, PDO::PARAM_STR);

        if (!$smt->execute())
        {
            Logger::error("Could not retrieve attachment from the database. Error info: " . Logger::obj($smt->errorInfo()));
            Logger::error("Attachment path: $path");
            return null;
        }

        $data = $smt->fetch(PDO::FETCH_ASSOC);

        Logger::info("Retrieved attachments for file $path: " . Logger::obj($data), Verbosity::HIGH);
        Logger::info("Building attachment object");

        if (!$data) return null;

        return new Attachment(Request::getById($data['request_id']), $data['upload_time'], $data['name'],
            $data['path'], self::computeFileSize($data['path']), $data['id']);
    }

    /**
     * Retrieves an attachment given its database id. Null if it can't be found
     * @param int $id
     * @return Section|null
     */
    public static function getById(int $id): ?Attachment
    {
        global $attachment_tbl;

        Logger::info("Retrieving attachment from database. ID: $id");

        $pdo = PDOWrapper::getConnection();

        $smt = $pdo->prepare("SELECT * FROM $attachment_tbl WHERE id=:id LIMIT 1");
        $smt->bindParam(":id", $id, PDO::PARAM_INT);

        if (!$smt->execute())
        {
            Logger::error("Could not retrieve attachment from the database. Error info: " . Logger::obj($smt->errorInfo()));
            Logger::error("Attachment ID: $id");
            return null;
        }

        $data = $smt->fetch(PDO::FETCH_ASSOC);

        Logger::info("Retrieved attachments for id $id: " . Logger::obj($data), Verbosity::HIGH);
        Logger::info("Building attachment object");

        if (!$data) return null;

        return new Attachment(Request::getById($data['request_id']), $data['upload_time'], $data['name'], $data['path'],
            self::computeFileSize($data['path']), $data['id']);
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

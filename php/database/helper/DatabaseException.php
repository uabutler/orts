<?php
require_once __DIR__ . '/PDOWrapper.php';

/**
 * This is used instead of PDOException. Here, the message isn't the one returned by the database, but the one that
 * will be returned by this system to the user.
 *
 * The error info are taken from the PDO helper.
 *
 * The code is the recommended HTTP response code for the API call. That is, 400 for problems that are attributed to the
 * user, and 500 to problems attributed to the system.
 *
 * Anytime this exception is created, the transaction for this request is rolled back, so no changes are made to the
 * database
 */
class DatabaseException extends Exception
{
    private $error_info;

    public function getErrorInfo(): array
    {
        return $this->error_info;
    }

    public function __construct(string $message, int $code, array $error_info)
    {
        parent::__construct($message, $code);
        $this->error_info = $error_info;
        PDOWrapper::getConnection()->rollBack();
    }
}
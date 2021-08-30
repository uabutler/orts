<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../logger.php';
require_once __DIR__ . '/tables.php';

// This function returns a connection to the MySQL DB
function connectDB(): PDO
{
    static $pdo = null;

    if ($pdo == null)
    {
        Logger::info("Creating database connection");

        $dsn = "mysql:host=".DATABASE['host'].";dbname=".DATABASE['db_name'];
        try
        {
            $pdo = new PDO($dsn, DATABASE['user'], DATABASE['passwd']);
        }
        catch (PDOException $e)
        {
            Logger::error("Could not create a database connection" . Logger::obj($e), Verbosity::LOW, true);
            require_once __DIR__ . '/../../html/error/error500.php';
            exit();
        }
    }

    return $pdo;
}

function flattenResult(array $result): array
{
    $out = [];
    foreach ($result as $row) $out[] = $row[0];
    return $out;
}

function deleteByIdFrom(string $table, int $id, PDO $pdo): bool
{
    Logger::info("Starting deletion from $table for $id");
    $query = "DELETE FROM $table WHERE id=:id";
    Logger::info("Running query: $query");
    $smt = $pdo->prepare($query);
    $smt->bindParam(":id", $id, PDO::PARAM_INT);

    if (!$smt->execute())
    {
        Logger::error("Could not delete from $table: " . Logger::obj($smt->errorInfo()));
        Logger::error("Item ID: $id");
        return false;
    }
    else
    {
        Logger::info("Deletion completed successfully", Verbosity::MED);
        return true;
    }
}

function inactiveByIdFrom(string $table, int $id, PDO $pdo): bool
{
    Logger::info("Setting $id from $table inactive");
    $query = "UPDATE $table SET active=false WHERE id=:id";
    Logger::info("Running query: $query");
    $smt = $pdo->prepare();
    $smt->bindParam(":id", $id, PDO::PARAM_INT);

    if (!$smt->execute())
    {
        Logger::error("Could not set inactive in $table: " . Logger::obj($smt->errorInfo()));
        Logger::error("Item ID: $id");
        return false;
    }
    else
    {
        Logger::info("Set inactive successfully", Verbosity::MED);
        return true;
    }
}

function getEnums(string $table, string $field, $pdo = null): array
{
    if (is_null($pdo)) $pdo = connectDB();

    Logger::info("Retrieving enum values for $table $field");
    $smt = $pdo->prepare("SHOW COLUMNS FROM $table WHERE Field=:field");
    $smt->bindParam(":field", $field, PDO::PARAM_STR);

    if (!$smt->execute())
    {
        Logger::error("Could retrieve enum values: " . Logger::obj($smt->errorInfo()));
        require '../../html/error/error500.php';
        exit();
    }

    return explode("','", substr($smt->fetch(PDO::FETCH_ASSOC)['Type'], 6, -2));
}

function getTimeStamp(): string
{
    return (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');
}
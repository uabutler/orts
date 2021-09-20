<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../logger.php';
require_once __DIR__ . '/tables.php';
require_once __DIR__ . '/helper/PDOWrapper.php';

function flattenResult(array $result): array
{
    $out = [];
    foreach ($result as $row) $out[] = $row[0];
    return $out;
}

function getEnums(string $table, string $field): array
{
    $pdo = PDOWrapper::getConnection();

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
<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/tables.php';

// This function returns a connection to the MySQL DB
function connectDB(): PDO
{
    $dsn = "mysql:host=".DATABASE['host'].";dbname=".DATABASE['db_name'];
    return new PDO($dsn, DATABASE['user'], DATABASE['passwd']);
}

function flattenResult(array $result): array
{
    $out = [];

    foreach ($result as $row)
        array_push($out, $row[0]);

    return $out;
}

function getEnums(string $table, string $field, $pdo = null): array
{
    if (is_null($pdo))
        $pdo = connectDB();

    $smt = $pdo->prepare("SHOW COLUMNS FROM $table WHERE Field=:field");
    $smt->bindParam(":field", $field, PDO::PARAM_STR);
    $smt->execute();

    return explode("','", substr($smt->fetch(PDO::FETCH_ASSOC)['Type'], 6, -2));
}

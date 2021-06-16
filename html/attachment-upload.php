<?php
require_once '../php/auth.php';
require_once '../php/database/attachments.php';

Auth::createClient();

if (!Auth::isAuthenticatedStudent(null))
{
    http_response_code(403);

    $response['msg'] = "You aren't allowed to upload attachments";
    echo json_encode($response);

    exit();
}

$fileName = $_POST['name'];
$requestId = $_POST['request'];
$serverFile = Auth::getUser() . '-' . time() . '-' . $fileName;

$data = $_POST['data'];
$fp = fopen('uploads/'.$serverFile,'w');
fwrite($fp, $data);
fclose($fp);

Attachment::build(Request::getById($requestId), $fileName, $serverFile)->storeInDB();

$returnData = array( "serverFile" => $serverFile );
echo json_encode($returnData);

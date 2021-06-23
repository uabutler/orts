<?php
require_once '../../php/auth.php';
require_once '../../php/config.php';
require_once '../../php/database/requests.php';
require_once '../../php/database/attachments.php';

Auth::createClient();

$requestId = $_POST['request'];

if (!Auth::isAuthenticatedStudent(Request::getById($requestId)->getStudent()->getEmail()))
{
    http_response_code(403);

    $response['msg'] = "You aren't allowed to upload attachments";
    echo json_encode($response);

    exit();
}

$fileName = $_FILES['attachment']['name'];
// The lazy man's way of ensuring uniqueness
$serverFile =  SERVER['attachment_loc'] . '/' . Auth::getUser() . '-' . time() . '-' . $fileName;

$attachment = Attachment::build(Request::getById($requestId), $fileName, $serverFile);

move_uploaded_file($_FILES['attachment']['tmp_name'], $serverFile);

if ($attachment->storeInDB())
    echo "success";
else
    echo "failure";
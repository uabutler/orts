<?php
require_once '../../../php/auth.php';
require_once '../../../php/database/attachments.php';

Auth::createClient();

if (!Auth::isAuthenticatedStudent(null))
{
    http_response_code(403);

    $response['msg'] = "You aren't allowed to upload attachments";
    echo json_encode($response);

    exit();
}

// TODO: Can you add an attachment to that request???

$fileName = $_FILES['attachment']['name'];
$requestId = $_POST['request'];
// The lazy man's way of ensuring uniqueness
$serverFile = Auth::getUser() . '-' . time() . '-' . $fileName;

$attachment = Attachment::build(Request::getById($requestId), $fileName, $serverFile);

move_uploaded_file($_FILES['attachment']['tmp_name'], 'uploads/' . $serverFile);

if ($attachment->storeInDB())
    echo "success";
else
    echo "failure";
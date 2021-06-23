<?php
require_once '../../php/auth.php';
require_once '../../php/database/attachments.php';

if (!isset($_GET['id']))
{
    http_response_code(404);

    $response['msg'] = "Please specify an ID of an attachment to access";
    echo json_encode($response);

    exit();
}

$attachment = Attachment::getById($_GET['id']);

$authed = Auth::isAuthenticatedStudent($attachment->getRequest()->getStudent()->getEmail());
$authed = $authed || Auth::isAuthenticatedFaculty();
$authed = $authed || $attachment;

if (!$authed)
{
    http_response_code(404);

    $response['msg'] = "You aren't authorized to access this attachment, or one wasn't found";
    echo json_encode($response);

    exit();
}

$mime_type = mime_content_type($attachment->getPath()) ?: 'application/octet-stream';

// TODO: Fix this non-sense
$filesize = filesize($attachment->getPath());
$filename = $attachment->getName();

header("Content-Type: $mime_type");
header("Content-Length: $filesize");
header("Content-Disposition: filename=$filename");

$fp = fopen($attachment->getPath(), "rb");
fpassthru($fp);
fclose($fp);
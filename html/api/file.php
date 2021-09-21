<?php
require_once '../../php/auth.php';
require_once '../../php/api.php';
require_once '../../php/logger.php';
require_once '../../php/database/attachments.php';

if (!isset($_GET['id']))
    API::error(400, "Please specify an ID of an attachment to access");

Logger::info(Auth::getUser() . " requsted file " . $_GET['id']);

$attachment = Attachment::getById(intval($_GET['id']));

Logger::info($attachment->getName() . " Location: " . $attachment->getPath());

$authed = !is_null($attachment);

if ($authed)
{
    $authed = Auth::isAuthenticatedStudent($attachment->getRequest()->getStudent()->getEmail());
    $authed = $authed || Auth::isAuthenticatedFaculty();
}

if (!$authed)
    API::error(404, "You aren't authorized to access this attachment, or one wasn't found");

$mime_type = mime_content_type($attachment->getPath()) ?: 'application/octet-stream';

$filesize = filesize($attachment->getPath());
$filename = $attachment->getName();

header("Content-Type: $mime_type");
header("Content-Length: $filesize");
header("Content-Disposition: filename=$filename");

$fp = fopen($attachment->getPath(), "rb");
fpassthru($fp);
fclose($fp);
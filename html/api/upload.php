<?php
require_once '../../php/logger.php';
require_once '../../php/auth.php';
require_once '../../php/api.php';
require_once '../../php/config.php';
require_once '../../php/database/requests.php';
require_once '../../php/database/attachments.php';
require_once '../../php/database/helper/DatabaseException.php';

Auth::createClient();

Logger::info("Starting upload");

if (!(isset($_POST['request']) && isset($_FILES['attachment'])))
    API::error(400, "Please specify a request id and upload a file");

Logger::info("User requested attachment be added to " . intval($_POST['request']));

if (!Auth::isAuthenticated())
    API::error(401, "You aren't authorized");

try
{
    $request = Request::getById(intval($_POST['request']));
}
catch (DatabaseException $e)
{
    API::error($e->getCode(), $e->getMessage());
}

Logger::info("Request retrieved from database, verifying authentication");

if (!(Auth::isAuthenticatedStudent($request->getStudent()->getEmail()) || Auth::isAuthenticatedFaculty()))
    API::error(403, "You aren't allowed to upload attachments");

$fileName = $_FILES['attachment']['name'];

Logger::info("User uploaded $fileName");

$serverFile =  SERVER['attachment_loc'] . '/' . Auth::getUser() . '-' . Logger::getRequestId() . '-' . $fileName;

Logger::info("Moving to $serverFile");

$attachment = Attachment::build($request, $fileName, $serverFile);

if (move_uploaded_file($_FILES['attachment']['tmp_name'], $serverFile))
    Logger::info("Attachment stored successfully");
else
    API::error(500, "Unable to store attachment. Please contact the system administrator");

try
{
    $attachment->storeInDB();
    API::success("Upload successful");
}
catch (DatabaseException $e)
{
    API::error($e->getCode(), $e->getMessage());
}

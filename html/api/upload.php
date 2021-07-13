<?php
require_once '../../php/auth.php';
require_once '../../php/api.php';
require_once '../../php/config.php';
require_once '../../php/database/requests.php';
require_once '../../php/database/attachments.php';

Auth::createClient();

if (!(isset($_POST['request']) && isset($_FILES['attachment'])))
    API::error(400, "Please specify a request id and upload a file");

if (!Auth::isAuthenticated())
    API::error(401, "You aren't authorized");

$request = Request::getById(intval($_POST['request']));

if (!(Auth::isAuthenticatedStudent($request->getStudent()->getEmail()) || Auth::isAuthenticatedFaculty()))
    API::error(403, "You aren't allowed to upload attachments");

$fileName = $_FILES['attachment']['name'];
// The lazy man's way of ensuring uniqueness
$serverFile =  SERVER['attachment_loc'] . '/' . Auth::getUser() . '-' . time() . '-' . $fileName;

$attachment = Attachment::build($request, $fileName, $serverFile);

move_uploaded_file($_FILES['attachment']['tmp_name'], $serverFile);

if ($attachment->storeInDB())
    echo "success";
else
    echo "failure";
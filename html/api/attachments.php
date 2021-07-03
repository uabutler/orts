<?php
require_once '../../php/auth.php';
require_once '../../php/api.php';
require_once '../../php/database/requests.php';
require_once '../../php/database/attachments.php';

Auth::createClient();

API::get(function ()
{
   if(!isset($_GET['id']))
       API::error(400, "Please specify the id of the request you want attachments for");

   $request = Request::getById($_GET['id']);

   $authed = Auth::isAuthenticatedStudent($request->getStudent()->getEmail());
   $authed = $authed || Auth::isAuthenticatedFaculty();

    if (!$authed)
        API::error(403, "You aren't allowed to access this request");

   $attachments = Attachment::list($request);

   if ($attachments)
       return $attachments;
   else
       API::error(204, "No attachment found");

   return null;
});

API::delete(function()
{
    global $_DELETE;

    $attachment = Attachment::getById($_DELETE['id']);

    if (Attachment::deleteById($_DELETE['id']))
        return "Success";
    else
        API::error(400, "Could not delete attachment");
});
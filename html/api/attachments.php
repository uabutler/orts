<?php
require_once '../../php/auth.php';
require_once '../../php/api.php';
require_once '../../php/database/requests.php';
require_once '../../php/database/attachments.php';

Auth::createClient();

API::get(function ()
{
    if (!Auth::isAuthenticated())
        API::error(401, "User not authenticated");

   if(!isset($_GET['id']))
       API::error(400, "Please specify the ID of the request you want attachments for");

   $request = Request::getById(intval($_GET['id']));

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

    if (!Auth::isAuthenticated())
        API::error(401, "User not authenticated");

    if(!isset($_DELETE['id']))
        API::error(400, "Please specify the ID of the attachment you want to delete");

    $attachment = Attachment::getById(intval($_DELETE['id']));

    $authed = Auth::isAuthenticatedStudent($attachment->getRequest()->getStudent()->getEmail());
    $authed = $authed || Auth::isAuthenticatedFaculty();

    if (!$authed)
        API::error(403, "You aren't allowed to delete this attachment");

    $attachment->delete();
});
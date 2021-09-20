<?php
require_once __DIR__ . '/../php/error/error-handling.php';
require_once '../php/auth.php';

Auth::createClient();
Auth::forceAuthentication();

if(Auth::isAuthenticatedFaculty())
{
    Auth::forceAuthenticationFaculty();
    header("Location: /admin/request-list.php");
}
else
{
    Auth::forceAuthenticationStudent(null);
    header("Location: /student/request-list.php");
}

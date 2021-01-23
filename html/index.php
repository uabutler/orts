<?php
require_once 'php/auth.php';

Auth::createClient();
if(Auth::isAuthenticated())
{
    if(Auth::isAuthenticatedFaculty())
    {
        Auth::forceAuthenticationFaculty();
        header("Location: /admin/request-list.php");
    }
    else
    {
        Auth::forceAuthenticationStudent();
        header("Location: /student/request-list.php");
    }
}
else
{
    header("Location: /home.php");
}
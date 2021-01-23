<?php
require_once 'php/auth.php';

Auth::createClient();
if(Auth::isAuthenticated())
{
    if(Auth::isAuthenticatedFaculty())
    {
        Auth::forceAuthenticationFaculty();
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
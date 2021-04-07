<?php
require_once 'php/auth.php';

if(!file_exists('../conf/app.ini'))
    header("Location: /install.php");

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
        Auth::forceAuthenticationStudent(null);
        header("Location: /student/request-list.php");
    }
}
else
{
    header("Location: /home.php");
}
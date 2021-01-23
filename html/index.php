<?php
require_once 'php/auth.php';

if(Auth::isAuthenticated())
{
    if(Auth::isAuthenticatedFaculty())
        Auth::forceAuthenticationFaculty();
    else
        Auth::forceAuthenticationStudent();
}
else
{
    header("Location: /home.php");
}
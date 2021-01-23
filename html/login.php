<?php
require_once 'php/auth.php';

Auth::createClient();
Auth::forceAuthentication();

if(Auth::isAuthenticatedFaculty())
    Auth::forceAuthenticationFaculty();
else
    Auth::forceAuthenticationStudent();

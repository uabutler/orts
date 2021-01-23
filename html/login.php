<?php
require_once 'php/cas/CAS.php';

Auth::createClient();
Auth::forceAuthentication();

if(Auth::isAuthenticatedFaculty())
    Auth::forceAuthenticationFaculty();
else
    Auth::forceAuthenticationStudent();

<?php
require '../php/auth.php';
require_once __DIR__ . '/../php/error/error-handling.php' ;

Auth::createClient();
Auth::logout();
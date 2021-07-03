<?php
require_once '../../../php/auth.php';
require_once '../../../php/api.php';
require_once '../../../php/database/programs.php';

API::get(function ()
{
    return Minor::list();
});

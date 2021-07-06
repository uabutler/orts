<?php
require_once '../../../php/auth.php';
require_once '../../../php/api.php';
require_once '../../../php/database/programs.php';
require_once 'programs.php';

API::get(function ()
{
    return Minor::list();
});

API::post(function($data)
{
    foreach ($data as $program)
        Minor::build($program)->storeInDB();

    return "Success";
});

API::put(function($data)
{
    return updateProgram($data, Minor::class);
});
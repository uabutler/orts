<?php
require_once '../../../php/auth.php';
require_once '../../../php/api.php';
require_once '../../../php/database/programs.php';
require_once 'programs.php';

API::get(function()
{
    return Major::list();
});

API::post(function($data)
{
    foreach ($data as $program)
        Major::build($program)->storeInDB();

    return "Success";
});

API::put(function($data)
{
    return updateProgram($data, Major::class);
});
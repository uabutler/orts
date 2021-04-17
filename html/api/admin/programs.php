<?php
require_once '../../../php/auth.php';
require_once '../../../php/api.php';
require_once '../../../php/database/programs.php';

Auth::createClient();

// Create new programs, either majors or minors
API::post(function($data)
{
    foreach ($data->programs as $program)
    {
        if ($data->type === "major")
            Major::build($program)->storeInDB();
        else
            Minor::build($program)->storeInDB();
    }
});

// Set a given program to inactive
API::delete(function()
{
    global $_DELETE;

    if(!(isset($_DELETE['type']) && isset($_DELETE['program'])))
        API::error(400, "Please the program and type of program");

    if ($_DELETE['type'] === 'major')
        $program = Major::get($_DELETE['program']);
    else
        $program = Minor::get($_DELETE['program']);

    if (is_null($program))
        API::error(204, "No such program found");

    $program->setInactive();

    if ($program->storeInDB())
        return "Success";
    else
        API::error(500, "Could not write to database");
});
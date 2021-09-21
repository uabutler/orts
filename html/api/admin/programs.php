<?php
require_once __DIR__ . '/../../../php/auth.php';
require_once __DIR__ . '/../../../php/api.php';
require_once __DIR__ . '/../../../php/logger.php';
require_once __DIR__ . '/../../../php/database/programs.php';

function addPrograms($data, $type): string
{
    if (!is_array($data))
        API::error(400, "Please submit a list of majors or minors to add");

    Logger::info("Writing {$type}s to database.");

    foreach ($data as $program_name)
    {
        if (is_string($program_name))
        {
            $program = $type::build($program_name);
            $program->storeInDB();
        }
    }

    return 'success';
}

function updateProgram($data, $type): string
{
    foreach ($data as $update)
    {
        $program = $type::getById($update->id);

        if (isset($update->archive) && $update->archive)
            $program->deactivate();

        if (isset($update->delete) && $update->delete)
            $program->delete();
    }

    return 'success';
}
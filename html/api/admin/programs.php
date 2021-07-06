<?php
require_once __DIR__ . '/../../../php/auth.php';
require_once __DIR__ . '/../../../php/api.php';
require_once __DIR__ . '/../../../php/database/programs.php';

function updateProgram($data, $type): string
{
    $ret = true;

    foreach ($data as $update)
    {
        $program = $type::getById($update->id);

        if (isset($update->archive) && $update->archive)
            $program->setInactive();

        $ret = $ret && $program->storeInDB();

        if (isset($update->delete) && $update->delete)
            $ret = $ret && $program->deleteFromDB();
    }

    if ($ret)
        return 'Success';
    else
        return 'Uh oh';

    // TODO: Error Handling
}
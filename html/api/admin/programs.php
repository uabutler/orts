<?php
require_once __DIR__ . '/../../../php/auth.php';
require_once __DIR__ . '/../../../php/api.php';
require_once __DIR__ . '/../../../php/database/programs.php';

function addPrograms($data, $type): int
{
    if (!is_array($data))
        API::error(400, "Please submit a list of majors to add");

    $ret = 200;

    foreach ($data as $program_name)
    {
        if (is_string($program_name))
        {
            $program = $type::build($program_name);

            if (!$program->storeInDB())
            {
                $error_info = $program->errorInfo();

                if ($type === Major::class)
                    $error_msg = "ORTS ERROR: /api/admin/majors.php EDIT ";
                else
                    $error_msg = "ORTS ERROR: /api/admin/minors.php EDIT ";

                $error_msg .= " User=" . Auth::getUser();
                $error_msg .= " Name=" . $program_name;
                $error_msg .= " SQLSTATE=" . $error_info[0];
                $error_msg .= " ErrorMsg=" . $error_info[2];
                error_log($error_msg);

                if ($error_info[1] === "23000")
                    $ret = 409;
                else
                    API::error(500, "An unknown error has occurred. Please contact the system administrator");
            }
        }
    }

    if ($ret === 409)
        API::error(409, "One or more of the programs were all ready added");
    else
        return "Success";
}

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
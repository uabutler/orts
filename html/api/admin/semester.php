<?php
require_once '../../../php/auth.php';
require_once '../../../php/api.php';
require_once '../../../php/database/courses.php';

Auth::createClient();

API::get(function()
{
    return array_merge(Semester::listActive(), Semester:: listInactive());
});

API::post(function($data)
{
    $semester = Semester::build($data->semester, $data->description);

    if ($semester->storeInDB())
        return "Success";
});

/**
 * Bulk update
 */
API::put(function($data)
{
    $ret = true;

    foreach ($data as $update)
    {
        $semester = Semester::getById($update->id);

        if (isset($update->description))
            $semester->setDescription($update->description);

        if (isset($update->semester))
            $semester->setCode($update->semester);

        if (isset($update->archive) && $update->archive)
            $semester->setInactive();

        $ret = $semester->storeInDB();

        if (isset($update->delete) && $update->delete)
            $ret = $ret && $semester->deleteFromDB();
    }

    if ($ret)
        return 'Success';

    // TODO: Error Handling
});

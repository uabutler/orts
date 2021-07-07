<?php
require_once '../../../php/auth.php';
require_once '../../../php/api.php';
require_once '../../../php/database/courses.php';

API::get(function ()
{
    return Department::list();
});

API::post(function ($data)
{
    $dept = Department::build($data->department);

    if ($dept->storeInDB())
        return "Success";
});

API::put(function ($data)
{
    $ret = true;

    foreach ($data as $update)
    {
        $dept = Department::getById($update->id);

        if (isset($update->archive) && $update->archive)
            $dept->setInactive();

        $ret = $ret && $dept->storeInDB();

        if (isset($update->delete) && $update->delete)
            $ret = $ret && $dept->deleteFromDB();
    }

    if ($ret)
        return 'Success';

    // TODO: Error Handling
    return null;
});

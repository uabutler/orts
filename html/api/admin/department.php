<?php
require_once '../../../php/auth.php';
require_once '../../../php/logger.php';
require_once '../../../php/api.php';
require_once '../../../php/database/courses.php';

API::get(function()
{
    return Department::list();
});

API::post(function ($data)
{
    if (!(isset($data->department) && preg_match('/^[A-Z]+$/', $data->department)))
        API::error(400, "Department not specified properly.");

    $dept = Department::build($data->department);

    $dept->storeInDB();
    return "success";
});

API::put(function ($data)
{
    if (!is_array($data))
        API::error(400, "Please submit a list of departments to update");

    foreach ($data as $update)
    {
        if (!(isset($update->id)))
            API::error(400, "Please specify the id of each department you want to update");

        $department = Department::getById(intval($update->id));

        if ($department == null)
            throw new DatabaseException("One or more departments could not be found", 400, null);

        if (isset($update->archive) && filter_var($update->archive, FILTER_VALIDATE_BOOLEAN))
            $department->setInactive();

        $department->storeInDB();

        if (isset($update->delete) && filter_var($update->delete, FILTER_VALIDATE_BOOLEAN))
            $department->delete();
    }

    return "success";
});

API::error(404, "Not Found");

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

    if ($dept->storeInDB())
    {
        return "Success";
    }
    else
    {
        $error_info = $dept->errorInfo();

        $error_msg = "ORTS ERROR: /api/admin/department.php CREATE ";
        $error_msg .= " Dept=" . $data->department;
        $error_msg .= " SQLSTATE=" . $error_info[0];
        $error_msg .= " ErrorMsg=" . $error_info[2];
        error_log($error_msg);

        if ($error_info[0] === "23000")
            API::error(409, "This department has already been created");
        else
            API::error(500, "An unknown error has occurred. Please contact the system administrator");
    }
});

API::put(function ($data)
{
    $ret = true;

    if (!is_array($data))
        API::error(400, "Please submit a list of departments to update");

    foreach ($data as $update)
    {
        if (!(isset($update->id)))
            API::error(400, "Please specify the id of each department you want to update");

        $department = Department::getById(intval($update->id));

        if (is_null($department))
        {
            $error_msg = "ORTS ERROR: /api/admin/department.php UPDATE ";
            $error_msg .= " Not Found ID=" . $update->id;
            error_log($error_msg);

            $ret = false;
            continue;
        }

        if (isset($update->archive) && filter_var($update->archive, FILTER_VALIDATE_BOOLEAN))
            $department->setInactive();

        $err = $department->storeInDB();

        if (!$err)
        {
            $error_info = $department->errorInfo();

            $error_msg = "ORTS ERROR: /api/admin/department.php UPDATE ";
            $error_msg .= " ID=" . $update->id;
            $error_msg .= " Can't Deactivate STATUS=" . ($department->isActive() ? "active" : "inactive");
            $error_msg .= " SQLSTATE=" . $error_info[0];
            $error_msg .= " ErrorMsg=" . $error_info[2];
            error_log($error_msg);
        }

        $ret = $ret && $err;

        if (isset($update->delete) && filter_var($update->delete, FILTER_VALIDATE_BOOLEAN))
            $err = $department->deleteFromDB();

        if (!$err)
        {
            $error_msg = "ORTS ERROR: /api/admin/department.php UPDATE ";
            $error_msg .= " ID=" . $update->id;
            $error_msg .= " Can't Delete STATUS=" . ($department->isActive() ? "active" : "inactive");
            error_log($error_msg);
        }

        $ret = $ret && $err;
    }

    if ($ret)
        return "Success";
    else
        API::error(500, "An unknown error has occurred. One or more departments were not updated. Please contact the system administrator");
});

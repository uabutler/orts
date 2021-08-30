<?php
require_once '../../../php/auth.php';
require_once '../../../php/api.php';
require_once '../../../php/database/faculty.php';

API::get(function()
{
    return Faculty::list();
});

API::post(function($data)
{
    if (!(isset($data->email) && filter_var($data->email . "@truman.edu", FILTER_VALIDATE_EMAIL)))
        API::error(400, "Email address not specified properly");

    if (!(isset($data->first_name) && preg_match('/^\S+/', $data->first_name)))
        API::error(400, "First name not specified properly");

    if (!(isset($data->last_name) && preg_match('/^\S+/', $data->last_name)))
        API::error(400, "Last name not specified properly");

    $faculty = Faculty::build($data->email, $data->first_name, $data->last_name);

    if ($faculty->storeInDB())
    {
        return "Success";
    }
    else
    {
        $error_info = $faculty->errorInfo();

        $error_msg = "ORTS ERROR: /api/admin/faculty.php CREATE ";
        $error_msg .= " FirstName=" . $data->first_name;
        $error_msg .= " LastName=" . $data->last_name;
        $error_msg .= " Email=" . $data->email;
        $error_msg .= " SQLSTATE=" . $error_info[0];
        $error_msg .= " ErrorMsg=" . $error_info[2];
        error_log($error_msg);

        if ($error_info[0] === "23000")
            API::error(409, "A faculty with this email has already been created");
        else
            API::error(500, "An unknown error has occurred. Please contact the system administrator");
    }
});

API::put(function($data)
{
    if (!(isset($data->id) && is_int($data->id)))
        API::error(400, "Please specify the ID of the faculty to update");

    $faculty = Faculty::getById($data->id);

    if (isset($data->archive) && filter_var($data->archive, FILTER_VALIDATE_BOOLEAN))
    {
        $faculty->setDefault();

        if (!$faculty->storeInDB())
        {
            $error_info = $faculty->errorInfo();

            $error_msg = "ORTS ERROR: /api/admin/faculty.php UPDATE ";
            $error_msg .= " ID=" . $data->id;
            $error_msg .= " Can't Default STATUS=" . ($faculty->isDefault() ? "default" : "not default");
            $error_msg .= " SQLSTATE=" . $error_info[0];
            $error_msg .= " ErrorMsg=" . $error_info[2];
            error_log($error_msg);
        }
    }

    if (isset($data->delete) && $data->delete)
    {
        $faculty->deleteFromDB();
    }
});
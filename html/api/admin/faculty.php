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

    $faculty->storeInDB();
    return "success";
});

API::put(function($data)
{
    if (!(isset($data->id) && is_int($data->id)))
        API::error(400, "Please specify the ID of the faculty to update");

    $faculty = Faculty::getById($data->id);
    Logger::info("Updating faculty " . $faculty->getEmail());

    if (isset($data->default) && filter_var($data->default, FILTER_VALIDATE_BOOLEAN))
    {
        Logger::info("Setting default");
        $faculty->setDefault();
        $faculty->storeInDB();
    }

    if (isset($data->delete) && $data->delete)
    {
        Logger::info("Deleting");
        $faculty->delete();
    }

    return "success";
});

API::error(404, "Not Found");

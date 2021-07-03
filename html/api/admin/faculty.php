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
    $faculty = Faculty::build($data->email, $data->first_name, $data->last_name);

    if ($faculty->storeInDB())
        return "Success";
    else
        return "Fail";
});

API::put(function($data)
{
    $faculty = Faculty::getById($data->id);

    if (isset($data->make_default) && $data->make_default)
    {
        $faculty->setDefault();
        $faculty->storeInDB();
    }

    if (isset($data->delete) && $data->delete)
    {
        $faculty->deleteFromDB();
    }
});
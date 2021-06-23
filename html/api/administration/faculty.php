<?php
require_once '../../../php/auth.php';
require_once '../../../php/api.php';
require_once '../../../php/database/faculty.php';

Auth::createClient();

API::get(function ()
{
    return Faculty::list();
});

API::post(function ($data)
{
    $faculty = Faculty::build($data->email, $data->first_name, $data->last_name);

    if($faculty->storeInDB())
        return $faculty->getId();
    else
        API::error(409, "The faculty could not be added");
});
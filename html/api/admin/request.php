<?php
require_once '../../../php/auth.php';
require_once '../../../php/api.php';
require_once '../../../php/database/requests.php';
require_once '../../../php/database/faculty.php';

Auth::createClient();

API::put(function($data)
{
    if(isset($data->id) && is_numeric($data->id))
    {
        $request = Request::getById(intval($data->id));

        if(isset($data->active))
        {
            if(!filter_var($data->active, FILTER_VALIDATE_BOOLEAN))
                $request->setInactive();
        }

        if(isset($data->faculty))
            $request->setFaculty(Faculty::get($data->faculty));

        if(isset($data->status))
        {
            if(!in_array($data->status, Request::listStatuses()))
                API::error(400, "The specified status was not recognized by the system");

            $request->setStatus($data->status);
        }

        if(isset($data->justification))
            $request->setJustification($data->justification);

        if(isset($data->banner))
        {
            if(filter_var($data->banner, FILTER_VALIDATE_BOOLEAN))
                $request->setInBanner();
            else
                $request->setNotInBanner();
        }

        if($request->storeInDB())
            return "Success";
        else
            API::error(500, "Could not write to database");
    }
    else
    {
        API::error(400, "Please specify the id of the request to modify");
    }

    return null;
});

API::error(404, "Not Found");
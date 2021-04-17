<?php
require_once '../../../php/auth.php';
require_once '../../../php/api.php';
require_once '../../../php/database/requests.php';
require_once '../../../php/database/students.php';
require_once '../../../php/database/faculty.php';
require_once '../../../php/database/courses.php';

Auth::createClient();

// Get a request
API::get(function ()
{
    if(!isset($_GET['id']))
        API::error(400, "Please specify the id of the desired request record");

    $request = Request::getById($_GET['id']);

    if (!Auth::isAuthenticatedStudent($request->getStudent()->getEmail()))
        API::error(403, "You aren't allowed to access this request");

    if($request)
        return $request;
    else
        API::error(204, "No request found");

    return null;
});

// Create a request
API::post(function ($data)
{
    // TODO: Validate data

    if (!Auth::isAuthenticatedStudent(Student::getById($data->student_id)->getEmail()))
        API::error(403, "You aren't allowed to create a request for this student " . Student::getById($data->student_id)->getEmail() . " " . Auth::getUser());

    // TODO: Change default faculty in admin functions
    $request = Request::build(Student::getById($data->student_id), Section::getByCrn(Semester::getByCode($data->semester),
                            $data->crn), Faculty::getById(1), 'Received', $data->reason, $data->explanation);

    if($request->storeInDB())
        return $request->getId();
    else
        API::error(409, "The request could not be added");

    return null;
});

// TODO: Rewrite requests to send JSON
API::put(function ($data)
{
    if(isset($data->id) && is_numeric($data->id))
    {
        $request = Request::getById(intval($data->id));


        if (!Auth::isAuthenticatedStudent($request->getStudent()->getEmail()))
            API::error(403, "You aren't allowed to modify requests for this student");

        if(isset($data->semester) || isset($data->crn))
        {
            if(!(isset($data->semester) && isset($data->crn)))
                API::error(400, "Not enough information to determine class, specify semester and crn");

            $request->setSection(Section::getByCrn(Semester::getByCode($data->semester), $data->crn));
        }

        if(isset($data->reason))
            $request->setReason($data->reason);

        if(isset($data->explanation))
            $request->setExplanation($data->explanation);

        if(isset($data->active))
        {
            if(!filter_var($data->active, FILTER_VALIDATE_BOOLEAN))
                $request->setInactive();
        }

        if($request->storeInDB())
            return "Success";
        else
            API::error(500, "Could not write to database");
    }
    else
    {
        API::error(401, "Please specify the id of the request to modify ");
    }

    return null;
});

API::error(404, "Not Found");

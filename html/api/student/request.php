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

    $request = Request::getById(intval($_GET['id']));

    if ($request)
        return $request;
    else
        API::error(204, "No request found");

    if (!Auth::isAuthenticatedStudent($request->getStudent()->getEmail()))
        API::error(403, "You aren't allowed to access this request");

    return null;
});

// Create a request
API::post(function ($data)
{

    // Validate the request was submitted correctly
    if (!Auth::isAuthenticated())
        API::error(401, "User not authorized");

    if (!Auth::isAuthenticatedStudent())
        API::error(403, "User not authorized");

    if (!(isset($data->semester) && preg_match('/^\d{6}$/', $data->semester)))
        API::error(400, "Request semester not specified properly. Please provide 6-digit code sting");

    if (!(isset($data->crn) && preg_match('/^\d{4}$/', $data->crn)))
        API::error(400, "Course CRN not specified properly. Please specify the 4-digit code string");

    if (!(isset($data->reason) && in_array($data->reason, Request::listReasons())))
        API::error(400, "Reason not specified properly. Please choose from available list");

    if (!(isset($data->explanation) && preg_match('/\S+/', $data->explanation)))
        API::error(400, "Please provide an explanation with the request");

    $request = Request::build(Student::get(Auth::getUser()), Section::getByCrn(Semester::getByCode($data->semester),
                            $data->crn), Faculty::getDefault(), 'Received', $data->reason, $data->explanation);

    $request->storeInDB();
    return $request->getId();
});

API::put(function ($data)
{
    if(isset($data->id) && is_numeric($data->id))
    {
        $request = Request::getById(intval($data->id));

        if (!Auth::isAuthenticatedStudent($request->getStudent()->getEmail()))
            API::error(403, "You aren't allowed to modify requests for this student");

        if (isset($data->semester) || isset($data->crn))
        {
            if ($request->getStatus() !== "Received")
                API::error(405, "Cannot change course for request that's been processed");

            if(!(isset($data->semester) && isset($data->crn)))
                API::error(400, "Not enough information to determine class, specify semester and crn");

            if (!(isset($data->semester) && preg_match('/^\d{6}$/', $data->semester)))
                API::error(400, "Request semester not specified properly. Please provide 6-digit code sting");

            if (!(isset($data->crn) && preg_match('/^\d{4}$/', $data->crn)))
                API::error(400, "Course CRN not specified properly. Please specify the 4-digit code string");

            $request->setSection(Section::getByCrn(Semester::getByCode($data->semester), $data->crn));
        }

        if (isset($data->reason))
        {
            if (!in_array($data->reason, Request::listReasons()))
                API::error(400, "Invalid reason. Please choose from available list");

            $request->setReason($data->reason);
        }

        if (isset($data->explanation))
        {
            if (preg_match('/^\s*$/', $data->explanation))
                API::error(400, "Please provide an explanation with the request");

            $request->setExplanation($data->explanation);
        }

        if (isset($data->active))
        {
            // I don't think data validation matters here?
            if(!filter_var($data->active, FILTER_VALIDATE_BOOLEAN))
                $request->setInactive();
        }

        $request->storeInDB();
        return "Success";
    }
    else
    {
        API::error(400, "Please specify the id of the request to modify");
    }

    return null;
});

API::error(404, "Not Found");

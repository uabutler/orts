<?php
require_once '../../../php/auth.php';
require_once '../../../php/api.php';
require_once '../../../php/database/requests.php';
require_once '../../../php/database/students.php';
require_once '../../../php/database/faculty.php';
require_once '../../../php/database/courses.php';

Auth::createClient();

// Retrieve a student
API::get(function()
{
    if (!Auth::isAuthenticatedStudent())
        API::error(401, "You aren't allowed to request information about this student");

    $student = Student::get(Auth::getUser());

    if($student)
        return $student;
    else
        API::error(204, "No student found");

    // This should never happen, but my IDE will whine if I omit it
    return null;
});

// Create a student
API::post(function ($data)
{
    if (!Auth::isAuthenticated())
        API::error(401, "You aren't authorized");

    if (!(isset($data->first_name) && is_string($data->first_name) && $data->first_name !== ""))
        API::error(400, "User first name not specified");

    if (!(isset($data->last_name) && is_string($data->last_name) && $data->last_name !== ""))
        API::error(400, "User last name not specified");

    if (!(isset($data->banner_id) && preg_match('#^001\d{6}$#', $data->banner_id)))
        API::error(400, "User banner ID not specified properly");

    if (!(isset($data->grad_month) && preg_match('#^(05|12)/20[2-9]\d$#', $data->grad_month)))
        API::error(400, "User grad month not specified properly");

    if (!(isset($data->standing) && in_array($data->standing, Student::listStandings())))
        API::error(400, "Standing not specified properly. Please choose from available list");

    $majors = Major::listActive();

    if (isset($data->majors) && is_array($data->majors))
    {
        foreach ($data->majors as $major)
        {
            if (!in_array($major, $majors))
                API::error(400, "$major not recognized as a major");
        }
    }
    else
    {
        API::error(400, "Majors must be specified in a list");
    }

    $minors = Minor::listActive();

    if (isset($data->minors) && is_array($data->minors))
    {
        foreach ($data->minors as $minor)
        {
            if (!in_array($minor, $minors))
                API::error(400, "$minor not recognized as a minor");
        }
    }
    else
    {
        API::error(400, "Minors must be specified in a list");
    }

    $student = Student::build(Auth::getUser(), $data->first_name, $data->last_name, $data->banner_id,
        $data->grad_month, $data->standing, $data->majors, $data->minors);

    $student->storeInDB();
});

API::put(function ($data)
{
    $student = Student::get(Auth::getUser());

    if (isset($data->first_name))
    {
        if (is_string($data->first_name) && $data->first_name !== "")
            $student->setFirstName($data->first_name);
        else
            API::error(400, "User first name specified incorrectly");
    }

    if (isset($data->last_name))
    {
        if (is_string($data->last_name) && $data->last_name !== "")
            $student->setLastName($data->last_name);
        else
            API::error(400, "User last name specified incorrectly");
    }

    if (isset($data->standing))
    {
        if (in_array($data->standing, Student::listStandings()))
            $student->setStanding($data->standing);
        else
            API::error(400, "Standing not specified properly. Please choose from available list");
    }

    $majors = Major::listActive();

    if (isset($data->majors) && is_array($data->majors))
    {
        foreach ($data->majors as $major)
        {
            if (!in_array($major, $majors))
                API::error(400, "$major not recognized as a major");
        }

        $student->setMajors($data->majors);
    }

    $minors = Minor::listActive();

    if (isset($data->minors) && is_array($data->minors))
    {
        foreach ($data->minors as $minor)
        {
            if (!in_array($minor, $minors))
                API::error(400, "$minor not recognized as a minor");
        }

        $student->setMinors($data->minors);
    }

    if (isset($data->banner_id))
    {
        if (preg_match('#^001\d{6}$#', $data->banner_id))
            $student->setBannerId($data->banner_id);
        else
            API::error(400, "User banner ID not specified properly");
    }

    if (isset($data->grad_month))
    {
        if (preg_match('#^(05|12)/20[2-9]\d$#', $data->grad_month))
            $student->setGradMonth($data->grad_month);
        else
            API::error(400, "User grad month not specified properly");
    }

    $student->storeInDB();
});

API::error(404, "Not Found");
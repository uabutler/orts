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
    if (!Auth::isAuthenticatedStudent($_GET['email']))
        API::error(403, "You aren't allowed to request information about this student");

    if(!isset($_GET['email']))
        API::error(400, "Please specify email of desired student record");

    $student = Student::get($_GET['email']);

    if($student)
        return $student;
    else
        API::error(204, "No student found");

    // This should never happen, but my compiler will wine if I omit it
    return null;
});

// Create a student
API::post(function ($data)
{
    // TODO: Validate data
    Auth::forceAuthenticationStudent(null);

    $student = Student::build($data->email, $data->first_name, $data->last_name, $data->banner_id,
        $data->grad_month, $data->standing, $data->majors, $data->minors);

    if($student->storeInDB())
        return $student->getId();
    else
        API::error(409, "This is likely because a student with this email or Banner ID already exists");

    return null;
});

API::put(function ($data)
{
    if (!isset($data->email))
        API::error(400, "Please specify the student you'd like to modify");

    if (!Auth::isAuthenticatedStudent($data->email))
        API::error(403, "You aren't allowed to modify this student");

    $student = Student::get($data->email);

    // TODO: Validate data

    if (isset($data->first_name))
        $student->setFirstName($data->first_name);

    if (isset($data->last_name))
        $student->setLastName($data->last_name);

    if (isset($data->majors))
        $student->setMajors($data->majors);

    if (isset($data->minors))
        $student->setMinors($data->minors);

    if (isset($data->banner_id))
        $student->setBannerId($data->banner_id);

    if (isset($data->grad_month))
        $student->setGradMonth($data->grad_month);

    $student->storeInDB();
});

API::error(404, "Not Found");
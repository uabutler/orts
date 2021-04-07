<?php
require_once '../../php/api.php';
require_once '../../php/auth.php';
require_once '../../php/database/requests.php';
require_once '../../php/database/students.php';
require_once '../../php/database/faculty.php';
require_once '../../php/database/courses.php';

Auth::createClient();

API::get(function()
{
    Auth::forceAuthenticationStudent($_GET['email']);

    if(!isset($_GET['email']))
    {
        http_response_code(400);
        exit();
    }

    $student = Student::get($_GET['email']);

    if($student)
        return $student;
    else
        API::error(204, "No student found");
});

API::post(function ($data)
{
    // TODO Validate students
    Auth::forceAuthenticationStudent(null);

    $student = Student::build($data->email, $data->first_name, $data->last_name, $data->banner_id,
        $data->grad_month, $data->standing, $data->majors, $data->minors);

    if($student->storeInDB())
        return $student->getId();
    else
        API::error(409, "This is likely because a student with this email or Banner ID already exists");
});

API::error(404, "Not Found");
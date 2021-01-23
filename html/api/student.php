<?php
require_once '../php/database/students.php';

if($_SERVER['REQUEST_METHOD'] === 'GET')
    getStudent();
else if($_SERVER['REQUEST_METHOD'] === 'POST')
    putStudent();
else
    http_response_code(404);

function getStudent()
{
    if(!isset($_GET['email']))
    {
        http_response_code(400);
        exit();
    }

    $student = Student::get($_GET['email']);

    if($student)
    {
        http_response_code(200);
        echo json_encode($student);
    }
    else
    {
        http_response_code(204);
    }
}

function putStudent()
{
    $input = file_get_contents('php://input');

    $data = json_decode($input);

    // TODO: Validate data

    $student = Student::build($data->email, $data->first_name, $data->last_name, $data->banner_id,
                                 $data->grad_month, $data->standing, $data->majors, $data->minors);

    if($student->storeInDB())
    {
        http_response_code(200);
        echo $student->getId();
    }
    else
    {
        http_response_code(409);
    }
}

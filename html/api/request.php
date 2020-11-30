<?php
include_once '../database/requests_db.php';
include_once '../database/students_db.php';
include_once '../database/faculty_db.php';
include_once '../database/courses_db.php';

if($_SERVER['REQUEST_METHOD'] === 'GET')
    getRequest();
else if($_SERVER['REQUEST_METHOD'] === 'POST')
    putRequest();
else
    http_response_code(404);

function getRequest()
{
    if(!isset($_GET['id']))
    {
        http_response_code(400);
        exit();
    }

    $request = Request::getById($_GET['id']);

    if($request)
    {
        http_response_code(200);
        echo json_encode($request);
    }
    else
    {
        http_response_code(204);
    }
}

function putRequest()
{
    $input = file_get_contents('php://input');

    $data = json_decode($input);

    // TODO: Validate data

    // TODO: Change default faculty in admin functions
    $request = Request::build(Student::getById($data->student_id), Section::getByCrn(Semester::getByCode($data->semester),
                            $data->crn), Faculty::getById(1), 'Received', $data->reason, $data->explanation);

    $request->storeInDB();

    http_response_code(200);
}

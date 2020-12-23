<?php
include_once '../database/requests_db.php';
include_once '../database/students_db.php';
include_once '../database/faculty_db.php';
include_once '../database/courses_db.php';

if($_SERVER['REQUEST_METHOD'] === 'GET')
    getRequest();
else if($_SERVER['REQUEST_METHOD'] === 'POST')
    putRequest();
else if($_SERVER['REQUEST_METHOD'] === 'PUT')
    updateRequest();
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

    if($request->storeInDB())
        http_response_code(200);
    else
        http_response_code(409);

    http_response_code(200);
}

function updateRequest()
{
    parse_str(file_get_contents('php://input'), $_PUT);

    if(isset($_PUT['id']) && is_numeric($_PUT['id']))
    {
        $request = Request::getById(intval($_PUT['id']));

        // TODO: These are the things the student edits
        if(isset($_PUT['semester']) || isset($_PUT['crn']))
        {
            if(!(isset($_PUT['semester']) && isset($_PUT['crn'])))
            {
                http_response_code(400);
                exit();
            }

            $request->setSection(Section::getByCrn(Semester::getByCode($_PUT['semester']), $_PUT['crn']));
        }

        if(isset($_PUT['reason']))
            $request->setReason($_PUT['reason']);

        if(isset($_PUT['explanation']))
            $request->setExplanation($_PUT['explanation']);

        if(isset($_PUT['active']))
        {
            if(filter_var($_PUT['active'], FILTER_VALIDATE_BOOLEAN))
                $request->setActive();
            else
                $request->setInactive();
        }

        if(isset($_PUT['faculty']))
            $request->setFaculty(Faculty::get($_PUT['faculty']));

        // TODO: These are the things the faculty edit
        if(isset($_PUT['status']))
        {
            if(!in_array($_PUT['status'], Request::listStatuses()))
            {
                http_response_code(400);
                exit();
            }

            $request->setStatus($_PUT['status']);
        }

        if(isset($_PUT['justification']))
            $request->setJustification($_PUT['justification']);

        if(isset($_PUT['banner']))
        {
            if(filter_var($_PUT['banner'], FILTER_VALIDATE_BOOLEAN))
                $request->setInBanner();
            else
                $request->setNotInBanner();
        }

        if($request->storeInDB())
            http_response_code(200);
        else
            http_response_code(400);
    }
    else
    {
        http_response_code(400);
    }
}
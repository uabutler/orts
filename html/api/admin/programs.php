<?php
require_once '../../php/database/programs.php';

error_log("MAKING REQUEST");
if($_SERVER['REQUEST_METHOD'] === 'POST')
    postRequest();
else if($_SERVER['REQUEST_METHOD'] === 'DELETE')
    deleteRequest();
else
    http_response_code(404);

function postRequest()
{
    error_log("USING POST");
    $lines = file("php://input", FILE_IGNORE_NEW_LINES);
    $lines = array_filter(array_map('trim', $lines));
    $program_type = array_shift($lines);

    foreach ($lines as $program)
    {
        if($program_type === "major")
            Major::build($program)->storeInDB();
        else
            Minor::build($program)->storeInDB();
    }
}

function deleteRequest()
{
    // TODO
}
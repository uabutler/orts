<?php
include_once '../database/courses_db.php';

if($_SERVER['REQUEST_METHOD'] === 'GET')
    getSection();
else
    http_response_code(404);

function getSection()
{
    if(!(isset($_GET['semester']) && isset($_GET['department']) && isset($_GET['course_num']) && isset ($_GET['section'])))
    {
        http_response_code(400);
        exit();
    }

    $course = Course::get(Department::get($_GET['department']), $_GET['course_num']);
    $semester = Semester::getByCode($_GET['semester']);

    $section = Section::get($course, $semester, intval($_GET['section']));

    if($section)
    {
        http_response_code(200);
        echo json_encode($section);
    }
    else
    {
        http_response_code(204);
    }
}

<?php
require_once '../../../php/auth.php';
require_once '../../../php/api.php';
require_once '../../../php/database/courses.php';

API::get(function ()
{
    if(!(isset($_GET['semester']) && isset($_GET['department']) && isset($_GET['course_num']) && isset ($_GET['section'])))
        API::error(400, "Please specify enough information to determine section");

    $course = Course::get(Department::get($_GET['department']), $_GET['course_num']);
    $semester = Semester::getByCode($_GET['semester']);
    $section = Section::get($course, $semester, intval($_GET['section']));

    if($section)
        return $section;
    else
        API::error(204, "No request found");

    return null;
});

API::error(404, "Not Found");

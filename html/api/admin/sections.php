<?php
require_once '../../../php/auth.php';
require_once '../../../php/api.php';
require_once '../../../php/database/courses.php';

Auth::createClient();

API::get(function()
{
    return Section::list(Semester::getById($_GET['id']));
});

API::post(function($data)
{
    $course = Course::get(Department::get($data->department), $data->course_num);
    $semester = Semester::get($data->semester);
    $section = Section::build($course, $semester, $data->section, $data->crn);

    $section->storeInDB();
    return "Success";
});

API::put(function($data)
{
    foreach ($data as $update)
    {
        $section = Section::getById($update->id);

        if (isset($update->archive) && $update->archive)
            $section->setInactive();

        $section->storeInDB();

        if (isset($update->delete) && $update->delete)
            $section->delete();
    }

    return 'success';
});

API::error(404, "Not Found");

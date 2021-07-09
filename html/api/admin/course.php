<?php
require_once '../../../php/auth.php';
require_once '../../../php/api.php';
require_once '../../../php/database/courses.php';

API::get(function ()
{
    if (isset($_GET['department']) && isset($_GET['course_num']))
        return Course::get(Department::get($_GET['department']), intval($_GET['course_num']));
    else
        return Course::list();
});

API::post(function ($data)
{
    $course = Course::build(Department::get($data->department), $data->course_num, $data->title);

    if ($course->storeInDB())
        return "Success";
});

API::put(function ($data)
{
    $ret = true;

    foreach ($data as $update)
    {
        $course = Course::getById($update->id);

        if (isset($update->archive) && $update->archive)
            $course->setInactive();

        $ret = $ret && $course->storeInDB();

        if (isset($update->delete) && $update->delete)
            $ret = $ret && $course->deleteFromDB();
    }

    if ($ret)
        return 'Success';

    // TODO: Error Handling
    return null;
});

<?php
require_once '../../../php/auth.php';
require_once '../../../php/api.php';
require_once '../../../php/database/courses.php';

API::get(function()
{
    if (isset($_GET['department']) || isset($_GET['course_num']))
    {
        if (!(isset($_GET['department']) && isset($_GET['course_num'])))
            API::error(400, "Not enough information to determine course. Please specify department and course number");

        if (!in_array($_GET['department'], Department::list()))
            API::error(400, "Department not specified properly. Please choose from available list");

        if (!preg_match('/^\d{3}$/', $_GET['course_num']))
            API::error(400, "Course number not specified properly. Please specify 3 digit course number");

        return Course::get(Department::get($_GET['department']), intval($_GET['course_num']));
    }
    else
        return Course::list();
});

API::post(function ($data)
{
    if (!(isset($data->department) && in_array($data->department, Department::list())))
        API::error(400, "Department not specified properly. Please choose from available list");

    if (!(isset($data->department) && preg_match('/^\d{3}$/', $data->course_num)))
        API::error(400, "Course number not specified properly. Please specify 3 digit course number");

    if (!(isset($data->title) && preg_match('/\S+/', $data->course_num)))
        API::error(400, "Course number not specified properly. Please specify 3 digit course number");

    $course = Course::build(Department::get($data->department), intval($data->course_num), $data->title);

    if ($course->storeInDB())
    {
        return "Success";
    }
    else
    {
        $error_info = $course->errorInfo();

        $error_msg = "ORTS ERROR: /api/admin/course.php CREATE ";
        $error_msg .= " Dept=" . $data->department;
        $error_msg .= " Course=" . $data->course_num;
        $error_msg .= " Title=" . $data->title;
        $error_msg .= " SQLSTATE=" . $error_info[0];
        $error_msg .= " ErrorMsg=" . $error_info[2];
        error_log($error_msg);

        if ($error_info[0] === "23000")
            API::error(409, "This course has already been created");
        else
            API::error(500, "An unknown error has occurred. Please contact the system administrator");
    }
});

API::put(function ($data)
{
    $ret = true;

    if (!is_array($data))
        API::error(400, "Please submit a list of courses to update");

    foreach ($data as $update)
    {
        if (!(isset($update->id)))
            API::error(400, "Please specify the id of each course you want to update");

        $course = Course::getById(intval($update->id));

        if (is_null($course))
        {
            $ret = false;
            continue;
        }

        if (isset($update->archive) && filter_var($update->archive, FILTER_VALIDATE_BOOLEAN))
            $course->setInactive();

        $err = $course->storeInDB();

        // TODO: Log error
        if (!$err);

        $ret = $ret && $err;

        if (isset($update->delete) && filter_var($update->delete, FILTER_VALIDATE_BOOLEAN))
            $err = $course->deleteFromDB();

        if (!$err)
        {
            $error_msg = "ORTS ERROR: /api/admin/course.php UPDATE ";
            $error_msg .= " ID=" . $update->id;
            $error_msg .= " Can't Delete STATUS=" . ($course->isActive() ? "active" : "inactive");
            error_log($error_msg);
        }

        $ret = $ret && $err;
    }

    if ($ret)
        return "Success";
    else
        API::error(500, "An unknown error has occurred. One or more courses were not updated. Please contact the system administrator");
});

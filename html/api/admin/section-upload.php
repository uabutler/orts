<?php
require_once '../../../php/auth.php';
include_once '../../../php/database/courses.php';

Auth::createClient();

/*
if (!Auth::isAuthenticatedFaculty())
{
    http_response_code(403);

    $response['msg'] = "You aren't allowed to upload attachments";
    echo json_encode($response);

    exit();
}
*/

$excel = $_FILES['attachment']['tmp_name'];
rename($excel, $excel . $_FILES['attachment']['name']);
$excel .= $_FILES['attachment']['name'];

// This python script reads the spread sheets and writes the contents to a PHP friendly TSV
exec("python ../../../script/section_xlsx2csv.py $excel", $out, $result_code);
// The out will contain the errors if there are any. Otherwise, it contains the TSV
if ($result_code !== 0)
{
    print_r($out);
    exit();
}

$semester = Semester::getById($_POST['semester']);

foreach ($out as $line)
{
    if ($line === "") exit();

    $crn = strtok($line, "\t");
    $department_prefix = strtok("\t");
    $course_num = strtok("\t");
    $section_num = strtok("\t");
    $title = substr(strtok("\t"), 0, -1);

    $department = Department::get($department_prefix);
    if (is_null($department))
    {
        $department = Department::build($department_prefix);
        $department->storeInDB();
    }

    $course = Course::get($department, $course_num);
    if (is_null($course))
    {
        $course = Course::build($department, $course_num, $title);
        $course->storeInDB();
    }

    $section = Section::build($course, $semester, $section_num, $crn);
    print_r($section);

    $section->storeInDB();

    print_r($section);
}

?>

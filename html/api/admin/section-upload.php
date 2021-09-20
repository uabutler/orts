<?php
require_once '../../../php/logger.php';
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

Logger::info("Spreadsheet submitted");

$excel = $_FILES['attachment']['tmp_name'];
Logger::info("Upload: $excel");
rename($excel, $excel . $_FILES['attachment']['name']);
$excel .= $_FILES['attachment']['name'];
Logger::info("Upload moved: $excel");
Logger::info("Reading spreadsheet...");

// This python script reads the spread sheets and writes the contents to a PHP friendly TSV
exec("python3 ../../../script/section_xlsx2csv.py $excel", $out, $result_code);

Logger::info("Done reading");
// The out will contain the errors if there are any. Otherwise, it contains the TSV
if ($result_code !== 0)
{
    global $_REQUEST_ID;

    Logger::error("Script execution failed. Output: " . Logger::obj($out));
    echo $_REQUEST_ID;
    print_r($out);
    exit();
}

$semester = Semester::getById($_POST['semester']);

Logger::info("Writing sections to semester: " . $semester->getDescription());

foreach ($out as $line)
{
    if ($line === "") exit();

    $crn = strtok($line, "\t");
    $department_prefix = strtok("\t");
    $course_num = strtok("\t");
    $section_num = strtok("\t");
    $title = strtok("\t");

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

    $section->storeInDB();
}

?>

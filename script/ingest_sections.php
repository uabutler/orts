<?php
include_once '../html/database/common_db.php';
include_once '../html/database/courses_db.php';

$pdo = connectDB();

$argc == 2 or die("Please specify an input file\n");

$file = fopen($argv[1], "r");
$pdo = connectDB();
$semester = Semester::getSemesterByCode('202110');

while(!feof($file))
{
  $line = fgets($file);
  if($line == "") exit();

  $crn = strtok($line, "\t");
  $department_prefix = strtok("\t");
  $course_num = strtok("\t");
  $section_num = strtok("\t");
  $title = substr(strtok("\t"), 0, -1);


  $course = Course::getCourse(Department::getDepartment($department_prefix), $course_num);
  if(is_null($course))
  {
    $course = Course::buildCourse(Department::getDepartment($department_prefix), $course_num, $title);
    $course->storeInDB();
  }

  $section = Section::buildSection($course, $semester, $section_num, $crn);

  $section->storeInDB();
}

fclose($file);

?>

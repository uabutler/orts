<?php
include_once '../html/database/common_db.php';
include_once '../html/database/courses_db.php';

$pdo = connectDB();
$file = fopen("out.csv", "r");
$pdo = connectDB();
$semester = Semester::getActive();

while(!feof($file))
{
  $line = fgets($file);

  $crn = strtok($line, '\t');
  $department_prefix = strtok('\t');
  $course_num = strtok('\t');
  $section_num = strtok('\t');
  $title = strtok('\t');

  $course = Course::getCourse(Department::getDepartment($department_prefix), $course_num);

  if(is_null($course))
  {
    $course = Course::buildCourse(Department::getDepartment($department_prefix), $course_num, $title);
    $course->storeInDB();
  }

  $section = Section::buildSection($course, $semester, $section_num, $crn);

  $section->storeInDB();

  fclose($file);
}

?>

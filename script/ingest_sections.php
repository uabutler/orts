<?php
include_once '../html/database/common_db.php';
include_once '../html/database/courses_db.php';

$pdo = connectDB();

$argc == 3 or $argc == 4 or die("Please specify an excel file and an output tsv file: $argv[0] excel_file tsv_file [python_script_name]\n");

echo "Converting xlsx to tsv\n";
if($argc == 4)
  $mesg = exec("python $argv[3] $argv[1] $argv[2]");
else
  $mesg = exec("python section_xlsx2csv.py $argv[1] $argv[2]");
echo $mesg;
echo "Done\n";

echo "Reading from $argv[2]\n";
$file = fopen($argv[2], "r");

echo "Establishing connection to database\n";
$pdo = connectDB();
echo "Determining current semester...";
$semester = Semester::getByCode('202110');
echo "Found ".$semester->getCode()."\n";

echo "Storing data\n";
while(!feof($file))
{
  $line = fgets($file);
  if($line == "") exit();

  $crn = strtok($line, "\t");
  $department_prefix = strtok("\t");
  $course_num = strtok("\t");
  $section_num = strtok("\t");
  $title = substr(strtok("\t"), 0, -1);


  $course = Course::get(Department::get($department_prefix), $course_num);
  if(is_null($course))
  {
    $course = Course::build(Department::get($department_prefix), $course_num, $title);
    $course->storeInDB();
  }

  echo "Building section\n";
  $section = Section::build($course, $semester, $section_num, $crn);
  print_r($section);

  $section->storeInDB();

  print_r($section);
}

echo "done; exiting\n";
fclose($file);

?>
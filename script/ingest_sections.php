<?php
include_once '../html/database/common_db.php';

$pdo = connectToDB();
=======
$file = fopen("out.csv", "r");
$pdo = connectDB();

while(!feof($file))
{
  $line = fgets($file);

  $crn = strtok($line, '\t');
  $department = strtok('\t');
  $course_num = strtok('\t');
  $section = strtok('\t');
  $title = strtok('\t');


}

?>

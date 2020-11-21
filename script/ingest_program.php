<?php
include_once '../html/database/common_db.php';
include_once '../html/database/students_db.php';

$pdo = connectDB();

$argc == 2 or die("Please specify input files: $argv[0] majors minors\n");

echo "Establishing connection to database\n";
$pdo = connectDB();

echo "Reading from majors file $argv[1]\n";
$file = fopen($argv[1], "r");

echo "Storing data\n";
while(!feof($file))
{
  $line = substr(fgets($file), 0, -1);
  if(strlen($line) <= 1) exit();
  Student::addMajor($line);
}

echo "done\n";
fclose($file);

echo "Reading from minors file $argv[1]\n";
$file = fopen($argv[1], "r");

echo "Storing data\n";
while(!feof($file))
{
  $line = substr(fgets($file), 0, -1);
  if(strlen($line) <= 1) exit();
  Student::addMinor($line);
}

echo "done\n";
fclose($file);

?>

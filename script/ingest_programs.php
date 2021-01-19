<?php
include_once '../html/php/database/common.php';
include_once '../html/php/database/students.php';
include_once '../html/php/database/programs.php';

$pdo = connectDB();

$argc == 3 or die("Please specify input files: $argv[0] majors minors\n");

echo "Establishing connection to database\n";
$pdo = connectDB();

echo "Reading from majors file $argv[1]\n";
$file = fopen($argv[1], "r");

echo "Storing data\n";
while(!feof($file))
{
  $line = substr(fgets($file), 0, -1);
  if(strlen($line) <= 1) break;
  Major::build($line)->storeInDB();
}

echo "done\n";
fclose($file);

echo "Reading from minors file $argv[2]\n";
$file = fopen($argv[2], "r");

echo "Storing data\n";
while(!feof($file))
{
  $line = substr(fgets($file), 0, -1);
  if(strlen($line) <= 1) break;
  Minor::build($line)->storeInDB();
}

echo "done\n";
fclose($file);

?>

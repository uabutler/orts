<?php
include_once 'students_db.php';
include_once 'faculty_db.php';
include_once 'requests_db.php';

/*
$student = Student::get('ub4782');

print_r($student);

$faculty = Faculty::get('sandefur');

print_r($faculty);

$request = Request::build($student, Section::getById(1), $faculty, Request::listStatuses()[0], Request::listReasons()[0], "Needed to graduate");

$request->storeInDB();

print_r($request);

$student = Student::build('jtk1701', 'James', 'Kirk', '001174565', '05/2021', 'Senior', ['Physics'], []);

print_r($student);

$student->storeInDB();

print_r($student);

$student = Student::get('ub4782');

$student->setMajors(['Computer Science']);
$student->setMinors(['Mathematics']);

$student->storeInDB();
$faculty = Faculty::get('sandefur');

$request = Request::build(Student::get('jtk1701'), Section::getById(2), $faculty, Request::listStatuses()[0], Request::listReasons()[0], "It sounds like a fun class");

$request = Request::getById(1);

$request->setStatus('Provisionally Approved');

$request->storeInDB();

print_r($request);

$student = Student::get('jtk1701');

$student->setMajors(['Physics', 'Mathematics']);

$student->storeInDB();
*/

$request = Request::getById(3);

$request->setExplanation("I can't register");

echo $request->getExplanation();
echo "\n";

$request->storeInDB();

$request = Request::getById(3);

echo $request->getExplanation();
echo "\n";

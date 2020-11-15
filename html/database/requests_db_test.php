<?php
	include_once 'requests_db.php';
	include_once 'students_db.php';

	class RequestTests{
		public static function getRequestsTest(){
			print_r(OverrideRequest::getOverrideRequests());
		}
		
		public static function getRequestTest(){
			$id = 6;
			print_r(OverrideRequest::getOverrideRequest($id));		
		}
		
		public static function getStudentRequestsTest(){
			$studentId = 2;
			$studentObject = Student::getStudentById($studentId);
			print_r(OverrideRequest::getStudentRequests($studentObject));
		}
	}
	
	RequestTests::getRequestTest();
	RequestTests::getRequestsTest();
	RequestTests::getStudentRequestsTest();
?>
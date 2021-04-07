<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/cas/CAS.php';
require_once __DIR__ . '/database/faculty.php';
require_once __DIR__ . '/database/students.php';

class Auth
{
    static function createClient(): void
    {
        phpCAS::client(CAS_SERVER['version'], CAS_SERVER['host'], CAS_SERVER['port'], CAS_SERVER['context']);
        phpCAS::setCasServerCACert(CAS_SERVER['cert_path']);
    }

    static function isAuthenticated(): bool
    {
        return phpCAS::isAuthenticated();
    }

    static function getUser(): string
    {
        return phpCAS::getUser();
    }

    static function isAuthenticatedStudent(string $expectedStudent): bool
    {
        $ret = self::isAuthenticated() && !is_null(Student::get(self::getUser()));
        if(!is_null($expectedStudent))
            $ret = $ret && ($expectedStudent === self::getUser());

        return $ret;
    }

    static function isAuthenticatedFaculty(): bool
    {
        return self::isAuthenticated() && !is_null(Faculty::get(self::getUser()));
    }

    static function forceAuthentication(): bool
    {
        return phpCAS::forceAuthentication();
    }

    /**
     * Authenticate. If not student, create new profile. If the specific student is not allowed to access that
     * page, HTTP Forbidden
     * @param $expectedStudent string The email of the student who is permitted to access this page
     */
    static function forceAuthenticationStudent(string $expectedStudent)
    {
        self::forceAuthentication();
        if(!self::isAuthenticatedStudent(null))
            header("Location: /student/new-profile.php");
        elseif(!self::isAuthenticatedStudent($expectedStudent))
            include '../error/error403.php';
    }

    /**
     * Authenticate. If not faculty, 403
     */
    static function forceAuthenticationFaculty()
    {
        self::forceAuthentication();
        if(is_null(Faculty::get(self::getUser())))
            include '../error/error403.php';
    }

    static function logout()
    {
        phpCAS::logoutWithRedirectService(SERVER['name'] . "/home.php");
    }
}

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

    static function isAuthenticatedStudent(): bool
    {
        return self::isAuthenticated() && !is_null(Faculty::get(self::getUser()));
    }

    static function isAuthenticatedFaculty(): bool
    {
        return self::isAuthenticated() && !is_null(Student::get(self::getUser()));
    }

    static function forceAuthentication(): bool
    {
        return phpCAS::forceAuthentication();
    }

    /**
     * Authenticate. If not student, create new profile
     */
    static function forceAuthenticationStudent()
    {
        self::forceAuthentication();
        if(is_null(Student::get(self::getUser())))
        {
            header("Location: /student/new-profile.php?ret=".rawurldecode($_SERVER['PHP_SELF']));
        }
    }

    /**
     * Authenticate. If not faculty, 403
     */
    static function forceAuthenticationFaculty()
    {
        self::forceAuthentication();
        if(is_null(Faculty::get(self::getUser())))
        {
            http_response_code(403);
            header("Location: /error403.php");
        }
    }

    static function logout()
    {
        // TODO: Update to use config URL
        phpCAS::logoutWithRedirectService("orts.uabutler.com/home.php");
    }
}

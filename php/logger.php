<?php
require_once 'config.php';
require_once 'database/common.php';

function uuid($data = null)
{
    $data = $data ?? random_bytes(16);
    assert(strlen($data) == 16);

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

// I know global variables are bad, but this is used EVERYWHERE and is only written here, so I'll give myself a pass...

/**
 * When a user makes a request, a UUID is created to identify the request as this request id variable. This value is
 * then used in all logs related to this request and is sent back to the user as a JSON parameter, in the HTML metadata,
 * etc. This way, when a user experiences a problem, we can easily retrieve all the logs related to the request
 */
$_REQUEST_ID = uuid();

/**
 * Class Verbosity
 * The user of this application can specify a verbosity level of logging in the config. Each log function call can
 * specify what the minimum level of verbosity is for that log entry to be emitted. The log can then check if it
 * should be emitted using the isEmitted() function.
 */
class Verbosity
{
    const ALL = 4;
    const HIGH = 3;
    const MED = 2;
    const LOW = 1;
    const NONE = 0;

    static function isEmitted(int $lvl): bool
    {
        $config_lvl = intval(LOGGING['verbosity']);
        return $config_lvl && ($lvl <= $config_lvl);
    }
}

/**
 * Class Logger
 * Write information, a warning, or an error message to the default PHP system logger for the override tracking system.
 * Includes tag indicating the application (ORTS), the log leven (INFO, WARN, or ERROR), a timestamp, and the function
 * or file that the log call was made from.
 *
 * In the future, the option will also be given to email the sysadmin of this application when certain fatal errors occur
 */
class Logger
{
    // Prefix the log entry with a timestamp and the name of the calling function
    private static function getPrefix(string $type): string
    {
        global $_REQUEST_ID;

        $prefix = "[ORTS] ";
        // The timestamp might already be added to the logs by php, so this is likely redundant
        //$prefix .= getTimeStamp() . "  ";
        $prefix .= $type . " ";
        $prefix .= $_REQUEST_ID . " ";

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);

        if (isset($trace[2]) && isset($trace[2]['class']))
            $prefix .= $trace[2]['class'] . "::" . $trace[2]['function'];
        else if (isset($trace[2]))
            $prefix .= $trace[2]['file'] . ":" . $trace[2]['function'];
        else
            $prefix .= $trace[1]['file'];

        $prefix .= " - ";

        return $prefix;
    }

    private static function log($msg)
    {
        error_log($msg, 0);
    }

    private static function pingAdministrator($msg)
    {
        $email = SERVER['webmaster'];
        // TODO
    }

    static function start()
    {
        global $_REQUEST_ID;
        $msg = "[ORTS] START $_REQUEST_ID";

        if ($is_auth = Auth::isAuthenticated())
        {
            $user = Auth::getUser();
            $user_type = Auth::isAuthenticatedFaculty() ? "Admin" : "Student";
            $msg .= " AUTHENTICATED AS $user WITH $user_type";
        }
        else
        {
            $msg .= " NOT AUTHENTICATED";
        }

        self::log($msg);
        self::log(print_r($_REQUEST, true));
    }

    static function end()
    {
        global $_REQUEST_ID;
        $msg = "[ORTS] END   $_REQUEST_ID";
        self::log($msg);
    }

    /**
     * Tagged as INFO. By default, only logged if verbose level is set to all
     */
    static function info(string $msg, int $lvl = Verbosity::ALL)
    {
        if (!Verbosity::isEmitted($lvl)) return;

        $log_entry = self::getPrefix("INFO ") . $msg;
        self::log($log_entry);
    }

    /**
     * Tagged as WARN. By default, only logged if verbose level is set to med
     */
    static function warning(string $msg, int $lvl = Verbosity::MED)
    {
        if (!Verbosity::isEmitted($lvl)) return;

        $log_entry = self::getPrefix("WARN ") . $msg;
        self::log($log_entry);
    }

    /**
     * Tagged as ERROR. By default, logged as long as logging isn't disabled.
     * In the future, this should also email the system administrator when asked
     */
    static function error(string $msg, int $lvl = Verbosity::LOW, bool $ping = false)
    {
        if (!Verbosity::isEmitted($lvl)) return;

        $log_entry = self::getPrefix("ERROR") . $msg;
        self::log($log_entry);

        if ($ping) self::pingAdministrator($log_entry);
    }

    /**
     * Turn an object into a string that can be written to the log
     * @param $obj Object|array The object to stringify
     * @return string A string representing the object
     */
    static function obj($obj): string
    {
        $ret = preg_replace("/\s+/", " ", var_export($obj, true));

        $ret = str_replace("::__set_state(array", "", $ret);

        if (substr($ret, 0, 5) == "array")
            return $ret;
        else
            return substr($ret, 0, -1);
    }
}

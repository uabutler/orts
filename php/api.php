<?php
require_once 'logger.php';

/**
 * When I started building this application, I didn't know about any "proper" techniques, so here is my
 * poor man's routing
 */
class API
{
    private static function paramHelper(string $method, callable $func)
    {
        global $_REQUEST_ID;

        if ($_SERVER['REQUEST_METHOD'] === $method)
        {
            Logger::start();
            $response['response'] = call_user_func($func);
            $response['request_id'] = $_REQUEST_ID;
            http_response_code(200);
            echo json_encode($response);
            Logger::end();
            exit();
        }
    }

    private static function jsonHelper(string $method, callable $func)
    {
        global $_REQUEST_ID;

        if ($_SERVER['REQUEST_METHOD'] === $method)
        {
            Logger::start();
            $param = json_decode(file_get_contents('php://input'));
            $response['response'] = call_user_func($func, $param);
            $response['request_id'] = $_REQUEST_ID;
            echo json_encode($response);
            Logger::end();
            exit();
        }
    }

    public static function get(callable $func)
    {
        self::paramHelper('GET', $func);
    }

    public static function delete(callable $func)
    {
        global $_DELETE;
        // A DELETE equivalent to $_GET isn't included by default
        parse_str(file_get_contents('php://input'), $_DELETE);
        self::paramHelper('DELETE', $func);
    }

    public static function post(callable $func)
    {
        self::jsonHelper('POST', $func);
    }

    public static function put(callable $func)
    {
        self::jsonHelper('PUT', $func);
    }

    public static function error(int $code, string $msg)
    {
        if ($code < 500)
            Logger::warning("Request failed. Returning error $code. REASON=$msg");
        else
            Logger::error("Request failed. Returning error $code. REASON=$msg", Verbosity::LOW, true);

        global $_REQUEST_ID;

        http_response_code($code);

        $response['msg'] = $msg;
        $response['request_id'] = $_REQUEST_ID;
        echo json_encode($response);

        exit();
    }

    public static function log(string $message)
    {
        file_put_contents("/var/www/orts/log", $message);
    }
}
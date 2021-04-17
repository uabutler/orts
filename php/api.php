<?php

/**
 * When I started building this application, I didn't know about any "proper" techniques, so here is my
 * poor man's routing
 */
class API
{
    private static function paramHelper(string $method, callable $func)
    {
        if ($_SERVER['REQUEST_METHOD'] === $method)
        {
            $response = call_user_func($func);
            http_response_code(200);
            echo json_encode($response);
            exit();
        }
    }

    private static function jsonHelper(string $method, callable $func)
    {
        if ($_SERVER['REQUEST_METHOD'] === $method)
        {
            $param = json_decode(file_get_contents('php://input'));
            $response = call_user_func($func, $param);
            echo json_encode($response);
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
        http_response_code($code);

        $response['msg'] = $msg;
        echo json_encode($response);

        exit();
    }

    public static function log(string $message)
    {
        file_put_contents("/var/www/orts/log", $message);
    }
}
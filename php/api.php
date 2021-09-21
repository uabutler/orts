<?php
require_once 'logger.php';
require_once __DIR__ . '/../php/database/helper/DatabaseException.php';
require_once __DIR__ . '/../php/database/helper/PDOWrapper.php';

function api_exception_handler($exception)
{
    Logger::error("A fatal error has occurred: $exception", Verbosity::LOW, true);
    PDOWrapper::rollBack();
    API::error(500, "An unknown error has occurred");
}

set_exception_handler('api_exception_handler');

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
            try
            {
                $response['response'] = call_user_func($func);
            }
            catch (DatabaseException $e)
            {
                API::error($e->getCode(), $e->getMessage());
            }
            catch ( \Exception $e)
            {
                API::error(500, "An unknown error occurred");
            }

            $response['request_id'] = Logger::getRequestId();
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

            try
            {
                $response['response'] = call_user_func($func, $param);
            }
            catch (DatabaseException $e)
            {
                API::error($e->getCode(), $e->getMessage());
            }
            catch ( \Exception $e)
            {
                API::error(500, "An unknown error occurred");
            }

            $response['request_id'] = Logger::getRequestId();
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
        if ($code < 500)
            Logger::warning("Request failed. Returning error $code. REASON=$msg");
        else
            Logger::error("Request failed. Returning error $code. REASON=$msg", Verbosity::LOW, true);

        http_response_code($code);

        $response['msg'] = $msg;
        $response['request_id'] = Logger::getRequestId();
        echo json_encode($response);

        exit();
    }

    public static function success(string $msg)
    {
        $response['msg'] = $msg;
        $response['request_id'] = Logger::getRequestId();
        echo json_encode($response);
        exit();
    }

    public static function log(string $message)
    {
        file_put_contents("/var/www/orts/log", $message);
    }
}
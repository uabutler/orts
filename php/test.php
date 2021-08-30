<?php

require_once 'logger.php';

class Test
{
    function log_test()
    {
        Logger::info("test method");
    }
}

function log_test()
{
    Logger::info("test function");
}

$test = new Test();

Logger::info("test file");
$test->log_test();
log_test();
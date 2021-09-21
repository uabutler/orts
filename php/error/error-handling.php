<?php
require_once __DIR__ . '/../database/helper/PDOWrapper.php';
function web_page_exception_handler($exception)
{
    Logger::error("A fatal error has occurred: $exception", Verbosity::LOW, true);

    require_once __DIR__ . '/error500.php';
}

set_exception_handler('web_page_exception_handler');

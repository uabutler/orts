<?php

if($config = parse_ini_file('../../conf/app.ini', true))
{
    define('DATABASE', $config['Database']);
    define('CAS_SERVER', $config['CAS']);
    define('SERVER', $config['Server']);
}
else
{
    http_response_code(500);
    header("Location: error500.php");
}

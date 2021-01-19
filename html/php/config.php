<?php

if($config = parse_ini_file(__DIR__.'../../conf/app.ini', true))
{
    define('DATABASE', $config['Database']);
    define('CAS_SERVER', $config['CAS']);
    define('SERVER', $config['Server']);
    print_r(DATABASE);
}
else
{
    http_response_code(500);
    header("Location: error500.php");
}
print_r(CAS_SERVER);

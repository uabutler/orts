<?php

if($config = parse_ini_file(__DIR__ . '/../../conf/app.ini', true, INI_SCANNER_TYPED))
{
    define('DATABASE', $config['Database']);
    define('CAS_SERVER', $config['CAS']);
    define('SERVER', $config['Server']);
}
else
{
    include '../error/error500.php';
}

<?php

if($config = parse_ini_file(__DIR__ . '/../conf/app.ini', true, INI_SCANNER_TYPED))
{
    define('DATABASE', $config['Database']);
    define('CAS_SERVER', $config['CAS']);
    define('SERVER', $config['Server']);
}
else
{
    // Since the configuration file hasn't been read in, we can't use the Logger class.
    error_log("[ORTS] ERROR - Could not read configuration file. Has the system been installed?", 0);
    include '../html/error/error500.php';
}

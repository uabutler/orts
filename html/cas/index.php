<?php
error_reporting(E_ALL);
# the next line is useful while developing, but
# TURN IT OFF IN PRODUCTION!
ini_set("display_errors", "1");
session_start( ['cookie_lifetime' => 86400] );

// Load the settings from the central config file
require_once('config.php');

// Load the CAS lib
require_once($phpcas_path . '/CAS.php');

// Enable debugging
//phpCAS::setDebug();

// Initialize phpCAS
phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);

// For production use set the CA certificate that is the issuer of the cert
// on the CAS server and uncomment the line below
// phpCAS::setCasServerCACert($cas_server_ca_cert_path);
// For quick testing you can disable SSL validation of the CAS server.
// THIS SETTING IS NOT RECOMMENDED FOR PRODUCTION.
// VALIDATING THE CAS SERVER IS CRUCIAL TO THE SECURITY OF THE CAS PROTOCOL!
phpCAS::setNoCasServerValidation();

// force CAS authentication
phpCAS::forceAuthentication();

// at this step, the user has been authenticated by the CAS server
// and the user's login name can be read with phpCAS::getUser().
$username = phpCAS::getUser();

// the rest of what you want index.html to do ...
$dbname = "group1";
$dbhost = "borax.truman.edu";
$user = "group1";
$passwd = "370145";

$dsn = "mysql:host=$dbhost;dbname=$dbname";
$pdo = new PDO($dsn, $user, $passwd);

$smt = $pdo->prepare("SELECT * FROM faculty WHERE email=:username");
$smt->bindParam(':username', $username);   
$smt->execute();

$returnedRecords = $smt->fetchAll();

//if faculty: direct to admin.php
if(count($returnedRecords) >= 1){
  echo "<h1>Hello, $username!</h1>";
  //redirect to admin.html
} else {
  //redirect to studentDashboard.html
}

?>


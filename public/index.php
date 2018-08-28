<?php 
ini_set("session.cookie_lifetime",32400);
date_default_timezone_set('America/Chihuahua');
define("PRODUCCION", "SVR02");
define("DESARROLLO", "AOS06");
define("CONFIG",PRODUCCION );  
(CONFIG==DESARROLLO) ? define("ACTUAL","PRUEBAS") : define("ACTUAL","PRODUCCION");
(CONFIG==DESARROLLO) ? define("APP_INI","/configs/application_dev.ini") : define("APP_INI","/configs/application.ini");
// Define path to application directory

defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';
// Create application, bootstrap, and run
$application = new Zend_Application( APPLICATION_ENV,APPLICATION_PATH . ''.APP_INI);
Zend_Session::start();
/** Constants of APP */
require_once APPLICATION_PATH.'/models/UserConstants.php';
//require_once APPLICATION_PATH.'/../library/includes/atpWs/DynamicsWSConsumer.php';
/* this line run the appplication for all*/
$application->bootstrap()->run();
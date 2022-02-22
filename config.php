<?php
header('Content-type: text/html; charset=utf-8');
	
/** Block direct access to the configuration file **/
if (strtolower (__FILE__) == strtolower ($_SERVER ['SCRIPT_FILENAME']))
die ("DISABLED ACCESS!");

/** MySQL host name **/
define ('DB_HOST', 'localhost');

/** Port to connect to the MySQL **/
define ('DB_PORT', '3306');

/** MySQL database name **/
define ('DB_NAME', 'skrypt');

/** MySQL user name **/
define ('DB_USER', 'root');

/** MySQL user password **/
define ('DB_PSSWD', 'root');

/** MySQL database table prefix **/
define ('DB_PREFIX', '');

/** Version of the script: kwejk, demotywator, gag, bebzol, pinestic, vines **/
define ('IPS_VERSION', 'gag');

/************************************************* *****/
/**** Edit data below is not advisable! ! ! ***/
/************************************************* *****/

/** The root directory for the script file **/
// OLD_PATH: define ('ABS_PATH', 'C:\xampp\htdocs\skrypt');
define ('ABS_PATH', dirname (__FILE__));

/** PHP class files directory **/
define ('CLASS_PATH', ABS_PATH. '/classes');

/** PHP library files directory **/
define ('LIBS_PATH', ABS_PATH. '/libs');

/** Sciażka Cache files **/
define ('CACHE_PATH', ABS_PATH. '/cache');

/** Sets the debug script/MySQL **/
define ('IPS_DEBUG', false );

/** The unique identifier for each installation **/
define ('AUTH_KEY', '$1$di5.iI3.$4o.Upe1ulL1PDj5sLq2ss1');

/* 
	error_reporting(E_ALL);
*/
/** Set php debug file **/
ini_set( 'error_log', ABS_PATH . '/logs/php.log' );

/** Set server timezone **/
date_default_timezone_set('Europe/Warsaw');
ini_set( 'date.timezone', 'Europe/Warsaw' );
	
/** Set UTF-8 **/
mb_language('uni');
mb_internal_encoding('UTF-8');


define ('IPS_SELF', true );
?>
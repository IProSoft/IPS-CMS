<?php
header('Content-type: text/html; charset=utf-8');
	
/** Block direct access to the configuration file **/
if (strtolower (__FILE__) == strtolower ($_SERVER ['SCRIPT_FILENAME']))
die ("DISABLED ACCESS!");

/** MySQL host name **/
define ('DB_HOST', '{DB_HOST}');

/** Port to connect to the MySQL **/
define ('DB_PORT', '{DB_PORT}');

/** MySQL database name **/
define ('DB_NAME', '{DB_NAME}');

/** MySQL user name **/
define ('DB_USER', '{DB_USER}');

/** MySQL user password **/
define ('DB_PSSWD', '{DB_PSSWD}');

/** MySQL database table prefix **/
define ('DB_PREFIX', '{DB_PREFIX}');

/** Version of the script: kwejk, demotywator, gag, bebzol, pinestic, vines **/
define ('IPS_VERSION', '{IPS_VERSION}');

/************************************************* *****/
/**** Edit data below is not advisable! ! ! ***/
/************************************************* *****/

/** The root directory for the script file **/
// OLD_PATH: define ('ABS_PATH', '{ABS_PATH}');
define ('ABS_PATH', dirname (__FILE__));

/** PHP class files directory **/
define ('CLASS_PATH', ABS_PATH. '/classes');

/** PHP library files directory **/
define ('LIBS_PATH', ABS_PATH. '/libs');

/** Sciażka Cache files **/
define ('CACHE_PATH', ABS_PATH. '/cache');

/** Sets the debug script/MySQL **/
define ('IPS_DEBUG', false);

/** The unique identifier for each installation **/
define ('AUTH_KEY', '{AUTH_KEY}');

/* 
	error_reporting(E_ALL);
*/
/** Set php debug file **/
ini_set( 'error_log', ABS_PATH . '/php.log' );

/** Set server timezone **/
date_default_timezone_set('{TIMEZONE}');
ini_set( 'date.timezone', '{TIMEZONE}' );
	
/** Set UTF-8 **/
mb_language('uni');
mb_internal_encoding('UTF-8');

?>